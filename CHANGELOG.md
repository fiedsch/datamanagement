# Changes

## Development

## Version 0.8.0

* `Data\File\CsvReader` now uses `League\Csv\Reader` internally.
  * we require the input file to have a header line
  * we now can handle fields containing line breaks
  * `CsvFileReader`->readHeader()` is now deprecated 
* Updated example
* Updated tests
