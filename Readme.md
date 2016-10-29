# Datamanagement Tools

PHP classes and helpers for managing data read from text files
 
 * Data\FileReader  read text files
 * Data\CsvFileReader read CSV files
 * Data\Helper helper functions like `SC()` that converts from spreadsheet column name to index of array 
 generated by (e.g.) `CsvFileReader->getLine()`
 
 
## Examples
 
### Work on CSV data
 
```php
<?php

require __DIR__ . '/../vendor/autoload.php';

use Fiedsch\Data\File\CsvReader;
 
try {
 
  $reader = new CsvReader("testdata.csv", ";");

  // Read and handle all lines containing data.

  while (($line = $reader->getLine()) !== null) {
    // ignore empty lines (i.e. lines containing no data)
    if (!$reader->isEmpty($line)) {
      print_r($line);
    }
  }
  $reader->close();

} catch (Exception $e) {
    print $e->getMessage() . "\n";
}
```

#### Features

Aas of v0.3.2 the typical boilerplate "open file, read every non-empty line, close file" 
can be written in a fancier way. Use the optional parameter to `getLine()`:
 
 ```php
 <?php
 
   while (($line = $reader->getLine(Reader::SKIP_EMPTY_LINES)) !== null) {
       print_r($line);
   }
   
 ```
 
 
### Data augmentation
 
 
```php
<?php
 
require __DIR__ . '/../vendor/autoload.php';
 
use Fiedsch\Data\File\CsvReader;
use Fiedsch\Data\Augmentation\Augmentor;
use Fiedsch\Data\Augmentation\Provider\TokenServiceProvider;
use Fiedsch\Data\File\CsvWriter;
  
try {

  $augmentor = new Augmentor();
 
  $augmentor->register(new TokenServiceProvider());
  
  $augmentor->addRule('token', function (Augmentor $augmentor, $data) {
     return [ 'token' => $augmentor['token']->getUniqueToken() ];
   });
  
   $reader = new CsvReader("testdata.csv", ";");
   
   $writer = new CsvWriter("testdata.augmented.txt", "\t");
   
   $header_written = false;
   
   $reader->readHeader();
   
   while (($line = $reader->getLine()) !== null) {
     if (!$reader->isEmpty($line)) {
       $result = $augmentor->augment($line);
       if (!$header_written) {
          $writer->printLine(array_merge(['input_line'], array_keys($result), $reader->getHeader()));
          $header_written = true;
       }
       $writer->printLine(array_merge([$reader->getLineNumber()], $result, $line));
     }
   }
   
   // $reader->close(); // not needed as it will be automatically called when there are no more lines
   $writer->close();
 
 } catch (Exception $e) {
     print $e->getMessage() . "\n";
 }
 ```
 