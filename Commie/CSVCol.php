<?php

namespace Commie;

class CSVCol {
	
	protected $value;
	
	static public $TRIM_ALL = FALSE;
	
	public function __construct(&$val) {
		
		$this->value =& $val;
		
		if (self::$TRIM_ALL) {
			$this->value = trim($val);
		}
		
	}
	
	public function value() {
		return $this->value;
	}
	
	public function set($val) {
		$this->value = $val;
	}
}

?>