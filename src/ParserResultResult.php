<?php
namespace ohmyshares\brokerReportParser;

use Exception;
use ohmyshares\brokerReportParser\models\Money;
use ohmyshares\brokerReportParser\models\Trade;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

abstract class ParserResultResult implements ParserResultInterface
{
    /**
     * @var string broker id
     */
    public string $brokerId = '';

    /**
     * @var string|null broker name
     */
    public ?string $brokerName = null;

    /**
     * @var string|null broker name in local language
     */
    public ?string $brokerNameLocal = null;

    /**
     * @var Trade[]|null list of trading operations
     */
    public ?array $trades = null;

    /**
     * @var Money[]|null list of money operations
     */
    public ?array $money = null;

    /**
     * @var array|null list of errors
     */
    public ?array $errors = null;

    /**
     * @var Exception|null stores exception data when parsing fails
     */
    public ?Exception $exceptionData = null;

    private Spreadsheet $_spreadsheet;

    /**
     * @param Spreadsheet $spreadsheet loaded Excel file object
     */
    public function __construct(Spreadsheet $spreadsheet)
    {
        $this->_spreadsheet = $spreadsheet;
    }

    public function getSpreadsheet(): Spreadsheet
    {
        return $this->_spreadsheet;
    }

    public function toArray(): array
    {
        return [
            'brokerName' => $this->brokerName,
            'trades' => $this->trades,
            'money' => $this->money,
        ];
    }
}