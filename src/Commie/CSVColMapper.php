<?php
/**
 * Commie: CSV Traversal Library
 * @license BSD-3-Clause
 * @author Justin Rovang <generate@itnobody.com>
 */

namespace Commie;

/**
 * CSVColMapper objects provide lookup/traversal of columns by index or label for the rows of a CSV file.
 *
 * @package Commie
 */
class CSVColMapper {

    protected array $indexes = array();
    protected array $labels = array();

    /**
     * @var bool $TRIM_ALL controls whether or not the value itself should be whitespace trimmed. Often in
     * various system integration scenarios a common nuisance that can arise is whitespace padding even with the
     * presence of delimiters. This is a headache-free toggle to control it across the board.
     *
     * Set this to TRUE to have all labels trimmed of their whitespace upon instantiation.
     */
    public static bool $TRIM_ALL = FALSE;

    /**
     * Instantiate a column mapper. A column mapper is responsible for providing lookup/traversal information for the
     * columns in a row.
     *
     * NOTE: If you have two columns under the same heading, e.g.: 'ColZed, ColZed, ColZed, ...'; they will be indexed
     * uniquely for label referencing as 'MyCol, MyCol2, Mycol3, ...'
     *
     * @param array $colHeaderData Data First row of data to allow parsing of column headings (if any) and indexes
     * @param bool  $hasHeader     TRUE if the file will have a HEADER row and should map them by label
     */
    public function __construct(array $colHeaderData, bool $hasHeader = FALSE) {

        $this->indexes = array_keys($colHeaderData); /* Should be numeric results */

        /* If set, we'll attempt to access the values in the first row and map to their idx */
        if ($hasHeader) {
            foreach ($colHeaderData as $key => $label) {
                $this->mapLabel((string) $label, (int) $key);
            }
        }
    }

    /**
     * Resolve a label or index to a column offset in the row. This is typically meant for
     * internal use, but it's main purpose is to determine the offset of a column by string.
     *
     * @param string|int $label Offset or string label to resolve to column offset
     *
     * @return string|int|null
     */
    public function resolve(string|int $label): string|int|null {

        if (self::$TRIM_ALL && is_string($label)) {
            $label = trim($label);
        }

        if (array_key_exists($label, $this->labels)) {
            return $this->labels[$label]; /* return the index */
        }

        if (in_array($label, $this->indexes)) {
            return $label;
        }

        return NULL;
    }

    /**
     * Uniquely index all labeled columns to column offsets
     *
     * @param string $label   String to map to offset
     * @param int    $mapping Offset to map
     */
    public function mapLabel(string $label, int $mapping): void {

        if (self::$TRIM_ALL) {
            $label = trim($label);
        }

        $i = 2; /* We want to start by appending '2' to the label */

        while (array_key_exists($label, $this->labels)) {
            $label = $label.$i;
            $i++;
        }

        $this->labels[$label] = $mapping;
    }

    /**
     * Factory for building col value objects
     *
     * @param mixed $val Value to fill column value object with
     *
     * @return CSVCol
     */
    public function factory(mixed &$val): CSVCol {
        return new CSVCol($val);
    }
}
