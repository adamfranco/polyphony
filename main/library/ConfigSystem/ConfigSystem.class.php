<?

/**
*
* @package
* @copyright 2004
* @version $Id: ConfigSystem.class.php,v 1.4 2004/07/29 03:44:03 gabeschine Exp $
*/
class ConfigSystem {

	/**
	* @variable object $_schema The {@link Schema} corresponding to our config setup.
	* @access private
	**/
	var $_schema;

	/**
	* @variable boolean $_setup Specifies if we are in setup mode, or "change settings" mode.
	* @access private
	**/
	var $_setup;

	/**
	* @variable object $_record The {@link Record} which is associated with this config system.
	* @access private
	**/
	var $_record;

	/**
	* @variable object $_schemaType A {@link HarmoniType} object associated with our Record.
	* @access private
	**/
	var $_schemaType;
	/**
	* @variable array $_defaults An array of {@link Primitive}s describing the default values for each property.
	* @access private
	**/
	var $_defaults;

	/**
	* The constructor.
	* @param string $program The name of the program to setup configuration for, such as "Segue" or "Concerto".
	**/
	function ConfigSystem($program) {
		// here we need to get/create a Schema for this setup
		if (!Services::serviceAvailable("SchemaManager")) {
			throwError(
			new Error(
			"ConfigSystem - could not continue because the SchemaManager service is not available!","ConfigSystem",true)
			);
			return;
		}

		$typeManager =& Services::getService("SchemaManager");

		$this->_schemaType =& new HarmoniType("Polyphony","ConfigSystem",$program);
		
		$schema =& $typeManager->newSchema($this->_schemaType);

		$this->_schema =& $schema;

		$this->_setup = true;
		$this->_record = null;
		$this->_defaults = array();
	}

	/**
	* Adds a property with the name & type specified. This is used while setting up the config system.
	* @param ref object $field A {@link SchemaField} defining the properties of this field.
	* @param optional object $default The default value for this property. Must be the correct class that is associated
	* with the {@link SchemaField} type value, as defined by the DataTypeManager.
	* @access public
	* @return void
	*/
	function addProperty($field, $default=null)
	{
		$typeManager =& Services::getService("DataTypeManager");

		if ($default == null || $typeManager->isObjectOfDataType($default, $field->getType())) {
			$this->_schema->addField( $field );
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
		$manager =& Services::getService("SchemaManager");
		$manager->synchronize($this->_schema);

		// now go through and set up all our default values. but only do so if it's not already stored in the DB.
		$record =& $this->getRecord(true);
		if (!$record->getID()) {
			foreach (array_keys($this->_defaults) as $key) {
				if ($this->_defaults[$key])
					$record->setValue($key,$this->_defaults[$key],NEW_VALUE);
			}
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
		if (is_object($this->_record)) 
			$this->_record->commit();
	}

	/**
	* Returns a {@link Record} ready for editing permissions.
	* @param optional boolean $force This option is only used internally.
	* @access public
	* @return ref object
	*/
	function &getRecord($force=false)
	{
		if ($this->_setup && !$force) {
			throwError(
			new Error("ConfigSystem::getRecord() - setup must first be completed by calling finishSetup() before the Record will be available.","ConfigSystem",true));
		}

		if ($this->_record) return $this->_record;
		$recordManager =& Services::getService("RecordManager");

		// first we will search the record manager to see if it has any records (hopefully only one!!) of the type
		// that we are looking for. otherwise, we will create a new one.
		$ids = $recordManager->getRecordIDsByType($this->_schemaType);

		// do we have any ids?
		if (count($ids)) {
			// we'll use the first id, since there really only should be one.
			$id = $ids[0];
			$this->_record =& $recordManager->fetchRecord($id, true);
			// if for some reason the record is not active, let's activate it
			if (!$this->_record->isActive()) {
				$this->_record->setActiveFlag(true);
			}
		} else {
			$this->_record =& $recordManager->newRecord($this->_schemaType, false);
		}

		return $this->_record;
	}

}