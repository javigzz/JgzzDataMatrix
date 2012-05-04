<?php
namespace Jgzz\DataMatrix\Form\Symfony;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\CallbackValidator;

/**
 * Genera formulario con los elementos de la matriz que se pasa en el 
 * constructor
 */
class MatrixFormType extends AbstractType
{
	
	private $matrix;
	
	
	public function __construct(\Jgzz\DataMatrix\Matrix\Matrix $matrix){
		
		$this->matrix = $matrix;
		
	}
	
    public function buildForm(FormBuilder $builder, array $options)
    {
    	
		$k_x = $this->matrix->getKeysDim('x');
		
		$k_y = $this->matrix->getKeysDim('y');
		
		foreach($k_x as $key_x){
			
			foreach($k_y as $key_y){
				
				$nombre_campo = 'k##'.$key_x.'##'.$key_y;
				
				//value="'.$m->getXY($key_x, $key_y).'" id="k##'.$key_x.'##'.$key_y.'"
				$builder->add($nombre_campo, null, array(
				'data'=>$this->matrix->getXY($key_x, $key_y),
				'required' => false,
				)
				);
				
				// validación: número entero
				// $builder->addValidator(new CallbackValidator(function($form) {
		            // if (!is_int($form->get($nombre_campo)->getData())) {
		                // $form->get($nombre_campo)->addError(new FormError('Se esperaba un número entero'));
		            // }
		        // }));
				
			}
			
		}
    }

    public function getName()
    {
        return 'matrix';
    }
}