<?php
namespace Jgzz\DataMatrix\Matrix;

use Jgzz\DataMatrix\Builder\AbstractMatrixBuilder;

class Matrix {

	const DIM_X = 'x';

	const DIM_Y = 'y';
	
	/**
	 * Array bidimensional de datos indexados
	 *
	 * @var array $data
	 */
	private $data;
	
	/**
	 * Keys of X (rows)
	 */
	private $keys_x;
	
	/*
	 * Keys of Y (columns)
	 */
	private $keys_y;

	/**
	 * Labels for both axis
	 */
	private $labels;

	public function __construct()
	{
		$this->labels = array(
			self::DIM_X => array(),
			self::DIM_Y => array()
		);
	}
	
	final public function build(AbstractMatrixBuilder $matrixBuilder)
	{
		$matrixBuilder->build($this);
	}

	public function setData(array $data)
	{
		$this->data = $data;
	}
	
	public function getData(){
		return $this->data;
	}

	public function getKeysDim($dim)
	{
		$this->checkDimOrException($dim);

		return self::DIM_X == $dim ? $this->getKeysX() : $this->getKeysY();
	}
	
	public function setKeysX($keys)
	{
		$this->keys_x = $keys;
	}
	
	public function getKeysX(){
		return $this->keys_x;
	}
	
	public function setKeysY($keys)
	{
		$this->keys_y = $keys;
	}

	public function getKeysY()
	{
		return $this->keys_y;
	}


	/**
	 * Checks wheather value x,y exists in matrix
	 *
	 * @return bool
	 */
	public function issetXY($key_x, $key_y){
		
		$v = $this->getXY($key_x, $key_y);
		
		return $v !== null;
	}

	/**
	 * Gets value in matrix for position x,y or NULL if not exists.
	 * NULL if either row or collumn don't exists.
	 * 
	 * @return mixed
	 */
	public function getXY($key_x, $key_y, $default = null){
		if(array_key_exists($key_x, $this->data)){
			if(array_key_exists($key_y, $this->data[$key_x])){
				return $this->data[$key_x][$key_y];
			}
		}
		return isset($default) ? $default : null;
	}

	public function setXY($key_x, $key_y, $value)
	{
		$this->data[$key_x][$key_y] = $value;
	}

	public function delXY($x, $y)
	{
		unset($this->data[$x][$y]);
	}
	
	/**
	 * Returns a row as an assoc Array for a row key.
	 * For a missing row returns an empty array
	 * 
	 * @param  string $row_key
	 * @return array         
	 */
	public function getRow($row_key)
	{
		return array_key_exists($row_key, $this->data) ? $this->data[$row_key] : array();
	}

	/**
	 * Returns a column as an assoc Array. Row keys are kept.
	 * For a missing column returns an empty array
	 * 
	 * @param  string $column_key 	Column label/key
	 * @return array 				
	 */
	public function getColumn($column_key)
	{
		$res = array();
		foreach ($this -> data as $row_key => $row) {
			foreach ($row as $col_key => $col_value) {
				if($col_key === $column_key){
					$res[$row_key] = $col_value;
					break;
				}
			}
		}
		return $res;
	}

	/**
	 * Sets values for a row by its index
	 * 
	 * @param string $row_key
	 * @param array  $row
	 */
	public function setRow($row_key, array $row)
	{
		$this->data[$row_key] = $row;
	}

	/**
	 * Sets values for a column by its index
	 * 
	 * @param string $column_key
	 * @param array  $column     Associative array
	 */
	public function setColumn($column_key, array $column)
	{
		foreach ($column as $row_key => $value) {
			if (array_key_exists($row_key, $this->data)) {
				$this->data[$row_key][$column_key] = $value;
			} else {
				$this->data[$row_key] = array($column_key => $value);
			}
		}
	}

	/** 
	 * Transpose matrix as a nested associative array
	 *
	 * @return array
	 */
	public function getTransposedData()
	{
		$data_transp = array();

		$keys_x = $this->getKeysX();
		$keys_y = $this->getKeysY();
		
		foreach ($keys_x as $kx){

			if(!array_key_exists($kx, $this->data)){
				continue;
			}
						
			foreach ($keys_y as $ky){
				
				if(array_key_exists($ky, $this->data[$kx])){
					$data_transp[$ky][$kx] = $this->data[$kx][$ky];
				}
			}
		}

		return $data_transp;
	}
	
	/**
	 * Fills up the empty cells with the value $padvalue
	 * 
	 * @param  string $padvalue   Value applied to matched cells
	 * @return Matrix
	 */
	public function pad($padvalue = ''){
		
		$keys_x = $this->getKeysX();
		$keys_y = $this->getKeysY();
		
		foreach ($keys_x as $kx){

			$row = $this->getRow($kx);
			
			foreach ($keys_y as $ky){
				
				if(!array_key_exists($ky, $row) || NULL == $row[$ky]){
					$this->data[$kx][$ky] = $padvalue;
				}
			}
		}

		return $this;
	}

	public function setAxisLabels($axis, array $labels)
	{
		$this->labels[$axis] = $labels;
	}

	public function setLabel($axis, $key, $label)
	{
		$this->labels[$axis][$key] = $label;
	}

	public function getLabel($axis, $key)
	{
		if (array_key_exists($key, $this->labels[$axis])) {
			return $this->labels[$axis][$key];
		}

		return null;
	}

	public function getLabels($axis)
	{
		return $this->labels[$axis];
	}

	public function checkDimOrException($dimension)
	{
		if(!in_array($dimension, array(self::DIM_X, self::DIM_Y))){
			throw new \Exception("wrong dimension name: ".$dimension);
		}
	}


	/**
	 * Dato para la posición x,y
	 * dada en un array asociativo $key_arr de la forma 'x'=>key_x, 'y'=>...
	 */
	public function getByKeyArrAssoc($key_arr){

		trigger_error("Use getXY instead", E_USER_DEPRECATED);

		$arr_ord = array();
		 
		foreach(array('x','y') as $var_pos => $var){
			array_push($arr_ord, $key_arr[$var]); 
		}
		
		return $this->getByKeyArrOrdenado($arr_ord);
	}
	
	/**
	 * Devuelve dato alojado en la posición dada por los valores del array.
	 * Se asume que la primera posición => x, segunda => y
	 */
	public function getByKeyArrOrdenado($key_arr){

		trigger_error("Use getXY instead", E_USER_DEPRECATED);

		if (count($key_arr) != 2) {
			throw new \Exception("Two elements expected");
		}

		if(array_key_exists($key_arr[0], $this->data) && array_key_exists($key_arr[1], $this->data[$key_arr[0]])){
			return $this->data[$key_arr[0]][$key_arr[1]];
		}
	}
	

	
}
