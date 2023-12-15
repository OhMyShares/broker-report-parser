<?php
namespace ohmyshares\brokerReportParser;

use ohmyshares\brokerReportParser\providers\AlfaParser;
use ohmyshares\brokerReportParser\providers\TinkoffParser;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Parser
{
    public ?ParserResultInterface $parser = null;
    public ?string $lastError = null;

    public function __construct($filePath)
    {
        $this->parser = $this->findParser($filePath);
    }

    /**
     * Detects broker by first sheet name
     */
    public function findParser(string $filePath): ?ParserResultInterface
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
            $firstSheetName = $spreadsheet->getSheet(0)?->getTitle();
        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
            return null;
        }
        if (str_contains($firstSheetName, 'Динамика позиций')) {
            return new AlfaParser($spreadsheet);
        }
        if (str_contains($firstSheetName, 'broker_rep')) {
            return new TinkoffParser($spreadsheet);
        }
        return null;
    }

    public function isParsingAvailable(): bool
    {
        return $this->parser !== null;
    }

    /**
     * @return ParserResultInterface|bool result of parsing or false
     */
    public function parse(): ParserResultInterface|bool
    {
        if ($this->isParsingAvailable()) {
            $result = $this->parser->parse();
            if ($result) {
                return $result;
            }
        }
        return false;
    }

    /**
     * Brokers list available to parse
     * This function lookup for all classes in providers folder
     * @return array list of available brokers
     */
    public static function brokersAvailable(): array
    {
        $files = glob(__DIR__ . '/providers/*.php');
        $brokers = [];
        foreach ($files as $file) {
            $className = 'ohmyshares\\brokerReportParser\\providers\\' . basename($file, '.php');
            $broker = new $className(new Spreadsheet);
            $brokers[$broker->brokerId] = [
                'name' => $broker->brokerName,
                'nameLocal' => $broker->brokerNameLocal,
            ];
        }
        return $brokers;
    }
}