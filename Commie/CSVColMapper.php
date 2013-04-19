<?php

namespace Commie;

use \RuntimeException;

class CSVColMapper {

    protected $indexes = array();
    protected $labels = array();
    
    /**
     * @var boolean $TRIM_ALL controls whether or not the value itself should be whitespace trimmed. Often in
     * various system integration scenarios a common nuisance that can arise is whitespace padding even with the
     * presence of delimiters. This is a headache-free toggle to control it across the board.
     *
     * Set this to TRUE to have all labels trimmed of their whitespace upon instantiation.
     */
    static public $TRIM_ALL = FALSE;

    /**
     * Instantiate a column mapper. A column mapper is responsible for providing lookup/traversal information for the
     * columns in a row.
     * 
     * NOTE: If you have two colums under the same heading, e.g.: 'ColZed, ColZed, ColZed, ...'; they will be indexed 
     * uniquely for label referencing as 'MyCol, MyCol2, Mycol3, ...'
     * 
     * @param array  $colHeader Data First row of data to allow parsing of column headings (if any) and indexes
     * @param string $hasHeader TRUE if the file will have a HEADER row and should map them by label.
     * 
     * @return NULL
     */
    public function __construct(array $colHeaderData, $hasHeader = FALSE) {

        $this->indexes = array_keys($colHeaderData); /* Should be numeric results */

        /* If set, we'll attempt to access the values in the first row and map to their idx */
        if ($hasHeader == TRUE) {
            	
            foreach ($colHeaderData as $key => $label) {
                $this->mapLabel($label, $key);
            }
        }
    }

    /**
     * Resolve a label or index to a column offset in the row. This is typically meant for
     * internal use, but it's main purpose is to determine the offset of a column by string.
     * 
     * @param integer|string $label Offset or string label to resolve to column offset
     * 
     * @return mixed
     */
    public function resolve($label) {

        if (self::$TRIM_ALL) {
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
     * @param string  $label   String to map to offset
     * @param integer $mapping Offset to map
     * 
     * @return NULL
     */
    public function mapLabel($label, $mapping) {

        if (self::$TRIM_ALL) {
            $label = trim($label);
            $mapping = trim($mapping);
        }

        $i = 2; /* We want to start by appending '2' to the label */

        while (array_key_exists($label, $this->labels) == TRUE) {
            $label = $label.$i;
            $i++;
        }

        $this->labels[$label] = $mapping;
    }
    
    /**
     * Factory for building col value objects
     * 
     * @param string|integer $val Value to fill column value object with
     * 
     * @return \Commie\CSVCol
     */
    public function factory(&$val) {
        return new CSVCol($val);
    }
}
?>