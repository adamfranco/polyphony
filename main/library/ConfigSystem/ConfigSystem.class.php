<?

/**
*
* @package
* @copyright 2004
* @version $Id: ConfigSystem.class.php,v 1.3 2004/07/22 19:36:49 gabeschine Exp $
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
	* @variable array $_defaults An array of descriptions for each property.
	* @access private
	**/
	var $_descriptions;

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
		$this->_descriptions = array();
	}

	/**
	* Adds a property with the name & type specified. This is used while setting up the config system.
	* @param ref object $field A {@link FieldDefinition} defining the properties of this field.
	* @param optional string $description An optional description of this property. 
	* @param optional object $default The default value for this property. Must be the correct class that is associated
	* with the $type value, as defined by the DataTypeManager.
	* @access public
	* @return void
	*/
	function addProperty($field, $description = "", $default=null)
	{
		$typeManager =& Services::getService("DataTypeManager");

		if ($default == null || $typeManager->isObjectOfDataType($default, $field->getType())) {
			$this->_typeDef->addNewField( $field );
			if ($default) $this->_defaults[$field->getLabel()] =& $default;
			$this->_descriptions[$field->getLabel()] = $description;
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

		// now go through and set up all our default values. but only do so if it's not already stored in the DB.
		$dataSet =& $this->getDataSet(true);
		if (!$dataSet->getID()) {
			foreach (array_keys($this->_defaults) as $key) {
				if ($this->_defaults[$key])
					$dataSet->setValue($key,$this->_defaults[$key],NEW_VALUE);
			}
		}

		// and we're done.
		$this->_setup = false;
	}

	/**
	 * Returns the string description of the named property.
	 * @param string $key The property name.
	 * @access public
	 * @return string
	 */
	function getDescriptions($key)
	{
		return $this->_descriptions[$key];
	}
	
	/**
	* Saves all the config data to the database.
	* @access public
	* @return void
	*/
	function save()
	{
		$dataSet =& $this->getDataSet();
		$dataSet->commit();
	}

	/**
	* Returns a {@link DataSet} ready for editing permissions.
	* @param optional boolean $force This option is only used internally.
	* @access public
	* @return ref object
	*/
	function &getDataSet($force=false)
	{
		if ($this->_setup && !$force) {
			throwError(
			new Error("ConfigSystem::getDataSet() - setup must first be completed by calling finishSetup() before the DataSet will be available.","ConfigSystem",true));
		}

		if ($this->_dataSet) return $this->_dataSet;
		$dataSetManager =& Services::getService("DataSetManager");

		// first we will search the dataset manager to see if it has any datasets (hopefully only one!!) of the type
		// that we are looking for. otherwise, we will create a new one.
		$search = new AndSearch();
		//		$search->addCriteria(new DataSetTypeSearch($this->_typeDef->getType()));
		$search->addCriteria(new DataSetTypeSearch($this->_typeDef->getType()));
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