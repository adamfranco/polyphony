<?php
/**
 * @since 10/5/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLImporter.class.php,v 1.25 2006/06/02 14:28:59 cws-midd Exp $
 *
 * @author Christopher W. Shubert
 */ 

require_once(HARMONI."/utilities/Dearchiver.class.php");
require_once(DOMIT);
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLRepositoryImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLRepositoryFileImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/StatusStars.class.php");

/**
 * This class and its children provide the ability to import objects into a 
 * Harmoni Based Application
 * 
 * @since 10/5/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLImporter.class.php,v 1.25 2006/06/02 14:28:59 cws-midd Exp $
 */
class XMLImporter {

/*********************************************************
 * CONSTRUCTORS AND INITIALIZATION
 *********************************************************/

 	/**
 	 * 	Constructor
 	 * 
 	 * @param array $existingArray contains the idStrings of any objects that 
 	 * may be in the xml file but are not to be created.
 	 * @return object XMLImporter
 	 * @access public
 	 * @since 10/5/05
 	 */
 	function XMLImporter (&$existingArray) {
	 	$this->setupSelf();		// gives the importer knowledge about itself
	 	$this->_errors = array();	// end-user friendly error handling
	 	$this->_existingArray =& $existingArray;
 	}

	/**
	 * Constructor with XML File to parse
	 * 
 	 * @param array $existingArray contains the idStrings of any objects that 
 	 * may be in the xml file but are not to be created.
	 * @param string $filepath path to the xml file with importable data
	 * @param string $type type of the import (update/insert)
	 * @param string $class class of the importer to instantiate
	 * @return object mixed 
	 * @access public
	 * @since 10/11/05
	 */
	function &withFile (&$existingArray, $filepath, $type, $class = 'XMLImporter') {
		if (!(strtolower($class) == strtolower('XMLImporter')
			|| is_subclass_of(new $class($existingArray), 'XMLImporter')))
		{
			throwError(new Error ("Class, '$class', is not a subclass of 'XMLImporter'.", "XMLImporter"));
		}
		eval('$importer =& new '.$class.'($existingArray);');
		$importer->_xmlFile = $filepath;
		$importer->_type = $type;

		return $importer;
	}
	
	/**
	 * Constructor with XMLFile and starting object
	 * 
 	 * @param array $existingArray contains the idStrings of any objects that 
 	 * may be in the xml file but are not to be created.
	 * @param object mixed $object the object underneath which importer acts
	 * @param string $filepath path to the xml data file
	 * @param string $type type of the import (update/insert)
	 * @param string $class class of the import to instantiate
	 * @return object mixed
	 * @access public
	 * @since 10/11/05
	 */
	function &withObject (&$existingArray, &$object, $filepath, $type, $class = 'XMLImporter') {
		if (!(strtolower($class) == strtolower('XMLImporter')
			|| is_subclass_of(new $class($existingArray), 'XMLImporter')))
		{
			throwError(new Error ("Class, '$class', is not a subclass of 'XMLImporter'.", "XMLImporter"));
		}
		eval('$importer =& '.$class.'::withFile($existingArray, $filepath, $type, $class);');

		// this object is already existant; the imported objects will go under
		$importer->_object =& $object;
		$importer->_myId =& $importer->_object->getId();
		
		return $importer;
	}

	/**
	 * Sets up importer's self-knowledge
	 * 
	 * This knowledge is a list of importers that are available below this and
	 * the xml elements that correspond to these importers.
	 * @access public
	 * @since 10/5/05
	 */
	function setupSelf () {
		$this->_childImporterList = array("XMLRepositoryImporter", "XMLRepositoryFileImporter");/*, "XMLSetImporter", "XMLHierarchyImporter", "XMLGroupImporter", "XMLAgentImporter");*/
		$this->_childElementList = array("repository", "repositoryfile", "set",
			"hierarchy", "group", "agent");
		$this->_info = array();	// stores information about importing element

	}

/*********************************************************
 * ACTIVE IMPORT FUNCTIONS
 *********************************************************/

