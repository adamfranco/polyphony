<?php
/**
 * @since 9/6/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLImporter.class.php,v 1.4 2005/09/28 19:13:24 cws-midd Exp $
 *
 * @author Christopher W. Shubert
 */ 

require_once(HARMONI."/utilities/Dearchiver.class.php");
require_once(DOMIT);
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLRepositoryImporter.class.php");

/**
 * This class provides the ability to import objects into a Harmoni Package
 * 
 * @since 9/6/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLImporter.class.php,v 1.4 2005/09/28 19:13:24 cws-midd Exp $
 */
class XMLImporter {
		
	/**
	 * 	Constructor
	 * 
	 * @return object XMLImporter
	 * @access public
	 * @since 9/6/05
	 */
	function XMLImporter ($filepath) {
		$this->_xmlFile = $filepath;
		$this->_childImporterList = array("XMLRepositoryImporter");/*, "XMLSetImporter", "XMLHierarchyImporter", "XMLGroupImporter", "XMLAgentImporter");*/
		$this->_childElementList = array("repository", "set", "hierarchy", "group",
			"agent");
		$this->_info = array();
		$this->_tableName = basename(dirname($filepath));
	}
	
	/**
	 * Creates the DOMIT Document and calls import
	 * 
	 * @access public
	 * @since 9/8/05
	 */
	function parse () {
		$this->_import =& new DOMIT_Document();
		if ($this->_import->loadXML($this->_xmlFile)) {
			if (!($this->_import->documentElement->hasChildNodes()))
				throwError(new Error("There are no Importables in this file",
					"Importer", TRUE));
			else {
				$this->_node =& $this->_import->documentElement;
				$this->import($this->_node->getAttribute("type"));
			}
		}
		else {
			print "error: ".$this->_import->getErrorCode()."<br/>";
			print "meaning: ".$this->_import->getErrorString()."<br/>";
			throwError(new Error("did not parse", "Importer", TRUE));
		}
	}
	
	/**
	 * Organizes the import
	 * 
	 * @return boolean
	 * @access public
	 * @since 9/8/05
	 */
	function import ($type) {
		$this->_type = $type;
		$this->importNode();
		unset($this->_info);
		$this->relegateChildren();
	}
	
	/**
	 * Imports the node itself
	 * 
	 * @access public
	 * @since 9/9/05
	 */
	function importNode () {
	}
	
	/**
	 * Relegates Children to their classes
	 * 
	 * @access public
	 * @since 9/9/05
	 */
	function relegateChildren () {				
		foreach ($this->_node->childNodes as $element)
			foreach ($this->_childImporterList as $importer) {
				eval('$result = '.$importer.'::isImportable($element);');
				if ($result) {
					$imp =& new $importer($element, $this->_tableName);
					$imp->import($this->_type);
					unset($imp);
				}
			}
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
	 		print("Error: ".$errorString."<br />");
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