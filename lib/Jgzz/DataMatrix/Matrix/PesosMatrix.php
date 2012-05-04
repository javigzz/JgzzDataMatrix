<?php
namespace Jgzz\DataMatrix\Matrix;

/**
 * Extensión para matrices de datos numéricos sobre los que se puedan realizar operaciones
 * mediante los métodos que proporciona esta clase.
 */
class PesosMatrix extends Matrix {
		
	/**
	 * Array de arrays de claves que aplican en las operaciones sobre las diferentes dimensiones
	 * Util para realizar operaciones filtradas a determinados valores de una dimensión.
	 * mascaras => ['x' => Array(), 'y' => Array(), ... ]
	 */
	private $mascaras;
		
	/**
	 * Establece una máscara para una variable.
	 */
	public function setMascara($var, $mascara){
		
		if(!in_array($var, $this->dimensiones)){
			throw new \Exception("Nombre de variable no válido: $var", 1);
		}
		
		if(!is_array($mascara)){
			$mascara = array($mascara);
		}
		
		$this->mascaras[$var] = $mascara;
	}
	
	public function getMascara($var){
		if(array_key_exists($var, $this->mascaras)){
			return $this->mascaras[$var];
		}
		return null;// ojo, no se devuelve un array vacío ya que eso implicaría que ningún elemento se seleccionaraía
	}
	
	/**
	 * Recorre la matriz a lo largo de una dimensión, calculando los totales de la suma para cada 
	 * categoría en esa dim.
	 */
	public function totales($dim, $mascaras_addhoc = null){
		
		$array_totales = array();
		
		$dim_keys = $this->getKeysDim($dim);
		
		foreach ($dim_keys as $dim_key){
			$array_totales[$dim_key] = $this->sum($dim, $dim_key, $mascaras_addhoc);
		}
		
		return $array_totales;
		
	}
	
	/**
	 * Suma todas las entradas de la matriz para un valor de una dimesión dada.
	 * TODO: Se pueden pasar máscaras para aplicara a la operación
	 * 
	 * TODO: generalizar a n dimensiones: hipercubo? -> buscar librería
	 */
	public function sum($dim, $dim_fija_key, $mascaras_addhoc = null)
	{
		// TODO: utilizar iterator o callback o array walk ... para separar iteración de operación
		
		// XXX: en más dimensiones, comproboar máscaras en el resto de dimensiones
		
		// máscara pasada como argumento a máscara en objeto 
		$mascaras = is_array($mascaras_addhoc) ? $mascaras_addhoc : 
			(isset($this->mascaras) ? $this->mascaras : null);
		

		$data = $this->getData();
		
		$sum = 0;
		
		
		// recorrido de las dimensiones
		// TODO: solo funcionará con 2 dim, para ampliar debe ser proceso recursivo
		foreach ($this->dimensiones as $walk_dim){
						
			// si dimensión fija, saltamos
			if($walk_dim == $dim){
				continue;
			}
			
			// máscara aplicable a la dimensión que estamos recorriendo
			// si no hay una máscara asignada a la dimensión, se toman todas las claves de la dimensión
			$mascara_dim = is_array($mascaras) && array_key_exists($walk_dim, $mascaras) ? $mascaras[$walk_dim] : $this->getKeysDim($walk_dim);
			
			foreach ($mascara_dim as $walk_dim_key){
				if(empty($walk_dim_key)){
					continue;
				}
				// XXX: solo 2 dim
				$sum += $this->getByKeyArrAssoc(array($dim => $dim_fija_key, $walk_dim => $walk_dim_key));
			}
				
		}
		
		return $sum;
	}
	
}