	/**
	 * Creates the DOMIT Document and Imports the data below the target
	 *
	 * This is used to import data underneath an object that already exists
	 * in the system.
	 * @param string $granule the xml element to count for status
	 * @param int $detail the number of divisions for status
	 * @access public
	 * @since 10/5/05
	 */
	function parseAndImportBelow ($granule, $detail = 50) {
		if (Services::serviceRunning("Logging")) {
			$loggingManager =& Services::getService("Logging");
			$log =& $loggingManager->getLogForWriting("Harmoni");
			$formatType =& new Type("logging", "edu.middlebury", "AgentsAndNodes",
							"A format in which the acting Agent[s] and the target nodes affected are specified.");
			$priorityType =& new Type("logging", "edu.middlebury", "Error",
							"Events involving critical system errors.");
		}
		$this->_import =& new DOMIT_Document();
		// attempt to load (parse) the xml file
		if ($this->_import->loadXML($this->_xmlFile)) {
			// xmlPath is used for finding files tared up with the import
			$this->_import->xmlPath = dirname($this->_xmlFile)."/";
			// @todo check the xml structure against what is expected
			if (!$this->_checkXMLStructure()) {
				$this->addError("This file can not be handled by this importer");
				// log error
				$item =& new AgentNodeEntryItem("XMLImporter Error",
					"Improper Import Format: ".$this->_xmlFile.".");
				if (isset($log))
					$log->appendLogWithTypes($item, $formatType, $priorityType);
			}
			else if (!($this->_import->documentElement->hasChildNodes())) {
				$this->addError("There are no Importables in this file");
				// log error
				$item =& new AgentNodeEntryItem("XMLImporter Error",
					"No Importables in the file: ".$this->_xmlFile.".");
				if (isset($log))
					$log->appendLogWithTypes($item, $formatType, $priorityType);
			} else {
				// the parsing importer is responsible for the docElement
				$this->_node =& $this->_import->documentElement;
	 			$this->setupStatusBar($granule, $detail);
	 			if (isset($this->_myId))
					$this->importBelow($this, $this->_myId->getIdString());
				else
					$this->importBelow($this, 
						"edu.middlebury.authorization.root");
			}
		}
		else {
			// any errors encountered by DOMIT in parsing handled here
			$this->addError("DOMIT error: ".$this->_import->getErrorCode().
			"<br/>\t meaning: ".$this->_import->getErrorString()."<br/>");
			$item =& new AgentNodeEntryItem("XMLImporter DOMIT Error",
				"Error Code: ".$this->_import->getErrorCode().", meaning: ".
				$this->_import->getErrorString().".");
			if (isset($log))
				$log->appendLogWithTypes($item, $formatType, $priorityType);
		}
	}
	
	/**
	 * Creates the DOMIT Document and Imports the data including the top target
	 *
	 * @param string $granule the xml element to count for status
	 * @param int $detail the number of divisions for status
	 * @access public
	 * @since 10/5/05
	 */
	function parseAndImport ($granule, $detail = 50) {
		if (Services::serviceRunning("Logging")) {
			$loggingManager =& Services::getService("Logging");
			$log =& $loggingManager->getLogForWriting("Harmoni");
			$formatType =& new Type("logging", "edu.middlebury", "AgentsAndNodes",
							"A format in which the acting Agent[s] and the target nodes affected are specified.");
			$priorityType =& new Type("logging", "edu.middlebury", "Error",
							"Events involving critical system errors.");
		}
		$this->_import =& new DOMIT_Document();
		// attempt to load (parse) XML file
		if ($this->_import->loadXML($this->_xmlFile)) {
			// path for finding files associated with import
			$this->_import->xmlPath = dirname($this->_xmlFile)."/";
			// check the xml structure against what is expected
			if (!$this->_checkXMLStructure()) {
				$this->addError("This file can not be handled by this importer");
				// log error
				$item =& new AgentNodeEntryItem("XMLImporter Error",
					"Improper Import Format: ".$this->_xmlFile.".");
				if (isset($log))
					$log->appendLogWithTypes($item, $formatType, $priorityType);
			}
			else if (!($this->_import->documentElement->hasChildNodes())) {
				$this->addError("There are no Importables in this file");
				// log error
				$item =& new AgentNodeEntryItem("XMLImporter Error",
					"No Importables in the file: ".$this->_xmlFile.".");
				if (isset($log))
					$log->appendLogWithTypes($item, $formatType, $priorityType);
			} else {
				// the parsing importer is responsible for the docElement
				$this->_node =& $this->_import->documentElement;
				$this->setupStatusBar($granule, $detail);
				$null = null;
				$this->import($this, $this->_node, $this->_type, $null);
			}
		}
		else {
			// any errors encountered by DOMIT in parsing handled here
			$this->addError("DOMIT error: ".$this->_import->getErrorCode().
			"<br/>\t meaning: ".$this->_import->getErrorString()."<br/>");
			$item =& new AgentNodeEntryItem("XMLImporter DOMIT Error",
				"Error Code: ".$this->_import->getErrorCode().", meaning: ".
				$this->_import->getErrorString().".");
			if (isset($log))
				$log->appendLogWithTypes($item, $formatType, $priorityType);
		}
	}
	
