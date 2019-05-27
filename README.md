Commie: CSV Traversal Library
======
---

## Introduction
Commie is a simplistic object-orientated wrapper around PHP's built in CSV handling.

The key difference with Commie is that it's the first library that will allow you to traverse the file's columns by name - as well 
as painlessly jump around the file in an efficient manner. 

There's a few antique packages out in the wild that handle CSV files, however all of them seem to be lacking in one way or 
another, and don't lend themselves well to customization for the wild formats delimited data can come in. Commie implements a 
'mapper' concept that allows you to do whatever strange things you need in addition to the core for traversal of your data. 

## Quick start

```php
<?php

require_once 'Commie/_config.php';

use Commie\CSVFile;

/* ^-- Not needed if you use a PSR-0 autoloader, just register it normally */

$file = new SplFileObject('./file.csv');
$csv = new CSVFile($file, TRUE);

while (($row = $csv->read())) {
    echo $row->col('My Heading')->value();
}

echo $csv->row(10)->col('TOTAL')->value();

```


## Further details

You can examine the source, or you can generate the documentation using apigen 
(Once apigen 3.00 is released the docs will be committed as markdown for github)