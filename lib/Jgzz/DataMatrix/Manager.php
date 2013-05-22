<?php
namespace Jgzz\DataMatrix;

use Doctrine\ORM\EntityManager;
use Jgzz\DataMatrix\Matrix\Matrix;
use Jgzz\DataMatrix\Builder\DoctrineMatrixBuilder;



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

			$updated_value = $m_updates->getXY($m_builder -> getLinkedEntityByAxis($link, 'x')->getId(), $m_builder -> getLinkedEntityByAxis($link, 'y')->getId());

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

}