	/**
	 * Starts an import below the qualifier passed
	 * 
	 * @param object mixed $topImporter is the importer instance that parsed the XML
	 * @param string $authZQString string of the qualifier for the object
	 * @return object HarmoniId 
	 * @access public
	 * @since 10/11/05
	 */
	function importBelow (&$topImporter, $authZQString = null) {
		if (!$this->canImportBelow($authZQString))
			return;
		// some importables need to map ids
		if (isset($this->_myId)) {
			$this->doIdMatrix();
		}
		// send the child elements (XML) to the appropriate importers
		$this->relegateChildren($topImporter);
		// done with id mapping
		$this->dropIdMatrix();
		
		if (isset($this->_myId)) {
			return $this->_myId;
		}
	}
	
	/**
	 * Checks if the user is able to import underneath this level
	 *
	 * @todo the authorizations could not keep up with the importer
	 * @param string $authZQString qualifier for authz checking
	 * @access public
	 * @since 11/3/05
	 */
	function canImportBelow($authZQString) {
 // 		$authZ =& Services::getService("AuthZ");
//  		$idManager =& Services::getService("Id");
//  		
//  		if (!$authZ->isUserAuthorized(
//  				$idManager->getId("edu.middlebury.authorization.add_children"),
//  				$idManager->getId($authZQString))) {
// 			$this->addError("No Authorization to Import under ".
// 				get_class($this).": ".$authZQString);
// 			return false;
// 		}
		return true;
	}
	
	/**
	 * Organizes the import
	 * 
	 * @param object mixed $topImporter is the importer instance that parsed the XML
	 * @param object DOMIT_Node &$node domit node for importing
	 * @param string $type type of import (update/insert) 
	 * @param object mixed $parent parent of object to be imported
	 * @return object HarmoniId
	 * @access public
	 * @since 10/5/05
	 */
	function import (&$topImporter, &$node, $type, &$parent) {
		$this->_node =& $node;	// xml element representing datastructure
		$this->_type = $type;	// type of import (currently unimportant)
		$this->_parent =& $parent;	// xml element parent

		$bottom = $this->importNode(); // bottom says do not import below me
		unset($this->_info);	// don't need anymore
		if ($bottom === true)
			return;
		if (isset($this->_myId)) {
			return $this->importBelow($topImporter, 
				$this->_myId->getIdString());
		} else {
			return $this->importBelow($topImporter);
		}
	}

	/**
	 * Does what is necessary to the temporary table for internal id association
	 * 
	 * @access public
	 * @since 10/6/05
	 */
	function doIdMatrix () {
		/* only implemented where needed */
	}
	
	/**
	 * Drops the temporary table for internal id association
	 * 
	 * @access public
	 * @since 10/6/05
	 */
	function dropIdMatrix () {
		/* only implemented where needed */
	}	
	
	/**
	 * Imports the node itself
	 * 
	 * @access public
	 * @since 10/5/05
	 */
	function importNode () {
		$this->_object = null;
	}
	
