<?php
/**
 * @since 9/12/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLFileRecordImporter.class.php,v 1.1 2005/09/21 14:05:45 cws-midd Exp $
 */ 

require_once(POLYPHONY."/main/library/Importer/XMLImporter.class.php");

/**
 * XMLFileRecordImporter imports a file record into an asset
 * 
 * @since 9/12/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLFileRecordImporter.class.php,v 1.1 2005/09/21 14:05:45 cws-midd Exp $
 */
class XMLFilePartImporter extends XMLImporter {
		
	/**
	 * Constructor
	 * 
	 * @param object DOMIT_Node
	 * @param object HarmoniRepository
	 * @access public
	 * @since 9/12/05
	 */
	function XMLFileRecordImporter (&$element, &$record, $asset) {
		$this->_node =& $element;
		$this->_childImporterList = NULL;
		$this->_childElementList = array("filepath", "mime", "thumb", "thumbmime");
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
			$this->
			$this->_data_part =& $this->_record->createPart(
				$FILE_DATA_ID, file_get_contents($this->_info['filepath']));
			$this->_name_part =& $this->_record->createPart(
				$FILE_NAME_ID, basename($this->_info['filepath']));
			if ($this->_node->
			
			$this->_mimetype =& $mime->getMIMETypeForFileName(
				$this->_info['filepath']);
			$this->_mime_part =& $this->_record->createPart(
				$MIME_TYPE_ID, $this->_mimetype);
			if ($image->isFormatSupported($this->_mimetype))
				$this->_thumb_data_part =& $this->_record->createPart(
					$THUMBNAIL_DATA_ID, $image->generateThumbnailData(
					$this->_mimetype, file_get_contents(
					$this->_info['filepath'])));
			
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
		if ($this->_node->hasChildNodes())
			foreach ($this->_node->childNodes as $element) {
				if (in_array($element->nodeName, $this->_childElementList)) {
					$helper = "build".ucfirst($element->nodeName);
					$this->$helper($element);
				}
			}
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
					$imp =& new $importer($element, $this->_part, 
						$this->_asset);
					$imp->import($this->_type);
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
		if ($this->_info['value'] != $this->_part->getValue())
			$this->_part->updateValue($this->_info['value']);
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