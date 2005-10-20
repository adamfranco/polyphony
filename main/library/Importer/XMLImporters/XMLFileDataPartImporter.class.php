<?php
/**
 * @since 10/10/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLFileDataPartImporter.class.php,v 1.8 2005/10/20 18:33:39 cws-midd Exp $
 */ 
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLImporter.class.php");

/**
 * imports the filedata of a file, how interesting
 * 
 * @since 10/10/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLFileDataPartImporter.class.php,v 1.8 2005/10/20 18:33:39 cws-midd Exp $
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
	function XMLFileDataPartImporter () {
		parent::XMLImporter();
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
	function isImportable (&$element) {
		if ($element->nodeName == "filedatapart")
			return true;
		else
			return false;
	}

	/**
	 * Imports the current node's information
	 * 
	 * @access public
	 * @since 10/10/05
	 */
	function importNode () {
		$idManager =& Services::getService("Id");
		
		$this->getNodeInfo();

		if ($this->_node->hasAttribute("isExisting") && 		
			($this->_node->getAttribute("isExisting") == TRUE)) {
			$this->_myId =& $idManager->getId($this->_node->getAttribute("id"));
			$this->_object =& $this->_parent->getPart($this->_myId);
		} else if (($this->_type == "insert") || 
			(!$this->_node->hasAttribute("id"))) {
			$this->_object =& $this->_parent->createPart(
				$this->_info['partStructureId'], 
				file_get_contents($this->_info['value']));
			$this->_myId =& $this->_object->getId();
			$this->_object2 =& $this->_parent->createPart(
				$this->_info['namePartId'], $this->_info['filename']);
		} else {
			$this->_myId =& $idManager->getId($this->_node->getAttribute("id"));
			$this->_object =& $this->_parent->getPart($this->_myId);				
		}
		if ($this->_type == "update")
				$this->update();
	}

	/**
	 * Sets the node's internal information
	 * 
	 * @access public
	 * @since 10/10/05
	 */
	function getNodeInfo () {
		$idManager =& Services::getService("Id");
	
		$path = $this->_node->getText();
		if (!ereg("^([a-zA-Z]+://|[a-zA-Z]+:\\|/)", $path))
			$path = $this->_node->ownerDocument->xmlPath.$path;
		
		$this->_info['value'] = $path;
	
print "filepath: ".$path;
	
		$this->_info['partStructureId'] =& $idManager->getId("FILE_DATA");
					
		$this->_info['namePartId'] =& $idManager->getId("FILE_NAME");
		
		$this->_info['filename'] = basename($this->_info['value']);
	}
	
	/**
	 * Relegates Children to their classes
	 * 
	 * @access public
	 * @since 10/10/05
	 */
	function relegateChildren () {
	}
	
	/**
	 * Looks for discrepencies between imported data and current data
	 * 
	 * @access public
	 * @since 10/10/05
	 */
	function update () {
		if ($this->_info['value'] != $this->_object->getValue())
			$this->_object->updateValue($this->_info['value']);
			
		$parts =& $this->_parent->getPartsByPartStructure(
			$this->_info['namePartId']);
		if ($parts->count() == 1) {
			$this->object2 =& $parts->next();	
			if ($this->_info['filename'] != $this->_object2->getValue())
				$this->_object2->updateValue($this->_info['filename']);
		}
	}
}

?>