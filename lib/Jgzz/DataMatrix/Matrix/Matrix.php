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
	
	
	private $mascara_x;
	
	private $mascara_y;
	
	// 2dim
	final public function build(AbstractMatrixBuilder $matrixBuilder){
		list($this->data, $this->keys_x, $this->keys_y) = $matrixBuilder->build();
	}
	
	
	public function issetXY($key_x, $key_y){
		$v = $this->getXY($key_x, $key_y);
		
		return $v !== null;
	}
	
	/**
	 * Comprueba si hay un valor para la posición x,y.
	 * En caso de que no exista la fila o columna, devuelve null.
	 */
	public function getXY($key_x, $key_y, $default = null){
		if(array_key_exists($key_x, $this->data)){
			//var_dump($this->keys_x[$key_x]);
			if(array_key_exists($key_y, $this->data[$key_x])){
				return $this->data[$key_x][$key_y];
			}
		}
		return isset($default) ? $default : null;
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
				return $this->data[$key_arr[0]][$key_arr[1]];
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
	
	public function getKeysDim($dim){
		$n = 'keys_'.$dim;
		return $this->$n;
	}
	
	public function getKeysX(){
		return $this->keys_x;
	}
	
	public function getKeysY(){
		return $this->keys_y;
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
	
}
