<?php
namespace ohmyshares\brokerReportParser;

interface ParserResultInterface
{
    public function parse(): ParserResultInterface|bool;
}