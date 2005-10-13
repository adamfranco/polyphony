<?php
/**
 * @since 10/6/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLFileRecordImporter.class.php,v 1.6 2005/10/13 12:52:13 cws-midd Exp $
 */ 

require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLFileDataPartImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLMIMEPartImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLFileDimensionsPartImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLThumbDataPartImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLThumbMIMEPartImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLThumbDimensionsPartImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLThumbpathPartImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLFilepathPartImporter.class.php");

/**
 * Imports a File Record
 * 
 * @since 10/6/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLFileRecordImporter.class.php,v 1.6 2005/10/13 12:52:13 cws-midd Exp $
 */
class XMLFileRecordImporter extends XMLImporter {
		
	/**
	 * 	Constructor
	 * 
	 * The object is the object on which the import is acting (repository, etc.) 
	 * and should only be missing if the import is at the application level.
	 * 
	 * @return object XMLFileRecordImporter
	 * @access public
	 * @since 10/6/05
	 */
	function XMLFileRecordImporter () {
		parent::XMLImporter();
	}

	/**
	 * Sets up importer's self-knowledge
	 * 
	 * @access public
	 * @since 10/6/05
	 */
	function setupSelf () {
		$this->_childImporterList = array ("XMLFileDataPartImporter",
			"XMLMIMEPartImporter", "XMLFileDimensionsPartImporter",
			"XMLThumbDataPartImporter", "XMLThumbMIMEPartImporter", 
			"XMLThumbDimensionsPartImporter", "XMLFilepathPartImporter", 
			"XMLThumbpathPartImporter");
		$this->_childElementLIst = NULL;
		$this->_info = array();
	}
	
	/**
	 * Filters nodes of incorrect type
	 * 
	 * @param object DOMIT_Node
	 * @return boolean
	 * @static
	 * @access public
	 * @since 10/6/05
	 */
	function isImportable (&$element) {
		if ($element->nodeName == "filerecord")
			return true;
		else
			return false;
	}

	/**
	 * Imports the current node's information
	 * 
	 * @access public
	 * @since 10/6/05
	 */
	function importNode () {
		$idManager =& Services::getService("Id");
		
		$this->getNodeInfo();
		
		if (($this->_type == "insert") || (!$this->_node->hasAttribute("id"))) {
			$this->_object =& $this->_parent->createRecord(
				$this->_info['recordStructureId']);
			$this->_myId =& $this->_object->getId();
		} else {
			$this->_myId =& $idManager->getId($this->_node->getAttribute("id"));
			$this->_object =& $this->_parent->getRecord($id);
		}
	}

	/**
	 * Sets the node's internal information
	 * 
	 * @access public
	 * @since 10/6/05
	 */
	function getNodeInfo () {
		$idManager =& Services::getService("Id");
		
		$this->_info['recordStructureId'] =& $idManager->getId("FILE");
	}
	
	/**
	 * Relegates Children to their classes
	 * 
	 * @access public
	 * @since 10/6/05
	 */
	function relegateChildren () {
		$filepath = FALSE;
		$thumbpath = FALSE;
		foreach ($this->_node->childNodes as $element) {
			if ($element->nodeName == "filepathpart")
				$filepath = TRUE;
			else if ($element->nodeName == "thumbpathpart")
				$thumbpath = TRUE;
			foreach ($this->_childImporterList as $importer) {
				eval('$result = '.$importer.'::isImportable($element);');
				if ($result) {
					$imp =& new $importer();
					$imp->import($element, $this->_type, $this->_object);
					unset($imp);
				}
			}
		}
		if ($filepath && !$thumbpath) {
			$elements =& $this->_node->getElementsByTagName("filepathpart");
			$element =& $elements->item(0);
			$imp =& new XMLThumbpathPartImporter();
			$imp->import($element, $this->_type, $this->_object);
			unset($imp);
		}
	}
}
?>