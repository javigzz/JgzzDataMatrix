<?php
namespace Jgzz\DataMatrix\Matrix;

/**
 * Alias for NumericMatrix
 */
class PesosMatrix extends NumericMatrix {
	
	public function __construct()
	{
		parent::__construct();
		
		trigger_error("Use NumericMatrix", E_USER_DEPRECATED);
	}
}