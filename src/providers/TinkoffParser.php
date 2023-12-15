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

class TinkoffParser extends ParserResultResult
{
    public string $brokerId = 'tinkoff';
    public ?string $brokerName = 'Tinkoff';
    public ?string $brokerNameLocal = 'Тинькофф';

    public function parse(): ParserResultInterface|bool
    {
        try {
            $spreadsheet = $this->getSpreadsheet();
            $worksheet = $spreadsheet->getSheet(0);
            $this->parseTrades($worksheet);
            $this->parseMoney($worksheet);
            return $this;
        } catch (Exception $e) {
            $this->exceptionData = $e;
        }
        return false;
    }

    /**
     * @param Worksheet $worksheet
     */
    public function parseTrades(Worksheet $worksheet): void
    {
        [$start, $end] = $this->findRange($worksheet,
            '1.1 Информация о совершенных и исполненных сделках на конец отчетного периода',
            '1.3 Сделки за расчетный период, обязательства из которых прекращены  не в результате исполнения '
        );
        for ($i = $start + 2; $i < $end; $i++) {
            try {
                $trade = new Trade;
                $trade->id = $worksheet->getCell([1, $i])->getValue();
                $typeStr = $worksheet->getCell([24, $i])->getValue();
                $dateStr = $worksheet->getCell([10, $i])->getValue() . ' ' .
                    $worksheet->getCell([14, $i])->getValue();
                $date = DateTime::createFromFormat('d.m.Y H:i:s', $dateStr);
                $trade->created = $date ? $date->format('Y-m-d H:i:s') : null;
                $trade->title = $worksheet->getCell([30, $i])->getValue();
                $trade->isin = $worksheet->getCell([34, $i])->getValue();
                $priceStr = str_replace(',', '.', $worksheet->getCell([37, $i])->getValue());
                $trade->price = $priceStr ? (float)$priceStr : null;
                $trade->currency = $worksheet->getCell([42, $i])->getValue();
                $qtyStr = str_replace(',', '.', $worksheet->getCell([46, $i])->getValue());
                $trade->qty = $qtyStr ? (float)$qtyStr : null;
                if ($typeStr == 'Продажа') {
                    $trade->qty = -1 * $trade->qty;
                }
                $totalStr = str_replace(',', '.', $worksheet->getCell([62, $i])->getValue());
                $trade->total = $totalStr ? (float)$totalStr : null;
                $trade->comment = $typeStr.' '.$trade->title;
                if ($trade->created) {
                    $this->trades[] = $trade;
                }
            } catch (Exception $e) {
                $temp = ['row' => $i, 'message' => $e->getMessage()];
                $this->errors[] = $temp;
                continue;
            }
        }
    }

    public function parseMoney(Worksheet $worksheet): void
    {
        [$start, $end] = $this->findRange($worksheet,
            '2. Операции с денежными средствами',
            '3.1 Движение по ценным бумагам инвестора'
        );
        $currency = '-';

        // looking for first money row
        for ($i = $start; $i < $end; $i++) {
            $currency = $worksheet->getCell([1, $i-1])->getValue();
            $cellValueFirstColumn = $worksheet->getCell([1, $i])->getValue();
            if ($cellValueFirstColumn != 'Дата') {
                continue;
            }
            $start = $i + 1;
            break;
        }

        for ($i = $start; $i < $end; $i++) {
            $cellValueFirstColumn = $worksheet->getCell([1, $i])->getValue();
            if ($cellValueFirstColumn == 'Дата') {
                $currency = $worksheet->getCell([1, $i-1])->getValue();
                continue;
            }
            try {
                $created = $worksheet->getCell([35, $i])->getValue();
                $date = DateTime::createFromFormat('d.m.Y', $created);
                if ($date) {
                    $date->setTime(0, 0);
                }
                $title = $worksheet->getCell([56, $i])->getValue();
                $sumIn = str_replace(',', '.', $worksheet->getCell([100, $i])->getValue());
                $sumOut = str_replace(',', '.', $worksheet->getCell([80, $i])->getValue());
                $comment = $worksheet->getCell([84, $i])->getValue();

                if ($created !== null) {
                    $money = new Money;
                    $money->created = $date->format('Y-m-d H:i:s');
                    $money->currency = $currency;
                    $money->total = floatval($sumOut) - floatval($sumIn);
                    $money->title = $title;
                    $money->comment = $comment;
                    $this->money[] = $money;
                }
            } catch (Exception $e) {
                $temp = ['row' => $i, 'message' => $e->getMessage()];
                $this->errors[] = $temp;
                continue;
            }
        }
    }

    /**
     * Finds row number containing find_start and row number containing find_end
     */
    private function findRange(Worksheet $worksheet, string $findStart, string $findEnd): array
    {
        $start = 0;
        $end = 0;
        for ($i = 1; $i <= $worksheet->getHighestRow(); $i++) {
            $cellValue = $worksheet->getCell([1, $i])->getValue();
            if ($cellValue == $findStart) {
                $start = $i;
            }
            if ($cellValue == $findEnd) {
                $end = $i;
            }
        }
        return [$start, $end];
    }

    private function findIsinByTicker($worksheet, $ticker) {
        [$start, $end] = $this->findRange($worksheet,
            '4.1 Информация о ценных бумагах',
            '4.2 Информация об инструментах, не квалифицированных в качестве ценной бумаги'
        );
        for ($i = $start + 2; $i < $end - 2; $i++) {
            try {
                $code = $worksheet->getCellByColumnAndRow(17, $i)->getValue();
                $isin = $worksheet->getCellByColumnAndRow(31, $i)->getValue();
                if ($code == $ticker) {
                    return $isin;
                }
            } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
                continue;
            }
        }
        return null;
    }
}