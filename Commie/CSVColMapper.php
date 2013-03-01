<?php

namespace Commie;

use \RuntimeException;

class CSVColMapper {
	
	protected $indexes = array();
	protected $labels = array();
	
	static public $TRIM_ALL = FALSE;
	
	
	public function __construct(array $colHeaderData, $hasHeader = FALSE) {

		$this->indexes = array_keys($colHeaderData); /* Should be numeric results */
		
		/* If set, we'll attempt to access the values in the first row and map to their idx */
		if ($hasHeader == TRUE) { 
			
			foreach ($colHeaderData as $key => $label) {
				$this->mapLabel($label, $key);
			}
		}
	}
	
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
	
	public function mapLabel($label, $mapping) {

		if (self::$TRIM_ALL) {
			$label = trim($label);
			$mapping = trim($mapping);
		}
	
		$i = 2; /* We want to start by appending '2' to the label */
		
		while (array_key_exists($label, $this->labels) == TRUE) {
			$label = $label.$i;
		}
		
		$this->labels[$label] = $mapping;
	}
	
	
	/*
	public function read() {
		
	}
	
	public function write() {
		
	}
	
	public function len() {
		
	}
	*/
	
}
?>