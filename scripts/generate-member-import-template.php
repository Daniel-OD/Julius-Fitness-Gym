<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

$directory = __DIR__.'/../public/templates';
$path = $directory.'/membri-template.xlsx';

if (! is_dir($directory)) {
    mkdir($directory, 0755, true);
}

$writer = new Writer;
$writer->openToFile($path);

$writer->addRow(Row::fromValues([
    'Prenume',
    'Nume',
    'Email',
    'Telefon',
    'Data nasterii',
    'Abonament',
    'Cost',
    'Data start',
    'Data expirare',
    'Note',
]));

$writer->addRow(Row::fromValues([
    'Andrei',
    'Popescu',
    'andrei.popescu@example.ro',
    '0721234567',
    '15/03/1990',
    'Lunar',
    '150',
    '01/06/2026',
    '01/07/2026',
    'Abonament anual',
]));

$writer->addRow(Row::fromValues([
    'Maria',
    'Ionescu',
    'maria.ionescu@example.ro',
    '0732987654',
    '22/07/1985',
    'Trimestrial',
    '400',
    '01/06/2026',
    '01/09/2026',
    '',
]));

$writer->addRow(Row::fromValues([
    'George',
    'Dumitrescu',
    'george.dumitrescu@example.ro',
    '0744112233',
    '01/11/1992',
    'Lunar',
    '150',
    '',
    '',
    'Preferă antrenament dimineața',
]));

$writer->close();

echo "Wrote {$path}".PHP_EOL;
