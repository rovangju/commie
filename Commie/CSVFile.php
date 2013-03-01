<?php

namespace Commie;

use \Exception;
use \InvalidArgumentException;
use \RuntimeException;

use \SplFileObject;


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
	
	public function setMapper(CSVColMapper $mapper) {
		$this->mapper = $mapper;
	}
	
	public function hasHeaders() {
		return $this->headersPresent;
	}
	
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

	public function setEscape($escape) {
		
		if (!($this->file instanceof SplFileObject)) {
			throw new Exception('Enclosure character must be set after initializing an instance');
		}
	
		if (strlen($escape) > 1) {
			throw new InvalidArgumentException("Escape character must be a single character");
		}
	
		$this->file->setCsvControl($this->delimiter, $this->enclosure, $escape);
		$this->escape = $escape;
	}
	
	/**
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
	 * 
	 * @param unknown $idx
	 * @throws InvalidArgumentException
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
	
	public function reset() {
		$this->file()->rewind();
	}
	
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