<?php
/**
 * @since 10/10/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLThumbMIMEPartImporter.class.php,v 1.9 2006/02/09 20:16:49 cws-midd Exp $
 */ 
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLImporter.class.php");

/**
 * imports the thumbnail mime type of a file, how interesting
 * 
 * @since 10/10/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLThumbMIMEPartImporter.class.php,v 1.9 2006/02/09 20:16:49 cws-midd Exp $
 */
class XMLThumbMIMEPartImporter extends XMLImporter {
	/**
	 * 	Constructor
	 * 
	 * 
	 * @return object XMLThumbMIMEPartImporter
	 * @access public
	 * @since 10/10/05
	 */
	function XMLThumbMIMEPartImporter (&$existingArray) {
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
		if ($element->nodeName == "thumbmimepart")
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

		if (in_array($this->_info['parentId']->getIdString(),
				$this->_existingArray) || ($this->_type == "update")) {
			$this->_myId =& $this->_info['id'];
			$this->_object =& $this->_parent->getPart($this->_myId);
			$this->update();
		} else {
			$this->_object =& $this->_parent->createPart(
				$this->_info['partStructureId'], $this->_info['value']);
			$this->_myId =& $this->_object->getId();
		}
	}

	/**
	 * Sets the node's internal information
	 * 
	 * @access public
	 * @since 10/10/05
	 */
	function getNodeInfo () {
		$idManager =& Services::getService("Id");
		
		$this->_info['partStructureId'] =& 
			$idManager->getId("THUMBNAIL_MIME_TYPE");
				
		$this->_info['value'] = $this->_node->getText();
		
		$this->_info['parentId'] =& $this->_parent->getId();
		
		$this->_info['id'] =& $idManager->getId(
			$this->_info['parentId']->getIdString()."-THUMBNAIL_MIME_TYPE");

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
		if (isset($this->_info['value']) && !is_null($this->_info['value']) && ($this->_info['value'] != $this->_object->getValue()))
			$this->_object->updateValue($this->_info['value']);
	}
}

?>