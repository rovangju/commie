<?php

namespace Commie;

use \UnexpectedValueException;

/**
 * CSVCol objects represent a fixed point in a CSV file based on row, and position.
 * This is a mere get/set class that serves as an interjection point for extension
 * for any customizations
 *
 * @package Commie
 *
 */

class CSVCol {

     /**
      * @var mixed Internal storage of the column value
      */
     protected $value;

     /**
      * @var boolean $TRIM_ALL controls whether or not the value itself should be whitespace trimmed. Often in
      * various system integration scenarios a common nuisance that can arise is whitespace padding even with the
      * presence of delimiters. This is a headache-free toggle to control it across the board.
      *
      * Set this to TRUE to have all values trimmed of their whitespace upon instantiation.
      */
     static public $TRIM_ALL = FALSE;

     /**
      * Create a column object that references a value in a row
      *
      * @param mixed $val Value of column to set
      *
      * @return NULL
      */
     public function __construct(&$val) {

         $this->value =& $val;

         if (self::$TRIM_ALL) {
             $this->value = trim($val);
         }

         return;
     }

     /**
      * Get the value of the column
      *
      * @return mixed Value - all cases this should be a string, integer or NULL
      */
     public function value() {
         return $this->value;
     }

     /**
      * Manually set the value for the column
      *
      * @param scalar $val A scalar value to set the value to
      *
      * @return NULL
      */
     public function set($val) {
          
         if (!is_scalar($val)) {
             throw new UnexpectedValueException("The value provided was non-scalar");
         }
         $this->value = $val;
         return;
     }
 }