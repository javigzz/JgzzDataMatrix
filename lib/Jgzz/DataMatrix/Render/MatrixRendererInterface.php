<?php
namespace Jgzz\DataMatrix\Render;

use Jgzz\DataMatrix\Matrix\Matrix;

interface MatrixRendererInterface {
	
	public function render(Matrix $matrix);
	
} 