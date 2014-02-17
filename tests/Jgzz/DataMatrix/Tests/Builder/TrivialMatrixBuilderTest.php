<?php
namespace Jgzz\DataMatrix\Tests;

use Jgzz\DataMatrix\Tests\TestCase;
use Jgzz\DataMatrix\Matrix\Matrix;
use Jgzz\DataMatrix\Builder\TrivialMatrixBuilder;

/**
 * Tests on basic functions of the Matrix Class. Uses the TrivialMatrixBuilder
 * to generate a Matrix with known data inside.
 */
class TrivialMatrixBuilderTest extends TestCase
{
	
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

	/**
	 * @depends testLabels
	 */
	public function testNullCellsEquivalentToMissing()
	{
		$matrix = $this->buildFixtureMatrix();

		$matrix->setXY("a","x", NULL);

		$this->assertFalse($matrix->issetXY("a", "x"), "A null cell is not 'set'");

		$this->assertFalse($matrix->issetXY("a", $this->missingColumnName), $matrix->issetXY("a", "x"), "NULL cell is equivalent to a missing cell");
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
		$matrix = $this->buildFixtureMatrix();

		$row1 = array("x" => 8, "y" => 2, "p" => 3);
		$row2 = array("x" => 8, "y" => 2, "p" => 3);

		// ok after setting an existen row
		$matrix->setRow("b", $row1);
		$this->assertEquals($row1, $matrix->getRow("b"), "set entire preexistent row");

		$matrix->setRow($this->missingRowName, $row2);
		$this->assertEquals($row2, $matrix->getRow($this->missingRowName), "set entire non existent row");
	}

	public function testSetColumn()
	{
		$matrix = $this->buildFixtureMatrix();

		$col1 = array("a" => 1, "b" => 7, "c" => 3);
		$col2 = array("a" => 6, "b" => 3, "c" => 3);

		// ok after setting an existen row
		$matrix->setColumn("x", $col1);
		$this->assertEquals($col1, $matrix->getColumn("x"), "set entire preexistent column");

		$matrix->setColumn($this->missingColumnName, $col2);
		$this->assertEquals($col2, $matrix->getColumn($this->missingColumnName), "set entire non existent column");
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

	/**
	 * @depends testLabels
	 */
	public function testTransposedData(Matrix $matrix)
	{
		$this->assertEquals($this->data_transposed, $matrix->getTransposedData(), "transpose data");
	}

	public function testPadding()
	{
		$data = array(
			"a" => array("x" => 1),
			"b" => array("x" => NULL, "y" => 2)
		);

		$expected_pad = array(
			"a" => array("x" => 1, "y" => ""),
			"b" => array("x" => "", "y" => 2)
		);

		$expected_pad2 = array(
			"a" => array("x" => 1, "y" => 0),
			"b" => array("x" => 0, "y" => 2)
		);

		$matrix = $this->buildMatrix($data);

		$this->assertEquals($expected_pad, $matrix->pad()->getData(), "Pad puts empty string by default");

		$this->assertEquals($expected_pad2, $matrix->pad(0)->getData(), "Pad puts ceros (or other data) on explicit request");
	}

	// TODO: label functions
}
