<?php
/**
 * @since 10/10/05
 * @package polyphony.library.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLFileDataPartImporter.class.php,v 1.15 2007/09/04 20:28:01 adamfranco Exp $
 */ 
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLImporter.class.php");

/**
 * imports the filedata of a file, how interesting
 * 
 * @since 10/10/05
 * @package polyphony.library.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLFileDataPartImporter.class.php,v 1.15 2007/09/04 20:28:01 adamfranco Exp $
 */
class XMLFileDataPartImporter extends XMLImporter {
		
	/**
	 * 	Constructor
	 * 
	 * 
	 * @return object XMLFileDataPartImporter
	 * @access public
	 * @since 10/10/05
	 */
	function XMLFileDataPartImporter ($existingArray) {
		parent::XMLImporter($existingArray);
	}

	/**
	 * Sets up importer's self-knowledge
	 * 
	 * @access public
	 * @since 10/10/05
	 */
	function setupSelf () {
		$this->_childImporterList = NULL;
		$this->_childElementList = NULL;
		$this->_info = array();
	}
	
	/**
	 * Filters nodes of incorrect type
	 * 
	 * @param object DOMIT_Node
	 * @return boolean
	 * @static
	 * @access public
	 * @since 10/10/05
	 */
	function isImportable ($element) {
		if ($element->nodeName == "filedatapart")
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
	 * @since 10/10/05
	 */
	function importNode () {
		$idManager = Services::getService("Id");

		$this->getNodeInfo();

		if (in_array($this->_info['parentId']->getIdString(),
				$this->_existingArray) || ($this->_type == "update")) {
			$this->_myId =$this->_info['fileDataId'];
			$this->_object =$this->_parent->getPart($this->_myId);
			$this->_object2 = 
				$this->_parent->getPart($this->_info['filenameId']);
			$this->update();
		} else {
			$this->_object =$this->_parent->createPart(
				$this->_info['partStructureId'], 
				file_get_contents($this->_info['value']));
			$this->_myId =$this->_object->getId();
			$this->_object2 =$this->_parent->createPart(
				$this->_info['namePartStructureId'], $this->_info['filename']);
		}
	}

	/**
	 * Sets the node's internal information
	 * 
	 * @access public
	 * @since 10/10/05
	 */
	function getNodeInfo () {
		$idManager = Services::getService("Id");
	
		$path = $this->_node->getText();
		if (!ereg("^([a-zA-Z]+://|[a-zA-Z]+:\\|/)", $path))
			$path = $this->_node->ownerDocument->xmlPath.$path;
		
		$this->_info['value'] = $path;
		
		$this->_info['partStructureId'] =$idManager->getId("FILE_DATA");
					
		$this->_info['namePartStructureId'] =$idManager->getId("FILE_NAME");
		
		$this->_info['filename'] = basename($this->_info['value']);

		$this->_info['parentId'] = $this->_parent->getId();
		
		$this->_info['fileDataId'] =$idManager->getId(
			$this->_info['parentId']->getIdString()."-FILE_DATA");
		
		$this->_info['filenameId'] =$idManager->getId(
			$this->_info['parentId']->getIdString()."-FILE_NAME");
	}
	
	/**
	 * Relegates Children to their classes
	 * 
	 * @param object mixed $topImporter is the importer instance that parsed the XML
	 * @access public
	 * @since 10/10/05
	 */
	function relegateChildren ($topImporter) {
	}
	
	/**
	 * Looks for discrepencies between imported data and current data
	 * 
	 * @access public
	 * @since 10/10/05
	 */
	function update () {
		if (isset($this->_info['value']) && !is_null($this->_info['value']) && 
			(file_get_contents($this->_info['value']) !=
			$this->_object->getValue()))
		$this->_object->updateValue(file_get_contents($this->_info['value']));
		if (isset($this->_info['filename']) && !is_null($this->_info['filename']) && ($this->_info['filename'] != $this->_object2->getValue()))
			$this->_object2->updateValue($this->_info['filename']);
	}
}

?>