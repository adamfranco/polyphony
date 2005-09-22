<?php
/**
 * @since 9/12/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLFileRecordImporter.class.php,v 1.1 2005/09/22 13:51:55 cws-midd Exp $
 */ 

require_once(POLYPHONY."/main/library/Importer/XMLImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLFileNamePartImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLFileDataPartImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLMIMEPartImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLThumbDataPartImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLThumbMIMEPartImporter.class.php");

/**
 * XMLFileRecordImporter imports a file record into an asset
 * 
 * @since 9/12/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLFileRecordImporter.class.php,v 1.1 2005/09/22 13:51:55 cws-midd Exp $
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
	function XMLFileRecordImporter (&$element, &$asset) {
		$this->_node =& $element;
		$this->_childImporterList = array("XMLFileNamePartImporter",
			"XMLFileDataPartImporter", "XMLMIMEPartImporter", 
			"XMLThumbDataPartImporter", "XMLThumbMIMEPartImporter");
		$this->_childElementList = array("filepath", "mime", "thumb", "thumbmime");
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
		$mime =& Services::getService("MIME");
		$image =& Services::getService("ImageProcessor");
		
		$FILE_ID =& $idManager->getId("FILE");
		$FILE_DATA_ID =& $idManager->getId("FILE_DATA");
		$FILE_NAME_ID =& $idManager->getId("FILE_NAME");
		$MIME_TYPE_ID =& $idManager->getId("MIME_TYPE");
		$THUMBNAIL_DATA_ID =& $idManager->getId("THUMBNAIL_DATA");
		$THUMBNAIL_MIME_TYPE_ID =& $idManager->getId("THUMBNAIL_MIME_TYPE");
		
		$this->getNodeInfo();
				
		if (!$this->_node->hasAttribute("id")) {
			$this->_record =& $this->_asset->createRecord($FILE_ID);

			$this->_data_part =& $this->_record->createPart(
				$FILE_DATA_ID, file_get_contents($this->_info['filepath']));
			$this->_name_part =& $this->_record->createPart(
				$FILE_NAME_ID, basename($this->_info['filepath']));
			$this->_mime_part =& $this->_record->createPart(
				$MIME_TYPE_ID, $this->_info['mime']);
			$this->_thumb_data_part =& $this->_record->createPart(
				$THUMBNAIL_DATA_ID,	$this->_info['thumb']);
			$this->_thumb_mime_part =& $this->_record->createPart(
				$THUMBNAIL_MIME_TYPE_ID, $this->_info['thumbmime']);
		}
		else {
			$idString = $this->_node->getAttribute("id");
			$id =& $idManager->getId($idString);
			$this->_record =& $this->_asset->getRecord($id);
			foreach ($this->_node->childNodes as $element) {
				if (in_array($element->nodeName, $this->_childElementList)) {
					
			if ($this->_type == "update")
				$this->update();
		}
	}

	/**
	 * Sets the node's internal information
	 * 
	 * This function is the opposite of the other getNodeInfo functions in that
	 * it gathers the information from the child nodes and not from itself, due
	 * to the unique circumstances surrounding FileRecords.
	 * @access public
	 * @since 9/12/05
	 */
	function getNodeInfo () {
		if ($this->_node->hasChildNodes())
			foreach ($this->_node->childNodes as $element) {
				if (in_array($element->nodeName, $this->_childElementList)) {
					$helper = "build".ucfirst($element->nodeName);
					$this->$helper($element);
				}
			}
		if (!isset($this->_info['filepath']))
			throwError(new Error("need filepath for filerecord", "", TRUE));
		if (!isset($this->_info['mime'])) {
			$mime =& Services::getService("MIME");
			$this->_info['mime'] = $mime->getMIMETypeForFileName(
				$this->_info['filepath']);
		}
		if (!isset($this->_info['thumb'])) {
			$image =& Services::getService("ImageProcessor");
			if ($image->isFormatSupported($this->_info['mime']))
				$this->_info['thumb'] =& $image->generateThumbnailData(
					$this->_info['mime'], file_get_contents(
					$this->_info['filepath']));
		}
		if(!isset($this->_info['thumbmime']))
			$this->_info['thumbmime'] = $image->getThumbnailFormat();
	}
		
	/**
	 * Looks for discrepencies between imported data and current data
	 * 
	 * @access public
	 * @since 9/12/05
	 */
	function update () {
		// what to do here?
	}
	
	/**
	 * Adds the filepath into info
	 * 
	 * @param object DOMIT_Node 
	 * @access public
	 * @since 9/20/05
	 */
	function buildFilepath ($element) {
		$this->_info['filepath'] = $element->getText();
	}
	
	/**
	 * Adds the mime type into info
	 * 
	 * @param object DOMIT_Node 
	 * @access public
	 * @since 9/20/05
	 */
	function buildMime ($element) {
		$this->_info['mime'] = $element->getText();
	}
	
	/**
	 * Adds the filepath into info
	 * 
	 * @param object DOMIT_Node 
	 * @access public
	 * @since 9/20/05
	 */
	function buildThumb ($element) {
		$this->_info['thumb'] = $element->getText();
	}
	
	/**
	 * Adds the filepath into info
	 * 
	 * @param object DOMIT_Node 
	 * @access public
	 * @since 9/20/05
	 */
	function buildThumbmime ($element) {
		$this->_info['thumbmime'] = $element->getText();
	}
	
}

?>