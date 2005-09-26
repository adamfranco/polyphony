<?php
/**
 * @since 9/21/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLFilepathPartImporter.class.php,v 1.3 2005/09/26 17:56:22 cws-midd Exp $
 */ 
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLImporter.class.php");

/**
 * imports the filename, filedata, and mimetype of a file
 * 
 * @since 9/21/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLFilepathPartImporter.class.php,v 1.3 2005/09/26 17:56:22 cws-midd Exp $
 */
class XMLFilepathPartImporter extends XMLImporter {
		
	/**
	 * Constructor
	 * 
	 * @param object DOMIT_Node
	 * @param object HarmoniRepository
	 * @access public
	 * @since 9/12/05
	 */
	function XMLFilepathPartImporter (&$element, &$record, $asset) {
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
		if ($element->nodeName == "filepathpart")
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
		$mime =& Services::getService("MIME");
		
		$FILE_DATA_ID =& $idManager->getId("FILE_DATA");
		$FILE_NAME_ID =& $idManager->getId("FILE_NAME");
		$MIME_TYPE_ID =& $idManager->getId("MIME_TYPE");
		$THUMBNAIL_DATA_ID =& $idManager->getId("THUMBNAIL_DATA");
		$THUMBNAIL_MIME_TYPE_ID =& $idManager->getId("THUMBNAIL_MIME_TYPE");

		$this->getNodeInfo();

		if (!$this->_node->hasAttribute("id")) {
			$this->_record->createPart($FILE_DATA_ID, 
				file_get_contents($this->_info['filepath']));
			$this->_record->createPart($FILE_NAME_ID,
				basename($this->_info['filepath']));
			$this->_record->createPart($MIME_TYPE_ID,
				$mime->getMIMETypeForFileName($this->_info['filepath']));
		}
	}

	/**
	 * Sets the node's internal information
	 * 
	 * @access public
	 * @since 9/12/05
	 */
	function getNodeInfo () {
		$this->_info['filepath'] = $this->_node->getText();
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
	}
}

?>