# Changes

## Development

## Version 1.3.0

* Assert compatibility with `league/csv` for versions `>= 9.12`


## Version 1.2.0

* Add type hints


## Version 1.1.0

* PHP 8.1 compatibility


## Version 1.0.1

* new helper function `overwriteValue($key, $value)`


## Version 1.0.0

* `Data\File\CsvReader` now uses `League\Csv\Reader` internally.
  * we require the input file to have a header line
  * we now can handle fields containing line breaks
  * `CsvFileReader`->readHeader()` is now deprecated 
* Updated example
* Updated tests
