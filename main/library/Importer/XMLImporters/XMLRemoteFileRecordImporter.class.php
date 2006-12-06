<?php
/**
 * @since 12/6/06
 * @package polyphony.library.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLRemoteFileRecordImporter.class.php,v 1.1 2006/12/06 22:17:21 adamfranco Exp $
 */ 
 
require_once(dirname(__FILE__)."/XMLFileRecordImporter.class.php");
require_once(dirname(__FILE__)."/XMLFileUrlPartImporter.class.php");

/**
 * <##>
 * 
 * @since 12/6/06
 * @package polyphony.library.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLRemoteFileRecordImporter.class.php,v 1.1 2006/12/06 22:17:21 adamfranco Exp $
 */
class XMLRemoteFileRecordImporter 
	extends XMLFileRecordImporter
{
		
	/**
	 * Sets up importer's self-knowledge
	 * 
	 * @access public
	 * @since 10/6/05
	 */
	function setupSelf () {
		$this->_childImporterList = array ("XMLFileUrlPartImporter",
			"XMLMIMEPartImporter", "XMLFileDimensionsPartImporter",
			"XMLThumbDataPartImporter", "XMLThumbMIMEPartImporter", 
			"XMLThumbDimensionsPartImporter", "XMLFilepathPartImporter", 
			"XMLThumbpathPartImporter");
		$this->_childElementLIst = NULL;
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
		if ($element->nodeName == "remotefilerecord")
			return true;
		else
			return false;
	}
	
	/**
	 * Sets the node's internal information
	 * 
	 * @access public
	 * @since 10/6/05
	 */
	function getNodeInfo () {
		$idManager =& Services::getService("Id");
		
		$this->_info['recordStructureId'] =& $idManager->getId("REMOTE_FILE");
	}
	
}

?>