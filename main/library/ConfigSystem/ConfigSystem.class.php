<?

/**
 *
 * @package 
 * @copyright 2004
 * @version $Id: ConfigSystem.class.php,v 1.1 2004/06/01 18:40:38 gabeschine Exp $
 */
class ConfigSystem {

	/**
	 * @variable object $_typeDef The {@link DataSetTypeDefinition} corresponding to our config setup.
	 * @access private
	 **/
	var $_typeDef;
	
	/**
	 * @variable boolean $_setup Specifies if we are in setup mode, or "change settings" mode.
	 * @access private
	 **/
	var $_setup;
	
	/**
	 * @variable object $_dataSet The {@link DataSet} which is associated with this config system.
	 * @access private
	 **/
	var $_dataSet;
	
	/**
	 * @variable object $_dataSetType A {@link HarmoniType} object associated with our DataSet.
	 * @access private
	 **/
	var $_dataSetType;
	/**
	 * @variable array $_defaults An array of {@link DataType}s describing the default values for each property.
	 * @access private
	 **/
	var $_defaults;
	
	/**
	 * The constructor.
	 * @param string $program The name of the program to setup configuration for, such as "Segue" or "Concerto".
	 **/
	function ConfigSystem($program) {
		// here we need to get/create a DataSetTypeDefinition for this setup
		if (!Services::serviceAvailable("DataSetTypeManager")) {
			throwError(
				new Error(
					"ConfigSystem - could not continue because the DataSetTypeManager service is not available!","ConfigSystem",true)
					);
			return;
		}
		
		$typeManager =& Services::getService("DataSetTypeManager");
		
		$this->_dataSetType =& new HarmoniType("Polyphony","ConfigSystem",$program);
		
		$typeDef =& $typeManager->newDataSetType($this->_dataSetType);
		
		$this->_typeDef =& $typeDef;
		
		$this->_setup = true;
		$this->_dataSet = null;
		$this->_defaults = array();
	}
	
	/**
	 * Adds a property with the name & type specified. This is used while setting up the config system.
	 * @param ref object $field A {@link FieldDefinition} defining the properties of this field.
	 * @param optional object $default The default value for this property. Must be the correct class that is associated
	 * with the $type value, as defined by the DataTypeManager.
	 * @access public
	 * @return void
	 */
	function addProperty($field, $default=null)
	{
		$typeManager =& Services::getService("DataTypeManager");
		
		if ($default == null || $typeManager->isObjectOfDataType($default, $field->getType())) {
			$this->_typeDef->addNewField( $field );
			if ($default) $this->_defaults[$field->getLabel()] =& $default;
		}
	}
	
	/**
	 * Tells the ConfigSystem to finish setup and change into a mode where settings can actually be changed/added, etc.
	 * @access public
	 * @return void
	 */
	function finishSetup()
	{
		$manager =& Services::getService("DataSetTypeManager");
		$manager->synchronize($this->_typeDef);
		
		// now go through and set up all our default values.
		$dataSet =& $this->getDataSet();
		foreach (array_keys($this->_defaults) as $key) {
			$dataSet->addValue($key,$this->_defaults[$key]);
		}
		
		// and we're done.
		$this->_setup = false;
	}
	
	/**
	 * Saves all the config data to the database.
	 * @access public
	 * @return void
	 */
	function save()
	{
		$dataSet->getDataSet();
		$dataSet->commit();
	}
	
	/**
	 * Returns a {@link DataSet} ready for editing permissions.
	 * @access public
	 * @return ref object
	 */
	function &getDataSet()
	{
		if ($this->_setup) {
			throwError(
				new Error("ConfigSystem::getDataSet() - setup must first be completed by calling finishSetup() before the DataSet will be available.","ConfigSystem",true));
		}
		
		if ($this->_dataSet) return $this->_dataSet;
		$dataSetManager =& Services::getService("DataSetManager");
		
		// first we will search the dataset manager to see if it has any datasets (hopefully only one!!) of the type
		// that we are looking for. otherwise, we will create a new one.
		$search = new AndSearch();
		$search->addCriteria(new DataSetTypeSearch($this->_typeDef->getType()));
		$search->addCriteria(new OnlyThisSearch());
		$search->addCriteria(new ActiveDataSetsSearch());
		
		$ids = $dataSetManager->selectIDsBySearch($search);
		
		// do we have any ids?
		if (count($ids)) {
			// we'll use the first id, since there really only should be one.
			$id = $ids[0];
			$this->_dataSet =& $dataSetManager->fetchDataSet($id, true);
		} else {
			$this->_dataSet =& $dataSetManager->newDataSet($this->_dataSetType, false);
		}
		
		return $this->_dataSet;
	}
	
}