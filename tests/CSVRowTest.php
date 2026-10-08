<?php

namespace Commie\Tests;

use Commie\CSVColMapper;
use Commie\CSVRow;

class CSVRowTest extends TestBase
{
    /**
     * @var CSVRow
     */
    protected $object;

    /**
     * @var CSVColMapper
     */
    protected $mapper;

    protected function setUp(): void
    {
        $data = str_getcsv('"Alpha","Beta","Charlie"', ',', '"', ''); /* Simulate first row of data */

        $this->mapper = new CSVColMapper(
            $data,
            TRUE
        );

        $this->object = new CSVRow($this->mapper, 0, $data);
    }

    public function testOffset(): void
    {
        $this->assertSame(0, $this->object->offset());
    }

    public function testCol(): void
    {
        $tData = str_getcsv('"Value1","Value2",""', ',', '"', '');
        $tRow = new CSVRow($this->mapper, 1, $tData);
        $this->assertEquals("Value2", $tRow->col('Beta')->value());
    }

    public function testIsCol(): void
    {
        $this->assertTrue($this->object->isCol(0));
        $this->assertFalse($this->object->isCol(9999));

        $tData = str_getcsv('"Value1","Value2",""', ',', '"', '');

        $tRow = new CSVRow($this->mapper, 1, $tData);
        $this->assertTrue($tRow->isCol('Alpha'));
    }
}
