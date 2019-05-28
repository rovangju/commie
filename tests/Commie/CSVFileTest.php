<?php

namespace Commie;


use PHPUnit_Framework_TestCase;
use SplFileObject;

class CSVFileTest extends PHPUnit_Framework_TestCase
{

    public function testEOFWithHeaders()
    {

        $f = new SplFileObject(__DIR__. "/sample.csv");

        $csv = new CSVFile($f, TRUE);
        $csv->setDelimiter('|');

        $records = array();

        while (($row = $csv->read()) == TRUE) {

            $records[] = $row->col('first')->value();
        }

        $this->assertEquals(
            4,
            count($records)
        );
    }
}