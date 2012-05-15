<?php
namespace Jgzz\DataMatrix\Builder;

class Assoc1DimMatrixBuilder extends AbstractMatrixBuilder {
	
	
	private $patron;
	
	private $assoc_keys_values;
	
	
	public function __construct($patron, $assoc_keys_values){
		$this->patron = $patron;
		
		$this->assoc_keys_values = $assoc_keys_values;
		
	}
	
	protected function doBuild(){
		
		
		$keys_x = array();
		
		$keys_y = array();
		
		$valores = array();
		

		foreach ($this->assoc_keys_values as $fus_keys => $valor){
			
			if(!preg_match($this->patron, $fus_keys, $match)){
				//throw new \Exception("El patrón no es válido")
				continue;
			}
			//var_dump($match);exit;
			$key_x = $match[1];
			$key_y = $match[2];
			
			if(!in_array($key_x, $keys_x)){
				array_push($keys_x, $key_x);
			}
			
			if(!in_array($key_y, $keys_y)){
				array_push($keys_y, $key_y);
			}
			
			$valores[$key_x][$key_y] = $valor;
			
			
		}

		return array($valores, $keys_x, $keys_y);
	}
	
}
	
