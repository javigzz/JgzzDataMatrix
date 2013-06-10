<?php
namespace Jgzz\DataMatrix;

use Doctrine\ORM\EntityManager;
use Jgzz\DataMatrix\Matrix\Matrix;
use Jgzz\DataMatrix\Matrix\PesosMatrix;
use Jgzz\DataMatrix\Builder\DoctrineMatrixBuilder;
use Jgzz\DataMatrix\Builder\TrivialMatrixBuilder;



/**
* Operates upon one or more matrix
*/
class Manager
{

	private $remove_link_on_value_empty_or_zero = true;

	
	/**
	 * Performs update of matrix $m with the values added or removed from $m_updates.
	 * It travels the x-y pairs defined by original matrix $m disregarding that $m_updates might include
	 * more x-y pairs
	 * 
	 * 
	 * @param  EntityManager         $em        [description]
	 * @param  DoctrineMatrixBuilder $m_builder Needed sice it holds the matrix mapping info
	 * @param  Matrix                $m         [description]
	 * @param  Matrix                $m_updates [description]
	 * @param  [type]                $settings  [description]
	 * @return [type]                           [description]
	 */
	public function persistUpdates(EntityManager $em, DoctrineMatrixBuilder $m_builder, Matrix $m, Matrix $m_updates)
	{

		$k_x = $m -> getKeysDim('x');

		$k_y = $m -> getKeysDim('y');

		$values_entities = array();

		$entities_x = $m_builder->fetchAxisEntriesByKeys('x', $k_x);

		$entities_y = $m_builder->fetchAxisEntriesByKeys('y', $k_y);

		$link_class = $m_builder->getLinkClassMetadata()->getName();

		$link_repository = $em->getRepository($link_class);

		$setter_x = $m_builder->setterMethodForAssociation('x');

		$getter_x = $m_builder->getterMethodForAssociation('x');

		$setter_y = $m_builder->setterMethodForAssociation('y');

		$getter_y = $m_builder->getterMethodForAssociation('y');

		foreach($k_x as $key_x){

			foreach($k_y as $key_y){

				$current_persisted_value = $m->getXY($key_x, $key_y);

				if($current_persisted_value == $m_updates->getXY($key_x, $key_y)){
					// not modified, jump
					continue;

				}

				if(!isset($current_persisted_value) || $current_persisted_value === ''){
					
					// new connexion

					$link_instance = new $link_class;

					call_user_func_array(array($link_instance, $setter_x), array($this->findEntityByIdOrException($entities_x, $key_x, 'x')));

					call_user_func_array(array($link_instance, $setter_y), array($this->findEntityByIdOrException($entities_y, $key_y, 'y')));

				} else {

					// fetch link instance from db

					$link_instance = $m_builder->fetchLinkInstanceByAssociations($key_x, $key_y);

				}

				array_push($values_entities, $link_instance);
			}
		}

		// update link instaces with updates matrix data
		
		foreach($values_entities as $link){

			$up_key_x = $m_builder -> getLinkedEntityByAxis($link, 'x')->getId();

			$up_key_y = $m_builder -> getLinkedEntityByAxis($link, 'y')->getId();

			$updated_value = $m_updates->getXY($up_key_x, $up_key_y);

			// update original matrix value
			$m->setXY($up_key_x, $up_key_y, $updated_value);

			if((empty($updated_value) or $updated_value == 0) && $this->remove_link_on_value_empty_or_zero){

				$em->remove($link);

			} else {

				$m_builder->setLinkValue($link, $updated_value);

				$em->persist($link);
				
			}

		}

		$em->flush();

		return $values_entities;

	}

	protected function findEntityByIdOrException($entities, $id, $label = '')
	{
		// buscamos respuesta entre las recuperadas
		foreach($entities as $entity){
			if ($entity -> getId() == $id){
				$entity_found = $entity;
				break;
			}
		}

		if(!isset($entity_found)){
			throw new \Exception(sprintf("Not found entity for label '%s', with id: %s", $label, $id), 1);
		}

		return $entity_found;
	}


	/**
	 * Multiplica dos matrices utilizando sus índices asociativos
	 * 
	 * TODO: result matrix with labels
	 * 
	 * @param  Matrix $m1 [description]
	 * @param  Matrix $m2 [description]
	 * @return [type]     [description]
	 */
	public function multiply(Matrix $m1, Matrix $m2)
	{
		
		$d1 = $m1 -> getData();

		$d2_transp = $m2 -> getTransposedData();

		$data_res = array();

		foreach ($d1 as $d1_row_key => $d1_row) {

			foreach ($d2_transp as $d2_col_key => $d2_col) {
					
				$data_res[$d1_row_key][$d2_col_key] = $this -> assocEscalarProd($d1_row, $d2_col);

			}
			
		}

		$m = new Matrix;
		$m -> build(new TrivialMatrixBuilder($data_res));

		return $m;

	}

	/**
	 * TODO: result matrix with labels
	 * 
	 * @param  Matrix $m1 [description]
	 * @param  Matrix $m2 [description]
	 * @return [type]     [description]
	 */
	public function sum(Matrix $m1, Matrix $m2)
	{
		$d1 = $m1 -> getData();

		$d2 = $m2 -> getData();

		$res = array();

		foreach ($d1 as $d1_row_key => $d1_row) {

			if(!array_key_exists($d1_row_key, $d2)){
				$d2[$d1_row_key] = $d1_row;
				continue;
			}

			foreach ($d1_row as $d1_col_key => $value) {
				$curr = array_key_exists($d1_col_key, $d2[$d1_row_key]) ? $d2[$d1_row_key][$d1_col_key] : 0;
				$d2[$d1_row_key][$d1_col_key] = $curr + $value;
			}

		}

		$m = new PesosMatrix;

		$m -> build(new TrivialMatrixBuilder($d2));

		return $m;
	}
	
	/**
	 * Producto escalar de dos arrays asociativos.
	 * Permite que los arrays no tengan las mismo índices de entrada
	 * 
	 * @param  [type] $v1 [description]
	 * @param  [type] $v2 [description]
	 * @return [type]     [description]
	 */
	public function assocEscalarProd($v1, $v2)
	{
		$res = 0;

		foreach ($v1 as $v1_key => $v1_val) {
			if(array_key_exists($v1_key, $v2)){
				$res += $v1_val * $v2[$v1_key];
			}
		}

		return $res;
	}

}