	/**
	 * Relegates child XML elements to their importer classes
	 *
	 * By matching the xml elements with their importers the importer hierachy
	 * is able to import each element with much customization (detail) making it
	 * easier to handle new elements with new child classes.  This function also
	 * passes any errors encountered in a child importer back up until the
	 * errors reach the top importer where they get printed at the end of the 
	 * import
	 * @param object mixed $topImporter is the importer instance that parsed the XML
	 * @access public
	 * @since 10/5/05
	 */
	function relegateChildren (&$topImporter) {				
		foreach ($this->_node->childNodes as $element) {
			foreach ($this->_childImporterList as $importer) {
				if (!is_subclass_of(new $importer($this->_existingArray),
						'XMLImporter')) {
					$this->addError("Class, '$class', is not a subclass of 'XMLImporter'.");
					// log error
					if (Services::serviceRunning("Logging")) {
						$loggingManager =& Services::getService("Logging");
						$log =& $loggingManager->getLogForWriting("Harmoni");
						$formatType =& new Type("logging", "edu.middlebury",
							"AgentsAndNodes",
							"A format in which the acting Agent[s] and the target nodes affected are specified.");
						$priorityType =& new Type("logging", "edu.middlebury",
							"Error","Events involving critical system errors.");
						$item =& new AgentNodeEntryItem("XMLImporter Error",
							"Class, '$class' is not a subclass of 'XMLImporter'.");
						$log->appendLogWithTypes($item, $formatType, 
							$priorityType);
					}
					break;
				}
				eval('$result = '.$importer.'::isImportable($element);');
				if ($result) {
					$imp =& new $importer($this->_existingArray);
					$imp->import($topImporter, $element, $this->_type, $this->_object);
					// used for bubbling errors to the top...
					if ($imp->hasErrors())
						foreach($imp->getErrors() as $error)
							$this->addError($error);
					unset($imp);
				}
			}
			if ($topImporter->_granule == $element->nodeName)
				$topImporter->_status->updateStatistics();
		}
	}
	
	/**
	 * Populates _info array with data from the xml file
	 *
	 * Retrieves all the necessary information to build the object that the 
	 * current xml element represents.
	 * @access public
	 * @since 10/5/05
	 */
	function getNodeInfo () {
		foreach ($this->_node->childNodes as $element) {
			if (is_null($this->_childElementList) || 
				!in_array($element->nodeName, $this->_childElementList)) {
				$helper = "build".ucfirst($element->nodeName);
				if (method_exists($this, $helper))
					$this->$helper($element);
				else {
					$this->addError($helper."() does not exist");
					// log error
					if (Services::serviceRunning("Logging")) {
						$loggingManager =& Services::getService("Logging");
						$log =& $loggingManager->getLogForWriting("Harmoni");
						$formatType =& new Type("logging", "edu.middlebury",
							"AgentsAndNodes",
							"A format in which the acting Agent[s] and the target nodes affected are specified.");
						$priorityType =& new Type("logging", "edu.middlebury",
							"Error","Events involving critical system errors.");
						$item =& new AgentNodeEntryItem("XMLImporter Error",
							"Function, '$helper' does not exist");
						$log->appendLogWithTypes($item, $formatType,
							$priorityType);
					}
				}
			}
		}
	}
	
	/**
	 * Update
	 * 
	 * @access public
	 * @since 10/10/05
	 */
	function update () {
		/* no update */
	}

/*********************************************************
 * HELPER FUNCTIONS FOR GETTING NODE INFO
 *********************************************************/

	/**
	 * Builds a type object from a type import node
	 * 
	 * @param object DOMIT_Node
	 * @access public
	 * @since 9/12/05
	 */
	function buildType (&$element) {
		$pieces = $element->childNodes;
		$type = array();
		foreach ($pieces as $piece)
			$type[$piece->nodeName] = $piece->getText();
		if (isset($type['description']))
			$this->_info['type'] = new Type($type['domain'],
				$type['authority'], $type['keyword'], $type['description']);	
		else 
			$this->_info['type'] = new Type($type['domain'],
				$type['authority'], $type['keyword']);
	}

	/**
	 * Builds a dimensions object from a type import node
	 * 
	 * @param object DOMIT_Node
	 * @access public
	 * @since 10/10/05
	 */
	function buildDimensions (&$element) {
		$pieces = $element->childNodes;
		$dim = array();
		foreach ($pieces as $piece)
			$dim[$piece->nodeName] = $piece->getText();
		$this->_info['value'] = array($dim['width'], $dim['height']);
	}	
	
