<?php
namespace Jgzz\DataMatrix\Render;

use Jgzz\DataMatrix\Matrix\Matrix;
use Jgzz\DataMatrix\Render\AbstractMatrixRenderer;

/**
 * Genera una tabla html cuyas celdas contienen campos input text
 * por cada celda de la matriz
 */
class HTMLInputsTableMatrixRenderer extends AbstractMatrixRenderer {
	
	
	public $titulos_x;

	public $titulos_y;
	
	public function render(Matrix $m){
		
		$k_x = $m->getKeysX();
		$k_y = $m->getKeysY();
		
		$k_x_titulos = isset($this->titulos_x) ? $this->titulos_x : $k_x;
		$k_y_titulos = isset($this->titulos_y) ? $this->titulos_y : $k_y;
		
		// cabecera de tabla
		$this->writeln('<tr><th></th><th>'.join('</th><th>', $k_y_titulos).'</th></tr>');
		
		foreach ($k_x as $k_x_pos => $key_x) {
			$this->write('<tr><th>'.$k_x_titulos[$k_x_pos].'</th>');
			
			foreach ($k_y as $key_y){
				$this -> write('<td>'.$this->renderInput($m, $key_x, $key_y).'</td>');
			}
			
			$this -> writeln('</tr>');
		}
		
		return $this->str;
	}
	
	protected function renderInput(Matrix $m, $key_x, $key_y){
		return '<input type="text" value="'.$m->getXY($key_x, $key_y).'" id="k##'.$key_x.'##'.$key_y.'" />';
	}
	
}