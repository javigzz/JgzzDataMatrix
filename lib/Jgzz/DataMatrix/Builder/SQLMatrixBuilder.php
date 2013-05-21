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


	/**
	 * Opciones para la estrategia de asignación de valores a los puntos de cruce
	 */
	
	/**
	 * Se asigna el valor del campo indicado en $campo_valor
	 */
	const VALUE_STRATEGY_FIELD = 1;

	/**
	 * Se asigna 1 si existe la conexión. Se ignora $campo_valor
	 */
	const VALUE_STRATEGY_EXISTS = 2;

	protected $value_strategy;


	public function __construct()
	{
		$this -> value_strategy = self::VALUE_STRATEGY_FIELD;
	}

	public function setStrategy($s)
	{
		$this -> value_strategy = $s;
	}
	
	protected function doBuild(){
		$registros = $this->query();
		
		$keys_x = array();
		
		$keys_y = array();
		
		$valores = array();
		
		/*
		 * recopilación de claves x e y
		 * y de array bidimensional de valores para x,y
		 */
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
			
			/*
			 * 
			 * TODO: arrays de posiciones para mejorar rendimiento?
			 */
			if(!isset($valores[$key_x][$key_y])){

				// Asignación del valor correspondiente en base a la estrategia
				
				switch ($this -> value_strategy) {
					case self::VALUE_STRATEGY_FIELD:
						$valores[$key_x][$key_y] = $reg[$this->campo_valor];
						break;

					case self::VALUE_STRATEGY_EXISTS:
						$valores[$key_x][$key_y] = 1;
						break;
					
					default:
						throw new \Exception(sprintf("Value strategy not valid: %s", $this -> value_strategy), 1);
						break;
				}
				

			} else {

				// TODO: permitir sobreescritura ?...
				throw new \Exception(
					sprintf("Se ha intentado sobreescribir un valor de la matriz. Solo se espera un valor por celda. x: %s, y: %s, valor: %s. Anterior valor: %s",
					$key_x, $key_y, $reg[$this->campo_valor], $valores[$key_x][$key_y]));
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
	
	/**
	 * 
	 */
	public function setOptions($options){
		$this->options = $options;
	}
	
	
	
	/**
	 * @return array
	 */
	protected function query(){

		switch ($this -> value_strategy) {
			case self::VALUE_STRATEGY_FIELD:
				$sql = sprintf("
				SELECT %s, %s, %s 
				FROM %s
				",$this->campo_x, $this->campo_y, $this->campo_valor, $this->tabla);		
				break;
			
			default:
				$sql = sprintf("
				SELECT %s, %s 
				FROM %s
				",$this->campo_x, $this->campo_y, $this->tabla);		

				break;
		}
		
		$statement = $this->con->prepare($sql);

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
	
		// order by
		$order_by_str = '';
		$order_by_option = 'orderby_'.$var;
		if (array_key_exists($order_by_option, $this->options)){
			$orderby_dir = array_key_exists('orderby_dir_'.$var, $this->options) ? 
				$this->options['orderby_dir_'.$var] : 'DESC';
			
			if($tabla_var_id != $this->options[$order_by_option]){
				$str_order_default = ",".$tabla_var_id." ASC ";
			}
			
			
			$order_by_str = sprintf('ORDER BY %s %s '.$str_order_default, $this->options[$order_by_option], $orderby_dir); 
		}	
			
		$sql = sprintf("
		SELECT %s 
		FROM %s %s
		",$tabla_var_id, $tabla, $order_by_str);
		
		$statement = $this->con->prepare($sql);
		
		$statement->execute();
		
		return $statement->fetchAll(\PDO::FETCH_COLUMN);
	}
	

	
}
