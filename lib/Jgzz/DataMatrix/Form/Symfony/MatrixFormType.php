<?php
namespace Jgzz\DataMatrix\Form\Symfony;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
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
	
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    	
		$k_x = $this->matrix->getKeysDim('x');
		
		$k_y = $this->matrix->getKeysDim('y');
		
		foreach($k_x as $key_x){
			
			foreach($k_y as $key_y){
				
				$nombre_campo = 'k__'.$key_x.'__'.$key_y;
				// TODO: CENTRALIZAR ESTE FORMATO
				
				$builder->add($nombre_campo, null, array(
				'data'=>$this->matrix->getXY($key_x, $key_y),
				'required' => false,
				)
				);
				
			}
			
		}
    }

    public function getName()
    {
        return 'matrix';
    }
}