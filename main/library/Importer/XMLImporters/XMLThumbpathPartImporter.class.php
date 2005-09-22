<?php
/**
 * @since 9/21/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLThumbpathPartImporter.class.php,v 1.1 2005/09/22 13:51:55 cws-midd Exp $
 */ 

/**
 * imports the thumbnaildata, and thumbnailmimetype of a file
 * 
 * @since 9/21/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLThumbpathPartImporter.class.php,v 1.1 2005/09/22 13:51:55 cws-midd Exp $
 */
class XMLThumbpathPartImporter extends XMLImporter {
		
	/**
	 * Constructor
	 * 
	 * @param object DOMIT_Node
	 * @param object HarmoniRepository
	 * @access public
	 * @since 9/12/05
	 */
	function XMLThumbpathPartImporter (&$element, &$record, $asset) {
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
		if ($element->nodeName == "thumbpathpart")
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
		$image =& Services::getService("ImageProcessor");

		$THUMBNAIL_DATA_ID =& $idManager->getId("THUMBNAIL_DATA");
		$THUMBNAIL_MIME_TYPE_ID =& $idManager->getId("THUMBNAIL_MIME_TYPE");

		$this->getNodeInfo();

		if (!$this->_node->hasAttribute("id") && $image->isFormatSupported(
			$this->_info['mime'])) {
			$this->_record->createPart($THUMBNAIL_DATA_ID,
				$image->generateThumbnailData($this->_info['mime'],
				file_get_contents($this->_info['filepath'])));
			$this->_record->createPart($THUMBNAIL_MIME_TYPE_ID,
				$image->getThumbnailFormat());
		}
	}

	/**
	 * Sets the node's internal information
	 * 
	 * @access public
	 * @since 9/12/05
	 */
	function getNodeInfo () {
		$mime =& Services::getService("MIME");
		
		$this->_info['filepath'] = $this->_node->getText();
		$this->_info['mime'] = $mime->getMIMETypeForFileName(
			$this->_info['filepath']);
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