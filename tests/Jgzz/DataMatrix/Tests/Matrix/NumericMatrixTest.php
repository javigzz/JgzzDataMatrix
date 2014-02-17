<?php
namespace Jgzz\DataMatrix\Test\Matrix;

use Jgzz\DataMatrix\Tests\TestCase;
use Jgzz\DataMatrix\Matrix\NumericMatrix;

class NumericMatrixTest extends TestCase {

	public function testSum()
	{
		$matrix = $this->buildFixtureNumericMatrix();

		$this->assertEquals($this->data_sum_col_y, $matrix->sum('y',"y"), "Total column sum ok");

		$this->assertEquals($this->data_sum_col_y_with_mask_rows, $matrix->sum('y',"y", $this->data_mask_rows), "Total masked column sum ok");

		return $matrix;
	}

	/**
	 * @depends testSum
	 */
	public function testTotals(NumericMatrix $matrix)
	{
		$this->assertEquals($this->data_totals_for_y_dim, $matrix->totales('y'), "Totals agregated for columns");

		$this->assertEquals($this->data_totals_for_x_dim, $matrix->totales('x'), "Totals agregated for rows");
	}

	/**
	 * @depends testSum
	 */
	public function testTotalsMasked(NumericMatrix $matrix)
	{
		$this->assertEquals($this->data_masked_totals_for_y_dim, $matrix->totales('y', $this->data_mask_rows), "Masked totals agregated for columns");

		$this->assertEquals($this->data_masked_totals_for_x_dim, $matrix->totales('x', $this->data_mask_cols), "Masked totals agregated for rows");

	}
}