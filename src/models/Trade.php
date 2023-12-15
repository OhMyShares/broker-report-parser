<?php
namespace ohmyshares\brokerReportParser\models;

class Trade extends BaseModel
{
    public ?string $id = null;
    public ?string $created = null;
    public ?string $title = null;
    public ?string $isin = null;
    public ?float $qty = null;
    public ?float $price = null;
    public ?float $total = null;
    public ?string $currency = null;
    public ?string $comment = null;
}