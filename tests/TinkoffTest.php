<?php
namespace ohmyshares\brokerReportParser\tests;

use ohmyshares\brokerReportParser\Parser;
use ohmyshares\brokerReportParser\providers\AlfaParser;
use ohmyshares\brokerReportParser\providers\TinkoffParser;
use PHPUnit\Framework\TestCase;

final class TinkoffTest extends TestCase
{
    public function testTinkoff()
    {
        $p = new Parser(__DIR__.'/reports/tinkoff01.xlsx');
        $result = $p->parse();
        $this->assertEquals('Tinkoff', $result->brokerName);
        $this->assertIsArray($result->trades);
        $this->assertCount(23, $result->trades);
        $this->assertCount(41, $result->money);

        $trade = $result->trades[0];
        $this->assertEquals('D048859610', $trade->id);
        $this->assertEquals('2023-01-13 17:47:32', $trade->created);
        $this->assertEquals('АЛРОСА ао', $trade->title);
        $this->assertEquals('RU0007252813', $trade->isin);
        $this->assertEquals(100, $trade->qty);
        $this->assertEquals(60.0999, $trade->price);
        $this->assertEquals(6009.99, $trade->total);
        $this->assertEquals('RUB', $trade->currency);
        $this->assertEquals('Покупка АЛРОСА ао', $trade->comment);

        $money = $result->money[0];
        $this->assertEquals('2023-01-17 00:00:00', $money->created);
        $this->assertEquals('Комиссия за сделки', $money->title);
        $this->assertEquals(-18.03, $money->total);
        $this->assertEquals('RUB', $money->currency);
        $this->assertEquals('', $money->comment);
    }
}