# Broker report parser

## Requirements

- `php 8.2` or higher

## Dependencies

This packaged depends on:

- `phpoffice/phpspreadsheet` for xls/xlsx files

## Installation

- Add in repositories of your composer.json:

- Run `composer require ohmyshares/broker-report-parser`

## Usage

```php
// automatically detect parser depending on file
$parser = new Parser($filePath);
if ($parser) {
    $results = $parser->parse();
}
```

List of supported parsers (available brokers):
```php
$brokersList = Parser::brokersAvailable();
```

## Testing
`./vendor/bin/phpunit ./tests`