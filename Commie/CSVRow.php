<?php

namespace Commie;


class CSVRow {
	
	public $rowData = array();
	protected $rowIdx;
	
	/**
	 * @var CSVColMapper
	 */
	public $mapper;
	
	protected $empty = NULL;
	
	public function __construct(CSVColMapper $mapper, $idx, array $rowData) {

		$this->rowIdx = $idx;
		$this->rowData = $rowData;
		
		$this->mapper = $mapper;
	}
	
	public function offset() {
		return $this->rowIdx;
	}
	
	public function isEmpty() {
		
		if (is_null($this->empty)) {
			$this->empty = !(bool)implode('', $this->rowData);
		}
		return $this->empty;
	}

	/**
	 * 
	 * @param unknown $id
	 * @throws \OutOfRangeException
	 * @return \Commie\CSVCol
	 */
	public function col($id) {
		
		/* error_log($id ." - ". json_encode($this->rowData) ." -- ". $this->mapper->resolve($id)); */
		
		if (!$this->isCol($id)) {
			throw new \OutOfRangeException("Column reference ".$id." could not be resolved.");
		}
		
		return new CSVCol(
			$this->rowData[$this->mapper->resolve($id)]
		);
	}
	
	public function isCol($reference) {
		
		if ($this->mapper->resolve($reference) !== NULL) {
			return TRUE;
		}
		return FALSE;		
	}
	
}

?>