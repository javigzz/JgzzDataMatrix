<?php
namespace Jgzz\DataMatrix\Tests;

use Jgzz\DataMatrix\Matrix\Matrix;
use Jgzz\DataMatrix\Matrix\NumericMatrix;
use Jgzz\DataMatrix\Builder\TrivialMatrixBuilder;

class TestCase extends \PHPUnit_Framework_TestCase
{
	/**
	 * matriz de datos de prueba con los nombres de fila a,b,c
	 * y columna x,y,z:
	 * 1 0 3
	 * 2 7 -1
	 * NULL 4 NULL
	 */	
	protected $data = array(
			"a" => array("x" => 1, "y" => 0, "z" => 3),
			"b" => array("x" => 2, "y" => 7, "z" => -1),
			"c" => array("y" => 4)
	);

	protected $data_sum_col_y = 11;

	protected $data_mask_rows = array("a","c");
	protected $data_mask_cols = array("x","z");

	protected $data_sum_col_y_with_mask_rows = 4;

	protected $data_totals_for_y_dim = array("x"=>3, "y"=>11, "z"=>2);
	protected $data_totals_for_x_dim = array("a"=>4, "b"=>8, "c"=>4);

	protected $data_masked_totals_for_y_dim = array("x"=>1, "y"=>4, "z"=>3);
	protected $data_masked_totals_for_x_dim = array("a"=>4, "b"=>1, "c"=>0);


	protected $data_transposed = array(
			"x" => array("a" => 1, "b" => 2),
			"y" => array("a" => 0, "b" => 7, "c" => 4),
			"z" => array("a" => 3, "b" => -1)
	);

	protected $data_x_column = array("a"=>1, "b"=>2);

	protected $missingColumnName = "w";

	protected $missingRowName = "d";

	protected function buildFixtureMatrix()
	{
		return $this->buildMatrix($this->data);
	}

	protected function buildFixtureNumericMatrix()
	{
		$mb = new TrivialMatrixBuilder($this->data);
		
		$matrix = new NumericMatrix;
		
		$matrix -> build($mb);
		
		return $matrix;		
	}

	protected function buildMatrix($data)
	{
		$mb = new TrivialMatrixBuilder($data);
		
		$matrix = new Matrix;
		
		$matrix -> build($mb);
		
		return $matrix;		
	}


}