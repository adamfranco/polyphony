<?php
/**
 * @since 10/10/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLThumbDataPartImporter.class.php,v 1.17 2007/10/10 22:58:48 adamfranco Exp $
 */ 
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLImporter.class.php");

/**
 * imports the thumbnail data of a file, how interesting
 * 
 * @since 10/10/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLThumbDataPartImporter.class.php,v 1.17 2007/10/10 22:58:48 adamfranco Exp $
 */
class XMLThumbDataPartImporter extends XMLImporter {
		
	/**
	 * 	Constructor
	 * 
	 * @return object XMLThumbDataPartImporter
	 * @access public
	 * @since 10/10/05
	 */
	function __construct ($existingArray) {
		parent::__construct($existingArray);
	}

	/**
	 * Sets up importer's self-knowledge
	 * 
	 * @access public
	 * @since 10/10/05
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
	 * @since 10/10/05
	 */
	static function isImportable ($element) {
		if ($element->nodeName == "thumbdatapart")
			return true;
		else
			return false;
	}

	/**
	 * Checks if the user is able to import underneath this level
	 *
	 * @param string $authZQString qualifier for authz checking
	 * @access public
	 * @since 11/3/05
	 */
	function canImportBelow($authZQString) {
		return true;
	}

	/**
	 * Imports the current node's information
	 * 
	 * @access public
	 * @since 10/10/05
	 */
	function importNode () {
		$idManager = Services::getService("Id");
		
		$this->getNodeInfo();

		if (in_array($this->_info['parentId']->getIdString(),
				$this->_existingArray) || ($this->_type == "update")) {
			$this->_myId =$this->_info['fileDataId'];
			$this->_object =$this->_parent->getPart($this->_myId);
				$this->update();
		} else {
			$this->_object =$this->_parent->createPart(
				$this->_info['partStructureId'], 
				file_get_contents($this->_info['value']));
			$this->_myId =$this->_object->getId();
		}
	}

	/**
	 * Sets the node's internal information
	 * 
	 * @access public
	 * @since 10/10/05
	 */
	function getNodeInfo () {
		$idManager = Services::getService("Id");
	
		$path = $this->_node->getText();
		if (!preg_match("#^([a-zA-Z]+://|[a-zA-Z]+:\\|/)#", $path))
			$path = $this->_node->ownerDocument->xmlPath.$path;
		
		$this->_info['value'] = $path;
		
		$this->_info['partStructureId'] =$idManager->getId("THUMBNAIL_DATA");
							
		$this->_info['parentId'] = $this->_parent->getId();
		
		$this->_info['fileDataId'] =$idManager->getId(
			$this->_info['parentId']->getIdString()."-THUMBNAIL_DATA");
	}
	
	/**
	 * Relegates Children to their classes
	 * 
	 * @param object mixed $topImporter will be passed down
	 * @access public
	 * @since 10/10/05
	 */
	function relegateChildren ($topImporter) {
	}
	
	/**
	 * Looks for discrepencies between imported data and current data
	 * 
	 * @access public
	 * @since 10/10/05
	 */
	function update () {
		if (isset($this->_info['value']) && !is_null($this->_info['value']) && (file_get_contents($this->_info['value']) !=
				$this->_object->getValue()))
		$this->_object->updateValue(file_get_contents($this->_info['value']));
	}
}

?>