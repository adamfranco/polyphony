<?php
/**
 * @since 10/10/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLThumbDataPartImporter.class.php,v 1.9 2005/11/03 21:13:15 cws-midd Exp $
 */ 
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLImporter.class.php");

/**
 * imports the thumbnail data of a file, how interesting
 * 
 * @since 10/10/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLThumbDataPartImporter.class.php,v 1.9 2005/11/03 21:13:15 cws-midd Exp $
 */
class XMLThumbDataPartImporter extends XMLImporter {
		
	/**
	 * 	Constructor
	 * 
	 * @return object XMLThumbDataPartImporter
	 * @access public
	 * @since 10/10/05
	 */
	function XMLThumbDataPartImporter (&$existingArray) {
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
	function isImportable (&$element) {
		if ($element->nodeName == "thumbdatapart")
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
		$idManager =& Services::getService("Id");
		
		$this->getNodeInfo();

		if ($this->_node->hasAttribute("id") && 
			in_array($this->_node->getAttribute("id"), $this->_existingArray)) {
			$this->_myId =& $idManager->getId($this->_node->getAttribute("id"));
			$this->_object =& $this->_parent->getPart($this->_myId);
		} else if (($this->_type == "insert") || 
			(!$this->_node->hasAttribute("id"))) {
			$this->_object =& $this->_parent->createPart(
				$this->_info['partStructureId'], 
				file_get_contents($this->_info['value']));
			$this->_myId =& $this->_object->getId();
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
		
		$this->_info['partStructureId'] =& $idManager->getId("THUMBNAIL_DATA");
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
	}
}

?>