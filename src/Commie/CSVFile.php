<?php
/**
 * Commie: CSV Traversal Library
 * @license BSD-3-Clause
 * @author Justin Rovang <generate@itnobody.com>
 */

namespace Commie;

use InvalidArgumentException;
use SplFileObject;

/**
 * The CSVFile object is intended to be a mere OO wrapper built around an SplFileObject.
 * It provides an iterable implementation for the file while providing an interface for dealing with the
 * corresponding rows and columns of a CSV file.
 *
 * <b>Basic use:</b>
 * <code>
 * $file = new SplFileObject('./file.csv');
 * $csv = new CSVFile($file, TRUE);
 *
 * while (($row = $csv->read())) {
 *     echo $row->col('My Heading')->value();
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
 * echo $csv->row(10)->col('TOTAL')->value();
 * </code>
 *
 * @package commie
 */
class CSVFile {

    /* Defaults as per PHP.net */
    protected string $delimiter = ",";
    protected string $enclosure = '"';
    protected string $escape = "\\";

    protected bool $headersPresent;

    protected int $lastRow = 0;

    protected SplFileObject $file;

    protected ?CSVColMapper $mapper = null;

    /**
     * Construct a CSVFile wrapper around an SplFileObject
     *
     * @param SplFileObject $file           File object to wrap
     * @param bool          $headersPresent TRUE if the file carries a header row
     */
    public function __construct(SplFileObject $file, bool $headersPresent = FALSE) {

        $this->file = $file;

        $file->setFlags(
            SplFileObject::READ_CSV
            | SplFileObject::READ_AHEAD
            | SplFileObject::SKIP_EMPTY
            | SplFileObject::DROP_NEW_LINE
        );

        $this->headersPresent = $headersPresent;
    }

    /**
     * Set the column object mapper to operate on rows
     *
     * @param CSVColMapper $mapper Corresponding mapper
     */
    public function setMapper(CSVColMapper $mapper): void {
        $this->mapper = $mapper;
    }

    /**
     * Determine if the file has a header row present
     *
     * @return bool TRUE if a header row is present
     */
    public function hasHeaders(): bool {
        return $this->headersPresent;
    }

    /**
     * Set the delimiter character for values. This method is a mere passthrough for SplFileObject's
     * setCsvControl() method
     *
     * @param string $delim The field delimiter (one character only)
     *
     * @throws InvalidArgumentException Thrown if the delimiter string is longer than one character
     *
     * @see https://www.php.net/manual/en/splfileobject.setcsvcontrol.php
     */
    public function setDelimiter(string $delim): void {

        if (strlen($delim) > 1) {
            throw new InvalidArgumentException("Delimiter must be a single character");
        }

        $this->delimiter = $delim;
        $this->applyCsvControl();
    }

    /**
     * Set the enclosing character for values. This method is a mere passthrough for SplFileObject's
     * setCsvControl() method
     *
     * @param string $enclosure The field enclosure character (one character only)
     *
     * @throws InvalidArgumentException Thrown if the enclosure string is longer than one character
     *
     * @see https://www.php.net/manual/en/splfileobject.setcsvcontrol.php
     */
    public function setEnclosing(string $enclosure): void {

        if (strlen($enclosure) > 1) {
            throw new InvalidArgumentException("Encloser must be a single character");
        }

        $this->enclosure = $enclosure;
        $this->applyCsvControl();
    }

    /**
     * Set the escape character for values. This method is a mere passthrough for SplFileObject's
     * setCsvControl() method
     *
     * NOTE: PHP 8.4 deprecates explicitly supplying the escape parameter to the CSV functions;
     * on 8.4+ the requested escape character is recorded but not pushed to the file control.
     *
     * @param string $escape The field escape character (one character only)
     *
     * @throws InvalidArgumentException Thrown if the escape string is longer than one character
     *
     * @see https://www.php.net/manual/en/splfileobject.setcsvcontrol.php
     */
    public function setEscape(string $escape): void {

        if (strlen($escape) > 1) {
            throw new InvalidArgumentException("Escape character must be a single character");
        }

        $this->escape = $escape;
        $this->applyCsvControl();
    }

    /**
     * Push the current delimiter/enclosure/escape onto the underlying file object.
     *
     * PHP 8.4 deprecates relying on the default escape argument and deprecates non-empty
     * escape values; on 8.4+ the new default (empty string) is applied to the file.
     */
    protected function applyCsvControl(): void {

        $escape = PHP_VERSION_ID >= 80400 ? '' : $this->escape;

        $this->file->setCsvControl($this->delimiter, $this->enclosure, $escape);
    }

    /**
     * Retrieve the underlying file object
     *
     * @return SplFileObject
     */
    public function file(): SplFileObject {
        return $this->file;
    }

    protected function getMapper(): CSVColMapper {

        if (!$this->mapper) {

            $curIdx = $this->file->key();

            $this->file->seek(0);

            $this->setMapper(
                new CSVColMapper($this->file->current() ?: [], $this->hasHeaders())
            );

            $this->file->seek($curIdx);
        }

        return $this->mapper;
    }

    /**
     * Cherry pick a specific row at the given zero-based row index. This method will set the internal pointer
     * to the specified index, and return it to the original value. If the index is higher than the number
     * of rows in the file, the last row is returned
     *
     * @param int $idx Row offset
     *
     * @return CSVRow
     */
    public function row(int $idx): CSVRow {

        $this->lastRow = $this->file->key();

        $this->file->seek($idx);

        $rval = new CSVRow(
            $this->getMapper(),
            $idx,
            $this->file->current() ?: []
        );

        $this->file->seek($this->lastRow);

        return $rval;
    }

    /**
     * Reset the internal file pointer
     */
    public function reset(): void {
        $this->file->rewind();
    }

    /**
     * Read the current row of a CSV file, move the internal pointer forward and return CSVRow object.
     * If the file is EOF this method returns FALSE in order to support usage in a while loop.
     *
     * <code>
     * $file = new SplFileObject('./file.csv');
     *
     * $csv = new CSVFile($file, TRUE);
     *
     * while (($row = $csv->read())) {
     *     echo $row->col(0)->value();
     * }
     * </code>
     *
     * @return CSVRow|false
     */
    public function read(): CSVRow|false {

        if ($this->file->eof()) {
            return FALSE;
        }

        if ($this->file->key() === 0 && $this->hasHeaders()) {
            $this->file->seek(1);
        }

        $rval = new CSVRow(
            $this->getMapper(),
            $this->file->key(),
            $this->file->current() ?: []
        );

        $this->lastRow = $this->file->key();

        if (!$this->file->eof()) {
            $this->file->next();
        }

        return $rval;
    }
}
