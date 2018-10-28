<?php

require __DIR__ . '/../vendor/autoload.php';

use Fiedsch\Data\Utility\SqlCodeGenerator;
// use Symfony\Component\Yaml\Yaml;

$inputfile = './assets/testdata.csv';

$config = [
    'table' => 'testtable',
    'types' => [
            'default' => 'DOUBLE',
            'string'  => 'VARCHAR(64)',
            'integer' => 'int(10)',
        ],
    'columns' => [
        'empty' => 'integer',
        'id'    => 'integer',
        'name'  => 'VARCHAR(128)',
        'email' => 'string',
        'phone' => 'VARCHAR(32)',
    ]
];
// or read from YAML file
//$config = Yaml::parse(file_get_contents($configfile));

$generator = new SqlCodeGenerator($inputfile, $config);
print $generator->getDropTable();
print $generator->getCreateTable();
print $generator->getInsertStatements();