	/* Helper function for XML elements
	 * 
	 * @param object DOMIT_Node
	 * @access public
	 * @since 9/13/05
	 */
	function buildName (&$element) {
		$this->_info['name'] = $element->getText();
	}
	
	/**
	 * Helper function for XML elements
	 * 
	 * @param object DOMIT_Node
	 * @access public
	 * @since 9/13/05
	 */
	function buildDescription (&$element) {
		$this->_info['description'] = $element->getText();
	}

	/**
	 * Helper function for XML elements
	 * 
	 * @param object DOMIT_Node
	 * @access public
	 * @since 9/13/05
	 */
	function buildFormat (&$element) {
		$this->_info['format'] = $element->getText();
	}
	
	/**
	 * Helper function for XML elements
	 * 
	 * @param object DOMIT_Node
	 * @access public
	 * @since 9/13/05
	 */
	function buildEffectivedate (&$element) {
		$this->_info['effectivedate'] = $element->getText();
	}
	
	/**
	 * Helper function for XML elements
	 * 
	 * @param object DOMIT_Node
	 * @access public
	 * @since 9/13/05
	 */
	function buildExpirationdate (&$element) {
		$this->_info['expirationdate'] = $element->getText();
	}
	
	/**
	 * 
	 * 
	 * @return string
	 * @access public
	 * @since 7/20/05
	 */
	function decompress ($filepath) {
		$dearchiver =& new Dearchiver();
		$worked = $dearchiver->uncompressFile($filepath,
			dirname($filepath));
		if ($worked == false) {
			$this->addError("Failed to decompress file: ".$filepath.
				".  Unsupported archive extension.");
			// log error
			if (Services::serviceRunning("Logging")) {
				$loggingManager =& Services::getService("Logging");
				$log =& $loggingManager->getLogForWriting("Harmoni");
				$formatType =& new Type("logging", "edu.middlebury",
					"AgentsAndNodes",
					"A format in which the acting Agent[s] and the target nodes affected are specified.");
				$priorityType =& new Type("logging", "edu.middlebury", "Error",
					"Events involving critical system errors.");
				$item =& new AgentNodeEntryItem("XMLImporter Dearchiver Error",
					"Failed to decompress file: $filepath.  Unsupported archive extension.");
				$log->appendLogWithTypes($item, $formatType, $priorityType);
			}
		}
	 	unset($dearchiver);
	 	return dirname($filepath);
	}

/*********************************************************
 * ERROR HANDLING FOR THE IMPORTERS
 *********************************************************/

	/**
	 * This function determines the structure wanted and makes sure it is so
	 * 
	 * sub-classes that can start an import should overwrite this function
	 * @access public
	 * @since 2/23/06
	 */
	function _checkXMLStructure () {
		return ($this->_import->documentElement->nodeName == "import");
	}


	/**
	 * Print the AssetIds for Assets created properly by the importer
	 *
	 * @param array $goodAssetIds
	 * @since 7/29/05
	 */
	 function printErrorMessages() {
	 	print "<div style='color:#FF0000;'>";
	 	foreach ($this->_errors as $errorString) {
	 		print("Error: ".$errorString."<br />");
	 	}
	 	print 'Please, Cancel this wizard and investigate other import options.  See Help.';
	 	print '</div>';
	 }

	/**
	 * gets error array
	 * 
	 * @return array
	 * @access public
	 * @since 7/26/05
	 */
	function getErrors() {
		return $this->_errors;
	}
	
	/**
	 * checks for errors
	 * 
	 * @return boolean
	 * @access public
	 * @since 7/26/05
	 */
	function hasErrors() {
		return (count($this->_errors) > 0);
	}

	/**
	 * adds an error to the  error array
	 * 
	 * @param String $error
	 * @access public
	 * @since 7/26/05
	 */
	function addError($error) {
		if (!isset($this->_errors))
			$this->_errors = array();
		if (isset($this->_myId))
			$this->_errors[] = $this->_myId->getIdString()."::".$error;
		else
			$this->_errors[] = $error;
	}
	
/*********************************************************
 * STATUS BAR UTILITY
 *********************************************************/

