<?php

require __DIR__ . '/../vendor/autoload.php';

use Fiedsch\Data\Augmentation\Augmentor;
use Fiedsch\Data\Augmentation\Provider\QuotaCellServiceProvider;
use Fiedsch\Data\Augmentation\Provider\TokenServiceProvider;
use Fiedsch\Data\Augmentation\Provider\UniquenessCheckerServiceProvider;
use Fiedsch\Data\Augmentation\Provider\ValidationServiceProvider;
use Fiedsch\Data\Augmentation\Rules\TokenRule;
use Fiedsch\Data\File\CsvReader;
use Fiedsch\Data\File\CsvWriter;
use Fiedsch\Data\File\Helper;
use Fiedsch\Data\File\Reader;
use Fiedsch\Data\Utility\TokenCreator;
use Fiedsch\Data\Utility\VariablenameMapper;

//use Fiedsch\Data\Utility\Validator;

try {

    echo "running examples\n";

    $reader = new CsvReader("assets/testdata.csv", ";");

    // Handle the header with column names:
    // Use a VariablenameMapper to access columns by their name.

    print "will write to assets/testdata.augmented.csv\n";

    $writer = new CsvWriter("assets/testdata.augmented.txt", "\t");

    // Augment data using an Augmentor.

    $augmentor = new Augmentor();

    // The optional argument passed to the constructor can be an array of column
    // names that we expect to be set in the augmantation steps. If the steps fail
    // to set these columns an exception will be thrown.
    //
    // $augmentor = new Augmentor([Augmentor::KEY_COLNAMES=>['email','token','study_id']]);
    //
    // The same in two steps (propably easier to read):
    // $augmentor = new Augmentor();
    // $augmentor->setRequiredColumns(['email','token','study_id']);
    //
    // Can also be omitted if we do not want to perform this check.

    // Register basic services.

    $augmentor->register(new TokenServiceProvider(),
        [
            'token.case'   => TokenCreator::MIXED,
            'token.length' => 5 // lower the "minimum required length" as shorter tokens
            // will be read read from a file below. (12 === TokenCreator::DEFAULT_LENGTH)
            // If we do not do the we will get an error (\LogicException):
            // "tokens read from file are too short (current length setting is '12')."
        ]
    );
    $augmentor['token']->readFromFile("assets/tokens.txt"); // read a list of tokens from a file

    $augmentor->register(new UniquenessCheckerServiceProvider());
    $augmentor->register(new ValidationServiceProvider());
    $augmentor->register(new QuotaCellServiceProvider(), [
        'quota.targets' => [
            '089'  => 2,
            '0871' => 1,
        ],
    ]);

    // Set global variables.
    // They will be available via the $augmentor in the rule's callback.
    //
    $augmentor['study_id'] = 42;
    $augmentor['study_start'] = '2016-03-01';

    // Add augmentation rules to the Augmentor.
    // The order of the rules matters as each step
    // will be passed the results of the previous steps.

    // Step one. Verify the email address.
    // As an example we only transform it to upper case.

    $augmentor->addRule('email', function(Augmentor $augmentor, $data) {
        // if we need to access the resulst from previous augmentation steps (rules)
        // $augmented = $augmentor->getAugmentedSoFar();
        // otherwise initialize as empty array
        // $augmented = array();
        // or rely on PHP to properly handele uninitialized values.
        $email = Helper::getBySC($data, 'D');

        $augmented['valid_email'] = $augmentor['validation']->isValidEmail($email);
        $augmented['email'] = strtolower($email);
        $augmented['is_unique_email'] = $augmentor['unique']->isNew($email, 'email');
        $augmented['is_unique_name'] = $augmentor['unique']->isNew(Helper::getBySC($data, 'C'), 'name');
        $augmented['is_unique_id'] = $augmentor['unique']->isNew(Helper::getBySC($data, 'B'), 'id');
        // We could also use the mapper ($augmentor['mapper']) here instead of Helper::getBySC().
        // Use whichever seems more readable or easier to maintain.

        return $augmented;
    });

    // Step two: Add a unique token

    $augmentor->addRule('token', new TokenRule());

    // Drawing a sample with qouta for the ssmple cells

    $augmentor->addRule('qouta', function(Augmentor $augmentor) {
        $area_code = '0871'; // TODO extract from $augmentor>getAugmentedSoFar()
        $augmented['in_sample'] = $augmentor['quota']->add(1, $area_code) ? 1 : 0;
        return $augmented;
    });

    // Final step: add global variables

    $augmentor->addRule('globals', function(Augmentor $augmentor) {
        //$augmented = $augmentor->getAugmentedSoFar();
        $augmented['study_id'] = $augmentor['study_id'];
        $augmented['study_start'] = $augmentor['study_start'];
        return $augmented;
    });


    // Read and handle all lines containing data.
    // Empty lines are frequently generated when exporting data from a spreadsheet.
    // They will appear as somethig like ;;;;;; in the export file (when ; is the delimiter).

    // First, read the header and create a VariablenameMapper from the names found
    // register the mapper as we did use it in the augmentation rules above
    $reader->readHeader();
    $mapper = new VariablenameMapper($reader->getHeader());
    $augmentor['mapper'] = $mapper;

    // Next, the boilerplate code: iterate over all non empty lines and augment
    // and write new header (augmented columns plus original columsn)

    $header_written = false;

    while (($line = $reader->getLine(Reader::SKIP_EMPTY_LINES)) !== null) {
        //if (!$reader->isEmpty($line)) {
        $result = $augmentor->augment($line);

        if (!$header_written) {
            $writer->printLine(array_merge(['input_line'], array_keys($result), $reader->getHeader()));
            $header_written = true;
        }
        $writer->printLine(array_merge([$reader->getLineNumber()], $result, $line));
        //}
    }

    // $reader->close(); // not needed as it will be automatically called when there are no more lines
    $writer->close();

    // Summary of the data augmentation

    // print "Duplicates\n";
    // print_r($augmentor['unique']->getDuplicates());

    // print "sample cells\n";
    //print_r($augmentor['quota']->getCounts());

    print "done\n";
} catch (Exception $e) {
    print $e->getMessage() . "\n";
}
