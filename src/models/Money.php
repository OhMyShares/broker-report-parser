<?php
namespace ohmyshares\brokerReportParser\models;

class Money extends BaseModel
{
    public ?string $id = null;
    public ?string $created = null;
    public ?string $title = null;
    public ?float $total = null;
    public ?string $currency = null;
    public ?string $comment = null;
}