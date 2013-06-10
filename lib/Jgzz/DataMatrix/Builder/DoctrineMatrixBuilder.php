<?php
namespace Jgzz\DataMatrix\Builder;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;

class DoctrineMatrixBuilder extends AbstractMatrixBuilder {
	
	protected $association_fieldname_x;

	protected $association_fieldname_y;

	protected $entity_link_name;

	/**
	 * @var Doctrine\ORM\Mapping\ClassMetadata
	 */
	protected $metadata_link;
	
	protected $value_property;

	/**
	 *
	 * - full_x: if set and TRUE all x values from db will be added to the matrix axis
	 * - full_y: if set and TRUE all y values from db will be added to the matrix axis
	 * - axis_x_querybuilder: if set, x axis will be overriden by this querybuilder result
	 * - axis_y_querybuilder: if set, y axis will be overriden by this querybuilder result
	 * - axis_hydration_mode: by default Query::HYDRATE_ARRAY
	 * - label_x_field
	 * - label_y_field
	 * 
	 * @var array
	 */
	protected $options = array();

	protected $em;

	protected $qb;

	protected $default_axis_hydration_mode;

	protected $getters = array();

	protected $setters = array();

	protected $setter_link_value;

	/**
	 * Query for retrieving link and associations x and y. Holds parameters x and y
	 * 
	 * @var Doctrine\ORM\Query
	 */
	protected $queryXY;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}

	/**
	 * Sets up the builder
	 * 
	 * @param  string $entity_link_name        Name of the link entity between related entities (eg: AcmeBundle:LinkEntity)
	 * @param  string $association_fieldname_x Property in the link entity that associates to x axis
	 * @param  [type] $association_fieldname_y Property in the link entity that associates to y axis
	 * @param  [type] $value_property          Property in the link entity that holds the value of the relation
	 * @param  array  $options                 See options documentation
	 * @return null
	 */
	public function settings($entity_link_name, $association_fieldname_x, $association_fieldname_y, $value_property, array $options = array())
	{
		$this->association_fieldname_x = $association_fieldname_x;
		$this->association_fieldname_y = $association_fieldname_y;
		$this->entity_link_name = $entity_link_name;// TODO: se puede eliminar este requisito buscando en los mappings
		$this->value_property = $value_property;
		$this->options = $options;

		$this->metadata_link = $this->em->getMetadataFactory()->getMetadataFor($this->entity_link_name);

		$this->default_axis_hydration_mode = Query::HYDRATE_ARRAY;
	}

	/**
	 * @return Doctrine\ORM\Mapping\ClassMetadata
	 */
	public function getLinkClassMetadata()
	{
		return $this->metadata_link;
	}

	/**
	 * Metadata object for the requested axis
	 * 
	 * @param  string $axis
	 * @return Doctrine\ORM\Mapping\ClassMetadata
	 */
	public function getAxisClassMetadata($axis)
	{
		$class = $this->metadata_link->getAssociationTargetClass($this->assocnameByAxis($axis));

		return $this->em->getMetadataFactory()->getMetadataFor($class);
	}

	
	protected function doBuild(){

		$qb = $this->getQueryBuilder();

		$results = $qb->getQuery()->getResult(Query::HYDRATE_ARRAY);
		
		$keys_x = array();
		
		$keys_y = array();

		$labels_x = array();

		$fetch_x_labels = array_key_exists('label_x_field', $this->options);

		$labels_y = array();
		
		$fetch_y_labels = array_key_exists('label_y_field', $this->options);

		$values = array();

		$x_id_key = $this->association_fieldname_x.'_id';

		$y_id_key = $this->association_fieldname_y.'_id';

		/*
		 * pick up x and y keys		
		 */
		foreach($results as $reg) {

			$key_x = $reg[$this->association_fieldname_x]['id'];

			$key_y = $reg[$this->association_fieldname_y]['id'];

			// adds x key to array
			if(!in_array($key_x, $keys_x)){
				array_push($keys_x, $key_x);
			}

			// adds y key to array
			if(!in_array($key_y, $keys_y)){
				array_push($keys_y, $key_y);
			}
			
			// more than one value for x y is not allowed
			if(isset($values[$key_x][$key_y])){

				throw new \Exception(
					sprintf("Attempt to assign two values to the same cell in the matrix. Only one value is supported. x key: %s, y key: %s, attempted value: %s. current value: %s",
					$key_x, $key_y, $reg[$this->value_property], $values[$key_x][$key_y]));

			}
			
			$values[$key_x][$key_y] = $reg[$this->value_property];

			// labels
			$labels_x[$key_x] = $fetch_x_labels ? $reg[$this->association_fieldname_x][$this->options['label_x_field']] : $key_x;

			$labels_y[$key_y] = $fetch_y_labels ? $reg[$this->association_fieldname_y][$this->options['label_y_field']] : $key_y;

		}
		
		// if all x or y are set to be filled with all the possible values:
		list($keys_x, $labels_x) = $this->amendAxisKeysAndLabelsByOptions($keys_x, 'x', $this->options);

		list($keys_y, $labels_y) = $this->amendAxisKeysAndLabelsByOptions($keys_y, 'y', $this->options);

		return array($values, $keys_x, $keys_y, $labels_x, $labels_y);
	}

	/**
	 * Modifies axis keys or not depending on options
	 */
	private function amendAxisKeysAndLabelsByOptions($keys, $axis, $options)
	{
		$option_name_full_axis = 'full_'.$axis;

		$option_name_axis_qb = 'axis_'.$axis.'_querybuilder';

		if(array_key_exists($option_name_full_axis, $options) && $options[$option_name_full_axis] == true){

			$entities =  $this->fetchFullAxisEntries($axis);

			$keys = $this->mapEntitiesIds($entities);

		} elseif(array_key_exists($option_name_axis_qb, $options)) {

			// $keys = $this->fetchAxisEntriesIdsByQb($options[$option_name_axis_qb]);

			$qb = $options[$option_name_axis_qb];

			$query = $qb->getQuery();

			$entities = $query->getResult($this->getAxisHydrationMode());

			$keys = $this->mapEntitiesIds($entities);
		}

		// set up labels depending on options

		$labels = array();

		$label_field_option = 'label_'.$axis.'_field';

		if(isset($entities) && array_key_exists($label_field_option, $options)){

			if(!(Query::HYDRATE_ARRAY == $this->getAxisHydrationMode())){
				$label_method = $this->to_camel_case('get_'.$label_field_option);
			}

			foreach ($entities as $entity) {

				if(Query::HYDRATE_ARRAY == $this->getAxisHydrationMode()){

					$labels[$entity['id']] = $entity[$options[$label_field_option]];

				} else {

					$labels[$entity->getId()] = call_user_func(array($entity, $label_method));

				}
			}

		}

		return array($keys, $labels);
	}

	private function mapEntitiesIds($entities)
	{
		return array_map(function($o){ return $o['id']; }, $entities);
	}
	
	public function getQueryBuilder()
	{
		if(!isset($this->qb)){
			$this->qb = $this->createQueryBuilder();
		}

		return $this->qb;
	}

	public function setQueryBuilder(QueryBuilder $qb)
	{
		$this->qb = $qb;
	}
		
	private function createQueryBuilder()
	{
		$qb = $this->em->createQueryBuilder();

		$qb -> add('select', 'v,x,y')
			-> add('from', $this->metadata_link->getName().' v')
			-> innerJoin('v.'.$this->association_fieldname_x, 'x')
			-> innerJoin('v.'.$this->association_fieldname_y, 'y')
			;

		$this->qb = $qb;

		return $this->qb;
	}

	private function getQueryXY()
	{
		if(!isset($this->queryXY)){
			$this->queryXY = $this->createQueryXY();
		} 
		
		return $this->queryXY;
	}

	private function createQueryXY()
	{
		$qb = $this->createQueryBuilder();

		$qb ->where($qb->expr()->andX(
	       $qb->expr()->eq('x.id', ':x_id'),
	       $qb->expr()->eq('y.id', ':y_id')))
		;

		$this->queryXY = $qb->getQuery();

		return $this->queryXY;
	}

	/**
	 * Fetches link entity instance from db corresponding to axis values x and y
	 * 
	 * @param  [type] $x [description]
	 * @param  [type] $y [description]
	 * @return [type]    [description]
	 */
	public function fetchLinkInstanceByAssociations($x,$y)
	{
		return $this->getQueryXY()
			->setParameters(array('x_id'=>$x, 'y_id'=> $y))
			->getSingleResult();
	}

	/**
	 * @param  [type] $keys [description]
	 * @return [type]       [description]
	 */
	public function fetchAxisEntriesByKeys($axis, $keys)
	{
		$class = $this->metadata_link->getAssociationTargetClass($this->assocnameByAxis($axis));

		return $this->em->getRepository($class)->findById($keys);
	}
	
	/**
	 * Fetches all entities in the requested axis
	 * 
	 * @param  strin $axis x or y
	 * @return [type]      entities collection
	 */
	public function fetchFullAxisEntities($axis)
	{
		$mapping = $this->metadata_link->getAssociationMapping($this->assocnameByAxis($axis));

		$class = $mapping['targetEntity'];

		$query = $this->em->createQuery(sprintf('SELECT o FROM %s o', $class));

		return $query->getResult($this->getAxisHydrationMode());
	}

	/**
	 * Fetches all the entries ids for the requested axis
	 * 
	 * @param  strin $axis x or y
	 * @return array       ids of entities
	 */
	// public function fetchFullAxisEntriesIds($axis)
	// {
	// 	$entities = $this->fetchFullAxisEntities($axis);

	// 	$keys = array_map(function($o){ return $o['id']; }, $entities);

	// 	return $keys;
	// }


	// public function fetchAxisEntriesIdsByQb(QueryBuilder $qb)
	// {
	// 	$query = $qb->getQuery();

	// 	$entities = $query->getResult($this->getAxisHydrationMode());

	// 	$keys = array_map(function($o){ return $o['id']; }, $entities);

	// 	return $keys;	
	// }

	protected function getAxisHydrationMode()
	{
		return array_key_exists('axis_hydration_mode', $this->options) ? $this->options['axis_hydration_mode'] : $this->default_axis_hydration_mode;
	}


	/**
	 * Guess the getter method name for retrieving the related entity on $axis
	 * 
	 * @param  string $axis x or y
	 * @return string
	 */
	public function getterMethodForAssociation($axis)
	{
		if(!array_key_exists($axis, $this->getters)){
			$this->getters[$axis] = $this->to_camel_case('get_'.$this->assocnameByAxis($axis));
		}

		return $this->getters[$axis];
	}

	public function setterMethodForAssociation($axis)
	{
		if(!array_key_exists($axis, $this->setters)){
			$this->setters[$axis] = $this->to_camel_case('set_'.$this->assocnameByAxis($axis));
		}

		return $this->setters[$axis];
	}

	public function setterMethodLinkValue()
	{
		if(!isset($this->setter_link_value)){
			$this->setter_link_value = $this->to_camel_case('set_'.$this->value_property);
		}

		return $this->setter_link_value;
	}

	// TODO: move to a better place
	public function getLinkedEntityByAxis($link_instance, $axis)
	{
		$method = $this->getterMethodForAssociation($axis);

		return call_user_func(array($link_instance, $method));
	}

	public function setLinkValue($link_instance, $value)
	{
		$value_setter = $this->setterMethodLinkValue();

		return call_user_func_array(array($link_instance, $value_setter), array($value));
	}

	protected function assocnameByAxis($axis)
	{
		$assoc_fieldname_prop = 'association_fieldname_'.$axis;

		return $this->$assoc_fieldname_prop;
	}

	/**
	 * Translates a string with underscores into camel case (e.g. first_name -&gt; firstName)
	 * @param    string   $str                     String in underscore format
	 * @param    bool     $capitalise_first_char   If true, capitalise the first char in $str
	 * @return   string                              $str translated into camel caps
	 *
	 * http://www.paulferrett.com/2009/php-camel-case-functions/
	 */
	 public function to_camel_case($str, $capitalise_first_char = false) {
	    if($capitalise_first_char) {
	      $str[0] = strtoupper($str[0]);
	    }
	    $func = create_function('$c', 'return strtoupper($c[1]);');
	    return preg_replace_callback('/_([a-z])/', $func, $str);
	 }
	

	
}
