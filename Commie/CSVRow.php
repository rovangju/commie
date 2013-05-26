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
    public $rowData = array();

    /**
     * Indicator for the row offset in the CSV file
     *
     * @var integer
    */
    protected $rowIdx;

    /**
     * @var CSVColMapper
     */
    public $mapper;

    /**
     * Construct a CSVRow object
     *
     * @param CSVColMapper $mapper  Column mapper
     * @param integer      $idx     Row offset
     * @param array        $rowData
     */
    public function __construct(CSVColMapper $mapper, $idx, array $rowData) {

        $this->rowIdx = $idx;
        $this->rowData = $rowData;

        $this->mapper = $mapper;
    }

    /**
     * Determine the row offset of the CSV file
     *
     * @return integer zero-based line delimited offset of CSV file
     */
    public function offset() {
        return $this->rowIdx;
    }

    /**
     * Determine if the row is empty or void of any values. This is handy for scenarios of CSV files that may have
     * additional lines for no reason
     *
     * @return boolean TRUE if it's empty, FALSE if values are present
     */
    public function isEmpty() {
        return empty($this->rowData);
    }

    /**
     * Retreive a column object for the given key - if the column could not be resolved by the provided
     * key an exception is thrown
     *
     * @param string|integer $key Offset or label to reference the column by and retrieve data
     *
     * @throws OutOfRangeException Thrown when the column key could not be resolved for the row
     *
     * @return \Commie\CSVCol
     */
    public function col($key) {

        if (!$this->isCol($key)) {
            throw new OutOfRangeException("Column key: ".$key." - could not be resolved.");
        }
        
        return $this->mapper->factory(
            $this->rowData[
                $this->mapper->resolve($key)
            ]
        );
    }

    /**
     * Determine if a column (index or by label) exists in the CSV file.
     * Using this before using col() is superfluous as col() implicitely calls isCol()
     * and throws an exception on FALSE return of this method.
     *
     * @param string|integer $reference The label or index to determine exists
     *
     * @return boolean TRUE if the column exists
     */
    public function isCol($key) {

        if ($this->mapper->resolve($key) !== NULL) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Retrieve the raw array data from the CSV row
     * 
     * @return array
     */
    public function raw() {
    
        return array_combine(
            $this->mapper->labels(),
            $this->rowData
        );
    }
}
?>