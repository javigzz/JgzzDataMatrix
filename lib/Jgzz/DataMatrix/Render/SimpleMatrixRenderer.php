<?php
namespace Jgzz\DataMatrix\Render;

use Jgzz\DataMatrix\Matrix\Matrix;
use Jgzz\DataMatrix\Render\AbstractMatrixRenderer;

class SimpleMatrixRenderer extends AbstractMatrixRenderer {
	
	
	public function render(Matrix $m){
		
		$k_x = $m->getKeysX();
		$k_y = $m->getKeysY();
		
		$this->writeln(join(' | ', $k_y));
		
		
		foreach ($k_x as $key_x) {
			$this->write($key_x.': ');
			
			foreach ($k_y as $key_y){
				$this -> write($m->getXY($key_x, $key_y).' | ');
			}
			
			$this -> writeln('');
		}
		
		return $this->str;
		
	}
	
}
