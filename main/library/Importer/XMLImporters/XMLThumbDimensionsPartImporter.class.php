<?php
/**
 * @since 10/10/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLThumbDimensionsPartImporter.class.php,v 1.5 2005/12/12 17:06:26 cws-midd Exp $
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
 * @version $Id: XMLThumbDimensionsPartImporter.class.php,v 1.5 2005/12/12 17:06:26 cws-midd Exp $
 */
class XMLThumbDimensionsPartImporter extends XMLImporter {
		
	/**
	 * 	Constructor
	 * 
	 * 
	 * @return object XMLThumbDimensionsPartImporter
	 * @access public
	 * @since 10/10/05
	 */
	function XMLThumbDimensionsPartImporter (&$existingArray) {
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
		if ($element->nodeName == "thumbdimensionspart")
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
			$idManager->getId("THUMBNAIL_DIMENSIONS");
				
		$this->buildDimensions($this->_node);

		$this->_info['parentId'] =& $this->_parent->getId();
		
		$this->_info['id'] =& 
			$idManager->getId($this->_info['parentId']->getIdString().
			"-THUMBNAIL_DIMENSIONS");
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
		if (!is_null($this->_info['value']) && ($this->_info['value'] != $this->_object->getValue()))
			$this->_object->updateValue($this->_info['value']);			
	}
}

?>