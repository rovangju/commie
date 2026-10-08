Commie: CSV Traversal Library
======
---

[![Tests](https://github.com/rovangju/commie/actions/workflows/tests.yml/badge.svg)](https://github.com/rovangju/commie/actions/workflows/tests.yml)

## Introduction
Commie is a simplistic object-orientated wrapper around PHP's built in CSV handling.

The key difference with Commie is that it's the first library that will allow you to traverse the file's columns by name - as well
as painlessly jump around the file in an efficient manner.

There's a few antique packages out in the wild that handle CSV files, however all of them seem to be lacking in one way or
another, and don't lend themselves well to customization for the wild formats delimited data can come in. Commie implements a
'mapper' concept that allows you to do whatever strange things you need in addition to the core for traversal of your data.

## Requirements

- PHP 8.1 or later

## Quick start

```bash
composer require rovangju/commie
```

```php
<?php

require_once 'vendor/autoload.php';

use Commie\CSVFile;

$file = new SplFileObject('./file.csv');
$csv = new CSVFile($file, TRUE);

while (($row = $csv->read())) {
    echo $row->col('My Heading')->value();
}

echo $csv->row(10)->col('TOTAL')->value();

```

## Contributing

Please follow the [Git Flow](https://github.com/nvie/gitflow) conventions. Proposals should be performed against develop or a feature/bugfix/support branch to be merged in by the maintainer.

Releases/versioning semantics follow the [Semantic Versioning](http://semver.org) 2.0.x guidelines. Minute adjustments (e.g.: changes to this README.md) may or may not result in a new version tag, depending on the nature of the change.

## Further details

You can examine the source, or run the test suite with:

```bash
composer test
```
