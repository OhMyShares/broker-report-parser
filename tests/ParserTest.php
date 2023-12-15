<?php
namespace ohmyshares\brokerReportParser\tests;

use ohmyshares\brokerReportParser\Parser;
use ohmyshares\brokerReportParser\providers\AlfaParser;
use ohmyshares\brokerReportParser\providers\TinkoffParser;
use PHPUnit\Framework\TestCase;

/**
 * Class ParserTest
 * Common tests for Parser class
 */
final class ParserTest extends TestCase
{
    public function testBrokersAvailable()
    {
        $brokersList = Parser::brokersAvailable();
        $this->assertIsArray($brokersList);
        $this->assertArrayHasKey('alfa', $brokersList);
        $this->assertArrayHasKey('tinkoff', $brokersList);
    }

    public function testCreation()
    {
        $p = new Parser('');
        $this->assertFalse($p->isParsingAvailable());

        $p = new Parser(__DIR__.'/reports/alfa01.xls');
        $this->assertInstanceOf(AlfaParser::class, $p->parser);
        $this->assertTrue($p->isParsingAvailable());

        $p = new Parser(__DIR__.'/reports/tinkoff01.xlsx');
        $this->assertInstanceOf(TinkoffParser::class, $p->parser);
        $this->assertTrue($p->isParsingAvailable());
    }
}