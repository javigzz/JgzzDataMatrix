<?php
namespace Jgzz\DataMatrix\Builder;

abstract class AbstractMatrixBuilder {
	
	
	abstract protected function doBuild();
	
	/**
	 * Llama al método doBuild que debe ser implementado y 
	 * realiza comprobaciones básicas antes de devolver
	 * los arrays
	 *
	 * @return array
	 */
	final public function build(){
		list($array_grid, $keys_x, $keys_y) = $this->doBuild();
		
		//TODO: comprobaciones sobre arrays obtenidos
		if(!is_array($keys_x)){
			throw new \Exception("keys_x debe ser array", 1);
		}
		if(!is_array($keys_y)){
			throw new \Exception("keys_y debe ser array", 1);
		}
		if(!is_array($array_grid)){
			throw new \Exception("array_grid debe ser array", 1);
		}
		
		return array($array_grid, $keys_x, $keys_y);

	} 
	
	/**
	 * Finds the two arrays of keys for rows and columns implied 
	 * by the $data bidimiensional and associative array.
	 */
	protected function getKeysByData($data)
	{
		$keys_x = array_keys($data);
		
		$keys_y = array();
		
		foreach($keys_x as $x_key)
		{
			foreach($data[$x_key] as $y_key => $value)
			{
				if(!in_array($y_key, $keys_y)){
					
					$keys_y[] = $y_key;
					
				}
				
			}
		}
		
		return array($keys_x, $keys_y);
	}
	
	/**
	 * 
	 */
	public function getArrayGrid(){
		if(!isset($this->array_grid)){
			throw new \Exception("Marix Builder no contiene ningún array_grid. Ejecutar build() previamente");
		}
		
		return $this->array_grid;
	}
	
	public function getKeysX(){
		return $this->keys_x;
	}
	
	public function getKeysY(){
		return $this->keys_y;
	}
	
}
