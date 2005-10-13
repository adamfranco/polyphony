<?php
/**
 * @since 10/5/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLImporter.class.php,v 1.6 2005/10/13 12:52:13 cws-midd Exp $
 *
 * @author Christopher W. Shubert
 */ 

require_once(HARMONI."/utilities/Dearchiver.class.php");
require_once(DOMIT);
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLRepositoryImporter.class.php");

/**
 * This class provides the ability to import objects into a Harmoni Package
 * 
 * @since 10/5/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLImporter.class.php,v 1.6 2005/10/13 12:52:13 cws-midd Exp $
 */
class XMLImporter {
		
 	/**
 	 * 	Constructor
 	 * 
 	 * The object is the object on which the import is acting (repository, etc.) 
 	 * and should only be missing if the import is at the application level.
 	 * 
 	 * @return object XMLImporter
 	 * @access public
 	 * @since 10/5/05
 	 */
 	function XMLImporter () {
 		$this->setupSelf();
 	}

	/**
	 * Constructor with XML File to parse
	 * 
	 * @param string
	 * @param string
	 * @param string
	 * @return object mixed
	 * @access public
	 * @since 10/11/05
	 */
	function &withFile ($filepath, $type, $class = 'XMLImporter') {
		if (!(strtolower($class) == strtolower('XMLImporter')
			|| is_subclass_of(new $class, 'XMLImporter')))
		{
			die("Class, '$class', is not a subclass of 'XMLImporter'.");
		}

		$importer =& new $class;
		$importer->_xmlFile = $filepath;
		$importer->_type = $type;
		$importer->_basepath = dirname($filepath);
		$importer->setupSelf();
		
		return $importer;
	}
	
	/**
	 * Constructor with XMLFile and starting object
	 * 
	 * @param object mixed
	 * @param string
	 * @param string
	 * @param string
	 * @return object mixed
	 * @access public
	 * @since 10/11/05
	 */
	function &withObject (&$object, $filepath, $type, $class = 'XMLImporter') {
		if (!(strtolower($class) == strtolower('XMLImporter')
			|| is_subclass_of(new $class, 'XMLImporter')))
		{
			die("Class, '$class', is not a subclass of 'XMLImporter'.");
		}
		eval('$importer =& '.$class.'::withFile($filepath, $type, $class);');

		$importer->_object =& $object;
		
		return $importer;
	}

	/**
	 * Sets up importer's self-knowledge
	 * 
	 * @access public
	 * @since 10/5/05
	 */
	function setupSelf () {
		$this->_childImporterList = array("XMLRepositoryImporter");/*, "XMLSetImporter", "XMLHierarchyImporter", "XMLGroupImporter", "XMLAgentImporter");*/
		$this->_childElementList = array("repository", "set", "hierarchy", 
			"group", "agent");
		$this->_info = array();
	}

	/**
	 * Creates the DOMIT Document and calls import
	 *
	 * @access public
	 * @since 10/5/05
	 */
	function parseAndImportBelow () {
		$this->_import =& new DOMIT_Document();
		if ($this->_import->loadXML($this->_xmlFile)) {
			if (!($this->_import->documentElement->hasChildNodes()))
				$this->addError("There are no Importables in this file");
			else {
				$this->_node =& $this->_import->documentElement;
				$this->importBelow();
			}
		}
		else {
			$this->addError("DOMIT error: ".$this->_import->getErrorCode().
			"<br/>\t meaning: ".$this->_import->getErrorString()."<br/>");
		}
	}
	
	/**
	 * Creates the DOMIT Document and calls import
	 *
	 * @access public
	 * @since 10/5/05
	 */
	function parseAndImport () {
		$this->_import =& new DOMIT_Document();
		if ($this->_import->loadXML($this->_xmlFile)) {
			if (!($this->_import->documentElement->hasChildNodes()))
				$this->addError("There are no Importables in this file");
			else {
				$this->_node =& $this->_import->documentElement;
				$null = null;
				$this->import($this->_node, $this->_type, $null);
			}
		}
		else {
			$this->addError("DOMIT error: ".$this->_import->getErrorCode().
			"<br/>\t meaning: ".$this->_import->getErrorString()."<br/>");
		}
	}	
	
