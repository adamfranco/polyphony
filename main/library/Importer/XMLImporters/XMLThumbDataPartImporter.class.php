<?php
/**
 * @since 9/21/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLThumbDataPartImporter.class.php,v 1.1 2005/09/22 13:51:55 cws-midd Exp $
 */ 

/**
 * imports the thumbnail data of a file, how interesting
 * 
 * @since 9/21/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLThumbDataPartImporter.class.php,v 1.1 2005/09/22 13:51:55 cws-midd Exp $
 */
class XMLThumbDataImporter extends XMLImporter {
		
	/**
	 * Constructor
	 * 
	 * @param object DOMIT_Node
	 * @param object HarmoniRepository
	 * @access public
	 * @since 9/12/05
	 */
	function XMLThumbDataPartImporter (&$element, &$record, $asset) {
		$this->_node =& $element;
		$this->_childImporterList = NULL;
		$this->_childElementList = NULL;
		$this->_record =& $record;
		$this->_asset =& $asset;
	}
	
	/**
	 * Filters nodes of incorrect type
	 * 
	 * @param object DOMIT_Node
	 * @return boolean
	 * @static
	 * @access public
	 * @since 9/12/05
	 */
	function isImportable (&$element) {
		if ($element->nodeName == "thumbdatapart")
			return true;
		else
			return false;
	}

	/**
	 * Imports the current node's information
	 * 
	 * @access public
	 * @since 9/12/05
	 */
	function importNode () {
		$idManager =& Services::getService("Id");
		
		$this->getNodeInfo();

		if (!$this->_node->hasAttribute("id"))
			$this->_part =& $this->_record->createPart(
				$this->_info['partStructureId'], $this->_info['value']);
		else {
			$idString = $this->_node->getAttribute("id");
			$id =& $idManager->getId($idString);
			$this->_part =& $this->_asset->getPart($id);
			if ($this->_type == "update")
				$this->update();
		}
	}

	/**
	 * Sets the node's internal information
	 * 
	 * @access public
	 * @since 9/12/05
	 */
	function getNodeInfo () {
		$idManager =& Services::getService("Id");
		
		$this->_info['partStructureId'] =& $idManager->getId("THUMBNAIL_DATA");
				
		$this->_info['value'] = $this->_node->getText();
	}
	
	/**
	 * Relegates Children to their classes
	 * 
	 * @access public
	 * @since 9/12/05
	 */
	function relegateChildren () {
	}
	
	/**
	 * Looks for discrepencies between imported data and current data
	 * 
	 * @access public
	 * @since 9/12/05
	 */
	function update () {
		if ($this->_info['value'] != $this->_part->getValue())
			$this->_part->updateValue($this->_info['value']);
	}
}

?>