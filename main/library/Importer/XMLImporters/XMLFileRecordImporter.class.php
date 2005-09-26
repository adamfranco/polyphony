<?php
/**
 * @since 9/21/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLFileRecordImporter.class.php,v 1.3 2005/09/26 17:56:22 cws-midd Exp $
 */ 

require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLFileNamePartImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLFileDataPartImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLMIMEPartImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLThumbDataPartImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLThumbMIMEPartImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLThumbpathPartImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLFilepathPartImporter.class.php");

/**
 * Imports a File Record
 * 
 * @since 9/21/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLFileRecordImporter.class.php,v 1.3 2005/09/26 17:56:22 cws-midd Exp $
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
	function XMLFileRecordImporter (&$element, $tableName, &$asset) {
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
		// if there is no separate thumbpath 
		$idManager =& Services::getService("Id");
		$THUMB_ID =& $idManager->getId("THUMBNAIL_DATA");
		$iterator =& $this->_record->getPartsByPartStructure($THUMB_ID);
		if ($iterator->count() == 0) {
			$elements =& $this->_node->getElementsByTagName("filepath");
			$element =& $elements[0];
			$imp =& new XMLThumbpathPartImporter($element);
			$imp->import($this->_type);
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