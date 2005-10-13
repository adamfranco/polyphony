<?php
/**
 * @since 10/6/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLFilepathPartImporter.class.php,v 1.6 2005/10/13 17:36:51 cws-midd Exp $
 */ 
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLImporter.class.php");

/**
 * imports the filename, filedata, and mimetype of a file
 * 
 * @since 10/6/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLFilepathPartImporter.class.php,v 1.6 2005/10/13 17:36:51 cws-midd Exp $
 */
class XMLFilepathPartImporter extends XMLImporter {
		
	/**
	 * 	Constructor
	 * 
	 * 
	 * @return object XMLFilepathImporterImporter
	 * @access public
	 * @since 10/6/05
	 */
	function XMLFilepathPartImporter () {
		parent::XMLImporter();
	}
	
	/**
	 * Filters nodes of incorrect type
	 * 
	 * @param object DOMIT_Node
	 * @return boolean
	 * @static
	 * @access public
	 * @since 10/6/05
	 */
	function isImportable (&$element) {
		if ($element->nodeName == "filepathpart")
			return true;
		else
			return false;
	}

	/**
	 * Sets up importer's self-knowledge
	 * 
	 * @access public
	 * @since 10/6/05
	 */
	function setupSelf () {
		$this->_childImporterList = NULL;
		$this->_childElementList = NULL;
		$this->_info = array();
	}

	/**
	 * Imports the current node's information
	 * 
	 * @access public
	 * @since 10/6/05
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

		$this->_parent->createPart($FILE_DATA_ID, 
			file_get_contents($this->_info['filepath']));
		$this->_parent->createPart($FILE_NAME_ID,
			basename($this->_info['filepath']));
		$this->_parent->createPart($MIME_TYPE_ID,
			$mime->getMIMETypeForFileName($this->_info['filepath']));
			
		$this->_myId = null;
	}

	/**
	 * Sets the node's internal information
	 * 
	 * @access public
	 * @since 10/6/05
	 */
	function getNodeInfo () {
		$this->_info['filepath'] = $this->_node->getText();
	}
	
	/**
	 * Relegates Children to their classes
	 * 
	 * @access public
	 * @since 10/6/05
	 */
	function relegateChildren () {
		/* this node should not have children */
	}
}

?>