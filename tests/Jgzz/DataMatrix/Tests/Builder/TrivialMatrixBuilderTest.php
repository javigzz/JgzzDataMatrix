<?php
namespace Jgzz\DataMatrix\Tests;

use Jgzz\DataMatrix\Matrix\Matrix;
use Jgzz\DataMatrix\Builder\TrivialMatrixBuilder;

/**
 * Tests on basic functions of the Matrix Class. Uses the TrivialMatrixBuilder
 * to generate a Matrix with known data inside.
 */
class TrivialMatrixBuilderTest extends \PHPUnit_Framework_TestCase
{
	
	/**
	 * matriz de datos de prueba con los nombres de fila a,b,c
	 * y columna x,y,z:
	 * 1 0 3
	 * 2 7 -1
	 * NULL 4 NULL
	 */	
	private $data = array(
			"a" => array("x" => 1, "y" => 0, "z" => 3),
			"b" => array("x" => 2, "y" => 7, "z" => -1),
			"c" => array("y" => 4)
	);

	private $data_x_column = array("a"=>1, "b"=>2);

	private $missingColumnName = "w";

	private $missingRowName = "d";

	public function testLabels()
	{
		$matrix = $this->buildFixtureMatrix();

		// comprobación de recuperación de las claves de ambas dimiensiones
		$this->assertEquals(array("a", "b", "c"), $matrix->getKeysX(), "X keys not match");
		$this->assertEquals(array("x", "y", "z"), $matrix->getKeysY(), "Y keys not match");
		
		return $matrix;			
	}
	
	/**
	 * @depends testLabels
	 *
	 * todo: separate
	 */
	public function testMissingCellsGetsNull(Matrix $matrix)
	{
		// get no existent (row not exists) is null
		$this->assertNull($matrix->getXY($this->missingRowName, "x"));

		// get no existent (row do exists) is null
		// ...but do exist in other rows
		$this->assertNull($matrix->getXY("c", "x"));
		// ...column doesn't exists in any row
		$this->assertNull($matrix->getXY("c", $this->missingColumnName));
		
		// get no existent. nor row or column exist
		$this->assertNull($matrix->getXY($this->missingRowName, $this->missingColumnName));
	}

	/**
	 * @depends testLabels
	 *
	 * todo: separate
	 */
	public function testMissingCellsIsseT(Matrix $matrix)
	{
		// get no existent (row not exists) is null
		$this->assertFalse($matrix->issetXY($this->missingRowName, "x"));

		// get no existent (row do exists) is null
		// ...but do exist in other rows
		$this->assertFalse($matrix->issetXY("c", "x"));
		// ...column doesn't exists in any row
		$this->assertFalse($matrix->issetXY("c", $this->missingColumnName));
		
		// get no existent. nor row or column exist
		$this->assertFalse($matrix->issetXY($this->missingRowName, $this->missingColumnName));
	}


	/**
	 * @depends testLabels
	 *
	 * todo: separate
	 */
	public function testMissingCellsIssetFalse(Matrix $matrix)
	{
		// get no existent (row not exists) is null
		$this->assertFalse($matrix->issetXY($this->missingRowName, "x"));

		// get no existent (row do exists) is null
		// ...but do exist in other rows
		$this->assertFalse($matrix->issetXY("c", "x"));
		// ...column doesn't exists in any row
		$this->assertFalse($matrix->issetXY("c", $this->missingColumnName));
		
		// get no existent. nor row or column exist
		$this->assertFalse($matrix->issetXY($this->missingRowName, $this->missingColumnName));
	}

	public function testGetCellsAfterSet()
	{
		$matrix = $this->buildFixtureMatrix();

		$value = 500;

		// ok after setting a previusly missing cell
		$matrix->setXY($this->missingRowName,$this->missingColumnName,$value);
		$this->assertEquals($value, $matrix->getXY($this->missingRowName,$this->missingColumnName), "get right value after setting a missing cell");

		// ok after setting a previusly set cell
		$matrix->setXY("a","x",$value);
		$this->assertEquals($value, $matrix->getXY("a","x"), "get right value after setting an existent cell");
	}


	public function testSetRow()
	{
		// todo
	}

	public function testSetColumn()
	{
		// todo
	}


	/**
	 * @depends testLabels
	 */
	public function testGetColumn(Matrix $matrix)
	{
		$this->assertEquals(array(), $matrix->getColumn($this->missingColumnName), "Missing column returns empty array");

		$this->assertEquals($this->data_x_column, $matrix->getColumn("x"), "Matrix column is retrieved ok by name");
	}

	/**
	 * @depends testLabels
	 */
	public function testGetRow(Matrix $matrix)
	{
		$this->assertEquals(array(), $matrix->getRow($this->missingRowName), "Missing row returns empty array");

		$this->assertEquals($this->data["a"], $matrix->getRow("a"), "Matrix row is retrieved ok by name");
	}

	private function buildFixtureMatrix()
	{
		$mb = new TrivialMatrixBuilder($this->data);
		
		$matrix = new Matrix;
		
		$matrix -> build($mb);
		
		return $matrix;		
	}


	// todo
	// getTransposedData
	// pad
	// label functions

}
