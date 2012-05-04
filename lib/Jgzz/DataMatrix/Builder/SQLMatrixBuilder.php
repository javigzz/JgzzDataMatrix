<?php
namespace Jgzz\DataMatrix\Builder;

class SQLMatrixBuilder extends AbstractMatrixBuilder {
	
	/**
	 * Tabla de valores
	 */
	protected $tabla;
	
	/**
	 * 
	 */
	protected $campo_x;
	
	/**
	 * 
	 */
	protected $campo_y;
	
	protected $campo_valor;
	
	protected $options = array();

	
	protected function doBuild(){
		$registros = $this->query();
		
		$keys_x = array();
		
		$keys_y = array();
		
		$valores = array();
		
		// 
		foreach($registros as $reg){
			$key_x = $reg[$this->campo_x];
			$key_y = $reg[$this->campo_y];
			// añadimos key x?
			if(!in_array($key_x, $keys_x)){
				array_push($keys_x, $key_x);
			}
			// añadimos key y?
			if(!in_array($key_y, $keys_y)){
				array_push($keys_y, $key_y);
			}
			
			// TODO: arrays de posiciones para mejorar rendimiento?
			if(!isset($valores[$key_x][$key_y])){
				$valores[$key_x][$key_y] = $reg[$this->campo_valor];
			} else {
				throw new \Exception("Se he intentado sobreescribir un valor de la matriz. Solo se espera un valor por celda");
			}
		}
		
		// padding en caso de solicitar todas las categorías x o y
		if(array_key_exists('full_x', $this->options) && $this->options['full_x'] == true){
			$full_categorias_x = $this->get_full_var_cat('x');
			$keys_x = $full_categorias_x;	
		}
		
		if(array_key_exists('full_y', $this->options) && $this->options['full_y'] == true){
			$full_categorias_y = $this->get_full_var_cat('y');
			$keys_y = $full_categorias_y;
		}

		return array($valores, $keys_x, $keys_y);
	}
	
	
	public function configDB(\PDO $con, $tabla, $campo_x, $campo_y, $campo_valor){
		$this->con = $con;
		$this->tabla = $tabla;
		$this->campo_x = $campo_x;
		$this->campo_y = $campo_y;
		$this->campo_valor = $campo_valor;
	}
	
	public function setOptions($options){
		$this->options = $options;
	}
	
	
	
	/**
	 * @return array
	 */
	protected function query(){
		
		$statement = $this->con->prepare(sprintf("
		SELECT %s, %s, %s 
		FROM %s
		",$this->campo_x, $this->campo_y, $this->campo_valor, $this->tabla)
		);
		
		$statement->execute();
		
		return $statement->fetchAll(\PDO::FETCH_ASSOC);
	}
	

	protected function get_full_var_cat($var){
		
		if(!array_key_exists('tabla_'.$var, $this->options)){
			throw new \Exception("No se ha pasado opción tabla_$var", 1);
		}
		
		$tabla = $this->options['tabla_'.$var];
		
		$option_var_id = 'tabla_'.$var.'_id';
		
		$tabla_var_id = array_key_exists($option_var_id, $this->options) ? $this->options[$option_var_id] : 'id';
		
		$statement = $this->con->prepare(sprintf("
		SELECT %s 
		FROM %s
		",$tabla_var_id, $tabla)
		);
		
		$statement->execute();
		
		return $statement->fetchAll(\PDO::FETCH_COLUMN);
	}
	

	
}
