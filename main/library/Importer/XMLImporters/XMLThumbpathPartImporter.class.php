<?php
/**
 * @since 10/6/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLThumbpathPartImporter.class.php,v 1.5 2005/10/13 17:36:51 cws-midd Exp $
 */ 
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLImporter.class.php");

/**
 * imports the thumbnaildata, and thumbnailmimetype of a file
 * 
 * @since 10/6/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLThumbpathPartImporter.class.php,v 1.5 2005/10/13 17:36:51 cws-midd Exp $
 */
class XMLThumbpathPartImporter extends XMLImporter {
		
	/**
	 * 	Constructor
	 * 
	 * 
	 * @return object XMLThumbpathPartImporter
	 * @access public
	 * @since 10/6/05
	 */
	function XMLThumbpathPartImporter () {
		parent::XMLImporter();
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
	 * Filters nodes of incorrect type
	 * 
	 * @param object DOMIT_Node
	 * @return boolean
	 * @static
	 * @access public
	 * @since 10/6/05
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
	 * @since 10/6/05
	 */
	function importNode () {
		$idManager =& Services::getService("Id");
		$image =& Services::getService("ImageProcessor");

		$THUMBNAIL_DATA_ID =& $idManager->getId("THUMBNAIL_DATA");
		$THUMBNAIL_MIME_TYPE_ID =& $idManager->getId("THUMBNAIL_MIME_TYPE");

		$this->getNodeInfo();

		if ($image->isFormatSupported($this->_info['mime'])) {
			$this->_parent->createPart($THUMBNAIL_DATA_ID,
				$image->generateThumbnailData($this->_info['mime'],
				file_get_contents($this->_info['filepath'])));
			$this->_parent->createPart($THUMBNAIL_MIME_TYPE_ID,
				$image->getThumbnailFormat());
		}
		
		$this->_myId = null;
	}

	/**
	 * Sets the node's internal information
	 * 
	 * @access public
	 * @since 10/6/05
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
		/* there should be no child nodes of this element */
	}
}

?>