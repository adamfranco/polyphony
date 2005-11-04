<?php
/**
 * @since 10/6/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLFileRecordImporter.class.php,v 1.9 2005/11/04 20:33:30 cws-midd Exp $
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
 * @version $Id: XMLFileRecordImporter.class.php,v 1.9 2005/11/04 20:33:30 cws-midd Exp $
 */
class XMLFileRecordImporter extends XMLImporter {
		
	/**
	 * 	Constructor
	 * 
	 * 
	 * @return object XMLFileRecordImporter
	 * @access public
	 * @since 10/6/05
	 */
	function XMLFileRecordImporter (&$existingArray) {
		parent::XMLImporter($existingArray);
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
	 * Checks if the user is able to import underneath this level
	 *
	 * @param string $authZQString qualifier for authz checking
	 * @access public
	 * @since 11/3/05
	 */
	function canImportBelow($authZQString) {
		return true;
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
		
		$hasId = $this->_node->hasAttribute("id");
		if ($hasId && (in_array($this->_node->getAttribute("id"),
				$this->_existingArray)	|| $this->_type == "update")) {
			$this->_myId =& $idManager->getId($this->_node->getAttribute("id"));
			$this->_object =& $this->_parent->getRecord($this->_myId);
		} else {
			$this->_object =& $this->_parent->createRecord(
				$this->_info['recordStructureId']);
			$this->_myId =& $this->_object->getId();
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
				if (!is_subclass_of(new $importer($this->_existingArray), 'XMLImporter')) {
					$this->addError("Class, '$class', is not a subclass of 'XMLImporter'.");
					break;
				}
				eval('$result = '.$importer.'::isImportable($element);');
				if ($result) {
					$imp =& new $importer($this->_existingArray);
					$imp->import($element, $this->_type, $this->_object);
					if ($imp->hasErrors())
						foreach($imp->getErrors() as $error)
							$this->addError($error);
					unset($imp);
				}
			}
		}
		if ($filepath && !$thumbpath) {
			$elements =& $this->_node->getElementsByTagName("filepathpart");
			$element =& $elements->item(0);
			$imp =& new XMLThumbpathPartImporter($this->_existingArray);
			$imp->import($element, $this->_type, $this->_object);
			if ($imp->hasErrors())
				foreach($imp->getErrors() as $error)
					$this->addError($error);
			unset($imp);
		}
	}
}
?>