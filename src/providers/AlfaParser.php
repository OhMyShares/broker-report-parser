<?php
namespace ohmyshares\brokerReportParser\providers;

use DateTime;
use Exception;
use ohmyshares\brokerReportParser\models\Money;
use ohmyshares\brokerReportParser\models\Trade;
use ohmyshares\brokerReportParser\ParserResultInterface;
use ohmyshares\brokerReportParser\ParserResultResult;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AlfaParser extends ParserResultResult
{
    public string $brokerId = 'alfa';
    public ?string $brokerName = 'Alfa-bank';
    public ?string $brokerNameLocal = 'Альфа-Банк';

    public function parse(): ParserResultInterface|bool
    {
        try {
            $spreadsheet = $this->getSpreadsheet();
            $tradesSheet = $spreadsheet->getSheetByName('Завершенные сделки');
            $moneySheet = $spreadsheet->getSheetByName(' Движение ДС');
            $this->trades = $this->parseTrades($tradesSheet);
            $this->money = $this->parseMoney($moneySheet);
            return $this;
        } catch (Exception $e) {
            $this->exceptionData = $e;
        }
        return false;
    }

    /**
     * @param Worksheet $worksheet
     * @return Trade[] list of trades parsed
     */
    public function parseTrades(Worksheet $worksheet): array
    {
        $data = [];
        for ($row = 16; $row <= $worksheet->getHighestDataRow(); ++$row) {
            $trade = new Trade;
            $trade->id = str_replace("\n", ' ', $worksheet->getCell([5, $row])->getValue());
            $dateStr = str_replace("\n", '', $worksheet->getCell([8, $row])->getValue());
            $date = DateTime::createFromFormat('d.m.Y H:i:s', $dateStr);
            $trade->created = $date ? $date->format('Y-m-d H:i:s') : null;
            $trade->isin = $worksheet->getCell([13, $row])->getValue();
            $trade->title = $worksheet->getCell([15, $row])->getValue();
            $trade->qty = (float)$worksheet->getCell([17, $row])->getValue();
            $trade->price = (float)$worksheet->getCell([19, $row])->getValue();
            $trade->total = (float)$worksheet->getCell([20, $row])->getValue();
            $trade->currency = $worksheet->getCell([23, $row])->getValue();
            $trade->comment = $worksheet->getCell([30, $row])->getValue();
            if ($trade->id) {
                $data[] = $trade;
            }
        }
        return $data;
    }

    public function parseMoney(Worksheet $worksheet): array
    {
        $data = [];
        for ($row = 20; $row <= $worksheet->getHighestDataRow(); ++$row) {
            $money = new Money;
            $createdStr = $worksheet->getCell([7, $row])->getValue();
            if ($createdStr) {
                $money->created = Date::excelToDateTimeObject($createdStr)->format('Y-m-d H:i:s');
            }
            $money->title = $worksheet->getCell([10, $row])->getValue();
            $money->comment = $worksheet->getCell([11, $row])->getValue();
            $total_rub = $worksheet->getCell([15, $row])->getValue();
            if ($total_rub) {
                $money->total = (float)$total_rub;
                $money->currency = 'RUB';
            }
            if (!$money->total) {
                $total_usd = $worksheet->getCell([18, $row])->getValue();
                if ($total_usd) {
                    $money->total = (float)$total_usd;
                    $money->currency = 'USD';
                }
            }
            if ($money->created) {
                $data[] = $money;
            }
        }
        return $data;
    }
}