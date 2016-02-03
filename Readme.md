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
 
### Data augmentation
 
 
```php
<?php
 
 require __DIR__ . '/../vendor/autoload.php';
 
use Fiedsch\Data\File\CsvReader;
use Fiedsch\Data\Augmentation\Augmentor;
use Fiedsch\Data\Augmentation\Provider\TokenServiceProvider;
  
try {

  $augmentor = new Augmentor();
 
  $augmentor->register(new TokenServiceProvider());
  
  $augmentor->addRule('token', function (Augmentor $augmentor, $data) {
     $augmented['token'] = $augmentor['token']->getUniqueToken();
     return $augmented;
   });
  
   $reader = new CsvReader("testdata.csv", ";");
   $out = fopen('php://output', 'w');
 
   while (($line = $reader->getLine()) !== null) {
     if (!$reader->isEmpty($line)) {
       $result = $augmentor->augment($line);
       fputcsv($out, array_merge($result['augmented'], $result['data']));
     }
   }
   fclose($out);
   $reader->close();
 
 } catch (Exception $e) {
     print $e->getMessage() . "\n";
 }
 ```
 