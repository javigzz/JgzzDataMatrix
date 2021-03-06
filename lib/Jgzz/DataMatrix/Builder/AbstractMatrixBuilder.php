<?php
namespace Jgzz\DataMatrix\Builder;

use Jgzz\DataMatrix\Matrix\Matrix;

abstract class AbstractMatrixBuilder {
	
	abstract protected function doBuild();
	
	/**
	 * Call doBuild method and makes some checks
	 *
	 * @return array
	 */
	final public function build(Matrix $matrix){

		$build_response = $this->doBuild();

		$array_grid = $build_response[0];

		$keys_x = $build_response[1];

		$keys_y = $build_response[2];

		if(array_key_exists(3, $build_response)){
			$labels_x = $build_response[3];
		}

		if(array_key_exists(4, $build_response)){
			$labels_y = $build_response[4];
		}
		
		if(!is_array($keys_x)){
			throw new \Exception("keys_x must be array");
		}
		
		if(!is_array($keys_y)){
			throw new \Exception("keys_y must be array");
		}
		
		$matrix->setData($array_grid);

		$matrix->setKeysX($keys_x);

		$matrix->setKeysY($keys_y);

		if(!empty($labels_x)){
			$matrix->setAxisLabels('x',$labels_x);
		}

		if(!empty($labels_y)){
			$matrix->setAxisLabels('y',$labels_y);
		}

		return $matrix;
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
	
	public function getArrayGrid(){
		if(!isset($this->array_grid)){
			throw new \Exception("No array_grid. Run build() before");
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
