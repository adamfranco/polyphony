<?php
/**
 * @since 9/21/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLFileRecordImporter2.class.php,v 1.1 2005/09/22 13:51:55 cws-midd Exp $
 */ 

require_once(POLYPHONY."/main/library/Importer/XMLImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLFileNamePartImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLFileDataPartImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLMIMEPartImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLThumbDataPartImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLThumbMIMEPartImporter.class.php");

/**
 * Imports a File Record
 * 
 * @since 9/21/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLFileRecordImporter2.class.php,v 1.1 2005/09/22 13:51:55 cws-midd Exp $
 */
class XMLFileRecordImporter extends XMLImporter {
		
	/**
	 * Constructor
	 * 
	 * @param object DOMIT_Node
	 * @param object HarmoniRepository
	 * @access public
	 * @since 9/12/05
	 */
	function XMLRecordImporter (&$element, &$asset) {
		$this->_node =& $element;
		$this->_childImporterList = array ("XMLFileNamePartImporter",
			"XMLFileDataPartImporter", "XMLMIMEPartImporter", 
			"XMLThumbDataPartImporter", "XMLThumbMIMEPartImporter",
			"XMLFilepathPartImporter", "XMLThumbpathPartImporter");
		$this->_childElementLIst = NULL;
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
		if ($element->nodeName == "filerecord")
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
			$this->_record =& $this->_asset->createRecord(
				$this->_info['recordStructureId']);
		else {
			$idString = $this->_node->getAttribute("id");
			$id =& $idManager->getId($idString);
			$this->_record =& $this->_asset->getRecord($id);
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
		
		$this->_info['recordStructureId'] =& $idManager->getId("FILE");
	}
	
	/**
	 * Relegates Children to their classes
	 * 
	 * @access public
	 * @since 9/12/05
	 */
	function relegateChildren () {
		foreach ($this->_node->childNodes as $element)
			foreach ($this->_childImporterList as $importer) {
				eval('$result = '.$importer.'::isImportable($element);');
				if ($result) {
					$imp =& new $importer($element, $this->_record, 
						$this->_asset);
					$imp->import($this->_type);
					unset($imp);
				}
			}
	}
	
	/**
	 * Looks for discrepencies between imported data and current data
	 * 
	 * @access public
	 * @since 9/12/05
	 */
	function update () {
	}
}

?>