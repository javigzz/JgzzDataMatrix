<?php
namespace Jgzz\DataMatrix\Render;

use Jgzz\DataMatrix\Matrix\Matrix;
use Jgzz\DataMatrix\Render\AbstractMatrixRenderer;

/**
 * Genera una tabla html para la matriz
 */
class HTMLTableMatrixRenderer extends AbstractMatrixRenderer {
	
	
	public $titulos_x;

	public $titulos_y;
	
	public function render(Matrix $m){
		
		$k_x = $m->getKeysX();
		$k_y = $m->getKeysY();
		
		$k_x_titulos = isset($this->titulos_x) ? $this->titulos_x : $k_x;
		$k_y_titulos = isset($this->titulos_y) ? $this->titulos_y : $k_y;
		
		// cabecera de tabla
		$this->write('<tr><th></th>');
		
		foreach($k_y as $key_y){
			
			$titulo_y = isset($this->titulos_y) ? $this->titulos_y[$key_y] : $key_y;
			
			$this->write("<th>$titulo_y</th>");
		}
		
		$this->writeln("</tr>");
		
		
		foreach ($k_x as $k_x_pos => $key_x) {
			
			$titulo_x = isset($this->titulos_x) ? $this->titulos_x[$key_x] : $key_x;
			
			$this->write('<tr><th>'.$titulo_x.'</th>');
			
			foreach ($k_y as $key_y){
				
				$this -> write('<td>'.$m->getXY($key_x, $key_y).'</td>');
			}
			
			$this -> writeln('</tr>');
		}
		
		return $this->str;
	}
	
	
}