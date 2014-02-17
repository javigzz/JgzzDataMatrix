<?php
namespace Jgzz\DataMatrix\Matrix;

/**
 * Extension for numeric data Matrix.
 * This class provides hability to make some aritmetic operations
 */
class NumericMatrix extends Matrix {
		
	/**
	 * Nested 2 dimension array that store keys that will be used to filter
	 * summary operations on the Matrix. Inclussion masks.
	 *
	 * e.g: array('x' => array("a","d","h"), 'y' => array("w","y"));
	 */
	private $masks;

	/**
	 * Sets a inclusion mask for a given dimmesion
	 * 
	 * @param string $dimension  
	 * @param mixed $mask 
	 */
	public function setMask($dimension, $mask){

		$this->checkDimOrException($dim);

		if(!is_array($mask)){
			$mask = array($mask);
		}
		
		$this->masks[$dimension] = $mask;
	}

	public function setMascara($dimension, $mask){

		trigger_error("Use setMask", E_USER_DEPRECATED);
		$this->setMask($dimension, $mask);
	}
	
	/**
	 * Mask for a dimension if exists
	 * 
	 * @param  string $dimension
	 * @return mixed
	 */
	public function getMask($dimension){

		if(array_key_exists($dimension, $this->masks)){
			return $this->masks[$dimension];
		}
	}

	public function getMascara($dimension){

		trigger_error("Use getMask", E_USER_DEPRECATED);
		$this->getMask($dimension);
	}
	
	/**
	 * Calculates total sums agregated by a dimension. Preserves keys
	 * 
	 * @param  string $dim          
	 * @param  array $masks_addhoc	Inclusion mask for the agregation
	 * @return array
	 */
	public function totales($dim, $mask = null){

		$this->checkDimOrException($dim);
		
		$totals = array();
		
		$keys = $this->getKeysDim($dim);

		$that = $this;

		$totals = array_map(function($key) use ($dim, $mask, $that){
			return $that->sum($dim, $key, $mask);
		}, array_combine($keys, $keys));

		return $totals;
		
	}
	
	/**
	 * Sums Matrix entries for a particular row/column. 
	 * According to passed mask or configured object dimension mask
	 * 
	 * @param  string $dim          
	 * @param  mixed $key 
	 * @param  array $masks_addhoc
	 * @return float
	 */
	public function sum($dim, $key, $mask = null)
	{
		$this->checkDimOrException($dim);

		$mask = is_array($mask) ? $mask 
			: (isset($this->masks) ? $this->masks[$dim] : null);

		$summable = $dim == Matrix::DIM_X ? $this->getRow($key) : $this->getColumn($key);

		if ($mask) {
			$summable = array_intersect_key($summable, array_flip($mask));
		}

		$sum = array_sum($summable);

		return $sum;
	}
	
}
