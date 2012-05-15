<?php
namespace Jgzz\DataMatrix\Tests;

use Jgzz\DataMatrix\Matrix\Matrix;
use Jgzz\DataMatrix\Builder\TrivialMatrixBuilder;

/**
 * Tests on basic functions of the Matrix Class. Uses the TrivialMatrixBuilder
 * to generate a Matrix with known data inside.
 */
class MatrixTest extends \PHPUnit_Framework_TestCase
{
	
	/**
	 * 
	 */
	public function testUno()
	{
		/*
		 * matriz de datos de prueba con los nombres de fila a,b,c
		 * y columna x,y,z:
		 * 1 0 3
		 * 2 7 -1
		 * NULL 4 NULL
		 */	
		$data = array(
			"a" => array("x" => 1, "y" => 0, "z" => 3),
			"b" => array("x" => 2, "y" => 7, "z" => -1),
			"c" => array("y" => 4)
		);
		
		//$keys_y = array("x", "y", "z");
		//$keys_x = array("a", "b", "c");
		
		$mb = new TrivialMatrixBuilder();
		
		$m = new Matrix;
		
		$m -> build($mb);
		
		$this->assertEquals(array("x", "y", "z"), $m->getKeysX(), "X keys not match");
		$this->assertEquals(array("a", "b", "c"), $m->getKeysY(), "Y keys not match");
		
	}
	
	public function provider()
	{
		return array(
			array(
			
			
			),
		);
	}
}
