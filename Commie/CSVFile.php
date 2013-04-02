<?php

namespace Commie;

use \Exception;
use \InvalidArgumentException;
use \RuntimeException;

use \SplFileObject;

/**
 * The CSVFile object is intended to be a mere OO wrapper built around an SplFileObject. 
 * It provides an iteratable implementation for the file while providing an interface for dealing with the 
 * corresponding rows and columns of a CSV file.
 * 
 * <b>Basic use:</b>
 * <code>
 * $file = new SplFileObject('./file.csv');
 * $csv = new CSVFile($file, TRUE);
 * 
 * while (($row = $csv->read()) {
 *     echo $row->col('My Heading');
 * }
 * </code>
 * 
 * <b>Misc. use cases:</b>
 * 
 * <code>
 * $file = new SplFileObject('./file.csv');
 * $csv = new CSVFile($file, TRUE);
 * 
 * $csv->setDelimiter("|");
 * 
 * echo $csv->row(10)->col('TOTAL');
 * </code>
 * 
 * @package commie
 */

class CSVFile {

    /* Defaults as per PHP.net */
    protected $delimiter = ",";
    protected $enclosure = '"';
    protected $escape = "\\";

    protected $headersPresent;

    protected $lastRow = 0;

    /**
     * @var SplFileObject
     */
    protected $file;

    /**
     * @var CSVColMapper
     */
    protected $mapper;

    public function __construct(SplFileObject $file, $headersPresent = FALSE) {

        $this->file =& $file;

        $file->setFlags(SplFileObject::READ_CSV);

        $this->headersPresent = (bool)$headersPresent;
    }

    /**
     * Set the column object mapper to operate on rows
     * 
     * @param CSVColMapper $mapper Corresponding mapper
     * 
     * @return NULL
     */
    public function setMapper(CSVColMapper $mapper) {
        $this->mapper = $mapper;
    }

    /**
     * Determine if the file has a header row present
     * 
     * @return boolean TRUE if a header row is present
     */
    public function hasHeaders() {
        return $this->headersPresent;
    }
    
    /**
     * Set the delimiter character for values. This method is a mere passthrough for SplFileObject's
     * setCsvControl() method
     *
     * @param string $enclosure The field delimiter (one character only). 
     *
     * @throws Exception Thrown if the object has somehow not been initialized with an SplFileObject instance
     * @throws InvalidArgumentException Thrown if the delimiter string is longer than one character
     *
     * @see http://us3.php.net/manual/en/splfileobject.setcsvcontrol.php
     *
     * @return void
     */
    public function setDelimiter($delim) {

        if (!($this->file instanceof SplFileObject)) {
            throw new RuntimeException('Delmiter must be set after initializing an instance');
        }

        if (strlen($delim) > 1) {
            throw new InvalidArgumentException("Delimiter must be a single character");
        }

        $this->file->setCsvControl($delimiter);
        $this->delimiter = $delimiter;

    }
    
    /**
     * Set the enclosing character for values. This method is a mere passthrough for SplFileObject's
     * setCsvControl() method
     *
     * @param string $enclosure The field enclosure character (one character only). 
     *
     * @throws Exception Thrown if the object has somehow not been initialized with an SplFileObject instance
     * @throws InvalidArgumentException Thrown if the enclosure string is longer than one character
     *
     * @see http://us3.php.net/manual/en/splfileobject.setcsvcontrol.php
     *
     * @return void
     */
    public function setEnclosing($enclosure) {

        if (!($this->file instanceof SplFileObject)) {
            throw new Exception('Enclosure character must be set after initializing an instance');
        }

        if (strlen($enclosure) > 1) {
            throw new InvalidArgumentException("Encloser must be a single character");
        }

        $this->file->setCsvControl($this->delimiter, $enclosure);
        $this->enclosure = $enclosure;
    }

    /**
     * Set the escape character for values. This method is a mere passthrough for SplFileObject's 
     * setCsvControl() method
     * 
     * @param string $escape The field escape character (one character only)
     * 
     * @throws Exception Thrown if the object has somehow not been initialized with an SplFileObject instance
     * @throws InvalidArgumentException Thrown if the escape string is longer than one character
     * 
     * @see http://us3.php.net/manual/en/splfileobject.setcsvcontrol.php
     * 
     * @return void
     */
    public function setEscape($escape) {

        if (!($this->file instanceof SplFileObject)) {
            throw new Exception('Escape character must be set after initializing an instance');
        }

        if (strlen($escape) > 1) {
            throw new InvalidArgumentException("Escape character must be a single character");
        }

        $this->file->setCsvControl($this->delimiter, $this->enclosure, $escape);
        $this->escape = $escape;
    }

    /**
     * Retrieve the underling file object
     * 
     * @return SplFileObject
     */
    public function file() {
        return $this->file;
    }

    protected function getMapper() {

        if (!$this->mapper) {
            	
            $curIdx = $this->file()->key();
            	
            $this->file()->seek(0);

            $this->setMapper(
                new CSVColMapper($this->file()->current(), $this->hasHeaders())
            );
            	
            $this->file()->seek($curIdx);
        }

        return $this->mapper;
    }

    /**
     * Cherry pick a specific row at the given zero-based row index. This method will set the internal pointer
     * to the specified index, and return it to the original value. If the index is higher than the number
     * of rows in the file, the last row is returned
     * 
     * @param integer $idx Row offset
     * 
     * @throws InvalidArgumentException Thrown if the index isn't an integer
     * 
     * @return \Commie\CSVRow
     */
    public function row($idx) {

        if (!is_int($idx)) {
            throw new InvalidArgumentException("Row index must be an integer");
        }

        $this->lastRow = $this->file()->key();

        $this->file()->seek($idx);

        $rval = new CSVRow(
            $this->getMapper(),
            $idx,
            $this->file->current()
        );

        $this->file()->seek($this->lastRow);

        return $rval;
    }

    /**
     * Reset the internal file pointer
     * 
     * @return NULL
     */
    public function reset() {
        $this->file()->rewind();
    }

    
    /**
     * Read the current row of a CSV file, move the internal pointer forward and return CSVRow object. 
     * if the file is EOF this method returns FALSE in order to support usage in a while loop.
     * 
     * <code>
     * $file = new SplFileObject('./file.csv');
     * 
     * $csv = new CSVFile($file, TRUE); 
     * 
     * while (($row = $csv->read()) == TRUE) {
     *     echo $row->col(0);
     * }
     * </code> 
     * 
     * @return false|\Commie\CSVRow
     */
    public function read() {

        if ($this->file()->eof() && $this->lastRow == $this->file()->key()) {
            return FALSE;
        }


        if ($this->file()->key() == 0 && $this->hasHeaders()) {
            $this->file()->seek(1);
        }

        $rval = new CSVRow(
            $this->getMapper(),
            $this->file()->key(),
            $this->file->current()
        );

        $this->lastRow = $this->file()->key();

        if (!$this->file()->eof()) {
            $this->file()->next();
        }

        return $rval;
    }
}

?>