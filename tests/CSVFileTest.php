<?php
/**
 * Commie: CSV Traversal Library
 * @license BSD-3-Clause
 * @author Justin Rovang <generate@itnobody.com>
 */

namespace Commie\Tests;

use Commie\CSVFile;
use SplFileObject;

class CSVFileTest extends TestBase
{

    public function testEOFWithHeaders(): void
    {

        $f = new SplFileObject(__DIR__. "/sample.csv");

        $csv = new CSVFile($f, TRUE);
        $csv->setDelimiter('|');

        $records = array();

        while (($row = $csv->read()) != FALSE) {

            $records[] = $row->col('first')->value();
        }

        $this->assertCount(4, $records);
    }
}