	function moreGranulesFromXML () {
		if (Services::serviceRunning("Logging")) {
			$loggingManager =& Services::getService("Logging");
			$log =& $loggingManager->getLogForWriting("Harmoni");
			$formatType =& new Type("logging", "edu.middlebury", "AgentsAndNodes",
							"A format in which the acting Agent[s] and the target nodes affected are specified.");
			$priorityType =& new Type("logging", "edu.middlebury", "Error",
							"Events involving critical system errors.");
		}
		$moreXML =& $this->_import->documentElement->getElementsByTagName(
			"repositoryfile");
		$granules = 0;
		for ($i = 0; $i < $moreXML->getLength(); $i++) {
			$element =& $moreXML->item($i);
			$path = $element->getText();
			if (!ereg("^([a-zA-Z]+://|[a-zA-Z]+:\\|/)", $path))
				$path = $element->ownerDocument->xmlPath.$path;			
		
			$import =& new DOMIT_Document();
			// attempt to load (parse) the xml file
			if ($import->loadXML($path)) {
				if (!($import->documentElement->hasChildNodes())) {
					$this->addError("There are no Importables in this file");
					// log error
					$item =& new AgentNodeEntryItem("XMLImporter Error",
						"No Importables in the file: ".$path.".");
					if (isset($log))
						$log->appendLogWithTypes($item, $formatType, $priorityType);
				} else {
					$nodes =& $import->documentElement->getElementsByTagName(
						$this->_granule);
					$granules += $nodes->getLength();
				}
			}
			else {
				// any errors encountered by DOMIT in parsing handled here
				$this->addError("DOMIT error: ".$import->getErrorCode().
				"<br/>\t meaning: ".$import->getErrorString()."<br/>");
				$item =& new AgentNodeEntryItem("XMLImporter DOMIT Error",
					"Error Code: ".$import->getErrorCode().", meaning: ".
					$import->getErrorString().".");
				if (isset($log))
					$log->appendLogWithTypes($item, $formatType, $priorityType);
			}
		}
		return $granules;
	}

	/**
	 * Sets up the status bar with appropriate granule
	 * 
	 * @param string $granule the xml element for status bar granule
	 * @param int $detail the number of divisions for the status bar
	 * @access public
	 * @since 2/27/06
	 */
	function setupStatusBar ($granule, $detail) {
		// @todo allow for array parameter so that multiple granules are used!
		$this->_granule = $granule;
		$this->_status = new StatusStars();
		
		$granules = 0;
		$nodes =& $this->_import->documentElement->getElementsByTagName(
			$granule);
		$granules += $nodes->getLength();
		
//		$granules += $this->moreGranulesFromXML();

		$this->_status->initializeStatistics($granules, $detail);
	}
	

/*********************************************************
 * ASSET ID TRACKING FOR DEVELOPMENT
 *********************************************************/
// 	/**
// 	 * Print the AssetIds for Assets created properly by the importer
// 	 *
// 	 * @param array $goodAssetIds
// 	 * @since 7/29/05
// 	 */
// 	 function printGoodAssetIds() {
// 	 	foreach ($this->_goodAssetIds as $id) {
// 	 		print("Asset: ".$id->getIdString()."<br />");
// 	 	}
// 	 }
// 
// 	/**
// 	 * gets created assset ids array
// 	 * 
// 	 * @return array
// 	 * @access public
// 	 * @since 7/26/05
// 	 */
// 	function getGoodAssetIds() {
// 		return $this->_goodAssetIds;
// 	}
// 
// 	/**
// 	 * checks for built Assets
// 	 * 
// 	 * @return boolean
// 	 * @access public
// 	 * @since 7/26/05
// 	 */
// 	function hasAssets() {
// 		return (count($this->_goodAssetIds) > 0);
// 	}
// 
// 	/**
// 	 * adds an error to the  error array
// 	 * 
// 	 * @param String $error
// 	 * @access public
// 	 * @since 7/26/05
// 	 */
// 	function addGoodAssetId($goodAssetId) {
// 		if (!isset($this->_errors))
// 			$this->_errors = array();
// 		$this->_goodAssetIds[] = $goodAssetId;
// 	}
	
}

?>