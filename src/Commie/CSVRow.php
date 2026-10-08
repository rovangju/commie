<?php

namespace Commie;

use OutOfRangeException;

/**
 * CSVRow objects represent a row within a CSV file which serves as the conduit to access and manipulate
 * values (CSVCol).
 *
 * @package Commie
 */
class CSVRow {

    /**
     * Internal storage for row values
     *
     * @var array
     */
    public array $rowData = array();

    /**
     * Indicator for the row offset in the CSV file
     *
     * @var int
     */
    protected int $rowIdx;

    public CSVColMapper $mapper;

    /**
     * Construct a CSVRow object
     *
     * @param CSVColMapper $mapper Column mapper
     * @param int          $idx    Row offset
     * @param array        $rowData Row values
     */
    public function __construct(CSVColMapper $mapper, int $idx, array $rowData) {

        $this->rowIdx = $idx;
        $this->rowData = $rowData;

        $this->mapper = $mapper;
    }

    /**
     * Determine the row offset of the CSV file
     *
     * @return int Zero-based line delimited offset of CSV file
     */
    public function offset(): int {
        return $this->rowIdx;
    }

    /**
     * Determine if the row is empty or void of any values. This is handy for scenarios of CSV files that may have
     * additional lines for no reason
     *
     * @return bool TRUE if it's empty, FALSE if values are present
     */
    public function isEmpty(): bool {
        return empty($this->rowData);
    }

    /**
     * Retrieve a column object for the given key - if the column could not be resolved by the provided
     * key an exception is thrown
     *
     * @param string|int $key Offset or label to reference the column by and retrieve data
     *
     * @throws OutOfRangeException Thrown when the column key could not be resolved for the row
     *
     * @return CSVCol
     */
    public function col(string|int $key): CSVCol {

        if (!$this->isCol($key)) {
            throw new OutOfRangeException("Column key: ".$key." - could not be resolved.");
        }

        return $this->mapper->factory(
            $this->rowData[$this->mapper->resolve($key)]
        );
    }

    /**
     * Determine if a column (index or by label) exists in the CSV file.
     * Using this before using col() is superfluous as col() implicitly calls isCol()
     * and throws an exception on FALSE return of this method.
     *
     * @param string|int $key The label or index to determine exists
     *
     * @return bool TRUE if the column exists
     */
    public function isCol(string|int $key): bool {
        return $this->mapper->resolve($key) !== NULL;
    }
}
