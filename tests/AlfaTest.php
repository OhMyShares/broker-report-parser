<?php
namespace ohmyshares\brokerReportParser\tests;

use ohmyshares\brokerReportParser\Parser;
use ohmyshares\brokerReportParser\providers\AlfaParser;
use ohmyshares\brokerReportParser\providers\TinkoffParser;
use PHPUnit\Framework\TestCase;

final class AlfaTest extends TestCase
{
    public function testAlfa()
    {
        $p = new Parser(__DIR__.'/reports/alfa01.xls');
        $result = $p->parse();
        $this->assertEquals('Alfa-bank', $result->brokerName);
        $this->assertIsArray($result->trades);
        $this->assertIsArray($result->money);
        $this->assertIsArray($result->toArray());
        $this->assertCount(100, $result->trades);
        $this->assertCount(21, $result->money);

        $trade = $result->trades[0];
        $this->assertEquals('5554444 222233333', $trade->id);
        $this->assertEquals('2018-11-19 10:09:09', $trade->created);
        $this->assertEquals('US00507V1098', $trade->isin);
        $this->assertEquals('Activision Blizzard, Inc., а.о.', $trade->title);
        $this->assertEquals(4, $trade->qty);
        $this->assertEquals(51.17, $trade->price);
        $this->assertEquals(204.68, $trade->total);
        $this->assertEquals('USD', $trade->currency);

        $money = $result->money[0];
        $this->assertEquals('2018-11-15 04:35:38', $money->created);
    }
}