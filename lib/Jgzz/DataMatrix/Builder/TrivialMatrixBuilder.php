<?php
namespace Jgzz\DataMatrix\Builder;

/**
 * Builder which takes data and keys and doesn't perform
 * any action on them. Usefull when already has a
 * bidimiensional array in the form of the Matrix->data array
 */
class TrivialMatrixBuilder extends AbstractMatrixBuilder {
	
	private $data;
	
	private $keys_x;
	
	private $keys_y;
	
	
	public function __construct($data){
			
		$this->data = $data;
		
		list($this->keys_x, $this->keys_y) = $this->getKeysByData($this->data);
		
	}
	
	protected function doBuild(){
		
		return array($this->data, $this->keys_x, $this->keys_y);
		
	}
	
}
	
