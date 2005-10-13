<?php
/**
 * @since 10/10/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLMIMEPartImporter.class.php,v 1.5 2005/10/13 17:36:51 cws-midd Exp $
 */ 
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLImporter.class.php");

/**
 * imports the mimetype of a file, how interesting
 * 
 * @since 10/10/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLMIMEPartImporter.class.php,v 1.5 2005/10/13 17:36:51 cws-midd Exp $
 */
class XMLMIMEPartImporter extends XMLImporter {
		
		
	/**
	 * 	Constructor
	 * 
	 * 
	 * @return object XMLMIMEPartImporter
	 * @access public
	 * @since 10/10/05
	 */
	function XMLMIMEPartImporter () {
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
		if ($element->nodeName == "mimepart")
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
				$this->_info['partStructureId'], $this->_info['value']);
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
		
		$this->_info['partStructureId'] =& $idManager->getId("MIME_TYPE");
				
		$this->_info['value'] = $this->_node->getText();
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