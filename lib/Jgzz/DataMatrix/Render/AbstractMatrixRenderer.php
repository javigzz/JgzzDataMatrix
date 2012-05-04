<?php
namespace Jgzz\DataMatrix\Render;

use Jgzz\DataMatrix\Matrix\Matrix;

abstract class AbstractMatrixRenderer implements MatrixRendererInterface {
	
	protected $str;
	
	//abstract public function render(Matrix $matrix);
	
	protected function writeln($str){
		$this->write($str."\r\n");
	}
	
	protected function write($str){
		$this->str .= $str;
	}
	
} 