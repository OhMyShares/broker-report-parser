<?php
namespace ohmyshares\brokerReportParser\models;

class BaseModel
{
    public function getHash(): string
    {
        return md5(serialize($this));
    }
}