	/**
	 * Starts the import (no parameters, because should be set)
	 * 
	 * @return object HarmoniId
	 * @access public
	 * @since 10/11/05
	 */
	function importBelow () {
		$this->doIdMatrix();
		$this->relegateChildren();
		$this->dropIdMatrix();
		
		if (isset($this->_myId)) {
			$this->printErrorMessages();
			return $this->_myId;
		}
	}
	
	/**
	 * Organizes the import
	 * 
	 * @param object DOMIT_Node
	 * @param string
	 * @param object mixed
	 * @param string
	 * @return object HarmoniId
	 * @access public
	 * @since 10/5/05
	 */
	function import (&$node, $type = null, &$parent) {
		$this->_node =& $node;
		$this->_type = $type;
		$this->_parent =& $parent;
		$this->importNode();
		unset($this->_info);

		return $this->importBelow();
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
		/* this is the "application level" importer, it doesn't import info */
	}
	
	/**
	 * Relegates Children to their classes
	 * 
	 * @access public
	 * @since 10/5/05
	 */
	function relegateChildren () {				
		foreach ($this->_node->childNodes as $element)
			foreach ($this->_childImporterList as $importer) {
				eval('$result = '.$importer.'::isImportable($element);');
				if ($result) {
					$imp =& new $importer();
					$imp->import($element, $this->_type, $this->_object);
					unset($imp);
				}
			}
	}
	
	/**
	 * sets the node's info
	 * 
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
				else
					$this->addError($helper."() does not exist");
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

	/**
	 * Builds a type object from a type import node
	 * 
	 * @param object DOMIT_Node
	 * @access public
	 * @since 9/12/05
	 */
	function &buildType (&$element) {
		$pieces = $element->childNodes;
		$type = array();
		foreach ($pieces as $piece)
			$type[$piece->nodeName] = $piece->getText();
		if (isset($type['description']))
			$this->_info['type'] =& new Type($type['domain'],
				$type['authority'], $type['keyword'], $type['description']);	
		else 
			$this->_info['type'] =& new Type($type['domain'],
				$type['authority'], $type['keyword']);
	}	

	/**
	 * Builds a dimensions object from a type import node
	 * 
	 * @param object DOMIT_Node
	 * @access public
	 * @since 10/10/05
	 */
	function &buildDimensions (&$element) {
		$pieces = $element->childNodes;
		$dim = array();
		foreach ($pieces as $piece)
			$dim[$piece->nodeName] = $piece->getText();
		$this->_info['value'] = array($dim['height'], $dim['width'], 
			$dim['type'], "height=\"".$dim['height'].
			"\"width=\"".$dim['width']."\"");
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
	 * Print the AssetIds for Assets created properly by the importer
	 *
	 * @param array $goodAssetIds
	 * @since 7/29/05
	 */
	 function printErrorMessages() {
	 	foreach ($this->_errors as $errorString) {
	 		print($this->_myId.": Error: ".$errorString."<br />");
	 	}
	 }

	
	/**
	 * Print the AssetIds for Assets created properly by the importer
	 *
	 * @param array $goodAssetIds
	 * @since 7/29/05
	 */
	 function printGoodAssetIds() {
	 	foreach ($this->_goodAssetIds as $id) {
	 		print("Asset: ".$id->getIdString()."<br />");
	 	}
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
		$this->_errors[] = $error;
	}

	/**
	 * gets created assset ids array
	 * 
	 * @return array
	 * @access public
	 * @since 7/26/05
	 */
	function getGoodAssetIds() {
		return $this->_goodAssetIds;
	}

	/**
	 * checks for built Assets
	 * 
	 * @return boolean
	 * @access public
	 * @since 7/26/05
	 */
	function hasAssets() {
		return (count($this->_goodAssetIds) > 0);
	}

	/**
	 * adds an error to the  error array
	 * 
	 * @param String $error
	 * @access public
	 * @since 7/26/05
	 */
	function addGoodAssetId($goodAssetId) {
		if (!isset($this->_errors))
			$this->_errors = array();
		$this->_goodAssetIds[] = $goodAssetId;
	}
	
	/**
	 * 
	 * 
	 * @return void
	 * @access public
	 * @since 7/20/05
	 */
	function decompress ($filepath) {
		$dearchiver =& new Dearchiver();
		$worked = $dearchiver->uncompressFile($filepath,
			dirname($filepath));
		if ($worked == false)
			$this->addError("Failed to decompress file: ".$filepath.
				".  Unsupported archive extension.");
	 unset($dearchiver);	
	}
	
}

?>