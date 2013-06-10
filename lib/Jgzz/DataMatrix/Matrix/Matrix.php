<?php
namespace Jgzz\DataMatrix\Matrix;

use Jgzz\DataMatrix\Builder\AbstractMatrixBuilder;

class Matrix {
	
	protected $dimensiones = array('x','y');
	
	private $var_pos = array('x'=>0, 'y'=>1, 'z'=>2);
	
	/**
	 * Array bidimensional de datos indexados
	 *
	 * @var array $data
	 */
	private $data;
	
	/**
	 * 
	 * Array de claves del eje x ordenadas
	 */
	private $keys_x;
	
	/*
	 * 
	 * Array de claves del eje y ordenadas
	 */
	private $keys_y;

	private $labels = array('x'=>array(), 'y'=>array());
	
	private $mascara_x;
	
	private $mascara_y;
	
	// 2dim
	final public function build(AbstractMatrixBuilder $matrixBuilder){
		// list($this->data, $this->keys_x, $this->keys_y) = $matrixBuilder->build();
		$matrixBuilder->build($this);
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
	 * Gets value in matrix for position x,y or null if not exists.
	 * Gets null if either row or collumn don't exists.
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
	
	/**
	 * Dato para la posición x,y,.. dada en un array asociativo $key_arr de la forma 'x'=>key_x, 'y'=>...
	 */
	public function getByKeyArrAssoc($key_arr){
		
		$arr_ord = array();
		
		foreach($this->dimensiones as $var_pos => $var){
			array_push($arr_ord, $key_arr[$var]); 
		}
		
		return $this->getByKeyArrOrdenado($arr_ord);
	}
	
	/**
	 * Devuelve dato alojado en la posición dada por los valores del array.
	 * Se asume que la primera posición => x, segunda => y,,
	 * Error en caso de índices no válidos
	 */
	public function getByKeyArrOrdenado($key_arr){
		// var_dump($key_arr);
		// var_dump($this->data);
		switch (count($key_arr)) {
			case 2:
				// dos dimensiones:
				if(array_key_exists($key_arr[0], $this->data) && array_key_exists($key_arr[1], $this->data[$key_arr[0]])){
					return $this->data[$key_arr[0]][$key_arr[1]];
				} else {
					return null;
				}
				break;
			case 3;
				// tres dimensiones:
				return $this->data[$key_arr[0]][$key_arr[1]][$key_arr[2]];
				break;
			break;
			
			default:
				throw new \Exception("Se esperaban dos / tres componentes en array de posición. Encontradas: "
				.count($key_arr)." Array: ".join(', ',$key_arr, 1));
				break;
		}
	}
	
	public function getData(){
		return $this->data;
	}

	public function setData($data)
	{
		$this -> data = $data;
	}
	
	public function getKeysDim($dim)
	{
		$n = 'keys_'.$dim;
		return $this->$n;
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
	 * Transposed data
	 */
	public function getTransposedData()
	{
		$data_transp = array();

		$keys_x = $this->getKeysDim('x');
		
		$keys_y = $this->getKeysDim('y');
		
		foreach ($keys_x as $kx){

			if(!array_key_exists($kx, $this->data)){
				continue;
				//$this->data[$kx] = array();
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
	 * Rellena la matriz inicializando las las celdas vacías con el valor $padvalue
	 *
	 * 2dim padd
	 */
	public function pad($padvalue = ''){
		// si padding
		
		$keys_x = $this->getKeysDim('x');
		
		$keys_y = $this->getKeysDim('y');
		
		foreach ($keys_x as $kx){
			
			if(!array_key_exists($kx, $this->data)){
					$this->data[$kx] = array();
			}
			
			foreach ($keys_y as $ky){
				
				if(!array_key_exists($ky, $this->data[$kx])){
					$this->data[$kx][$ky] = $padvalue;
				}
			}
		}
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

	
}
