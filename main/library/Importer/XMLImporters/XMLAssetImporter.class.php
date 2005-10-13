<?php
/**
 * @since 10/6/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLAssetImporter.class.php,v 1.7 2005/10/13 17:36:51 cws-midd Exp $
 */ 

require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLRecordImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLFileRecordImporter.class.php");
require_once(HARMONI."Primitives/Chronology/DateAndTime.class.php");

/**
 * XMLAssetImporter imports an asset into a repository
 * 
 * @since 10/6/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLAssetImporter.class.php,v 1.7 2005/10/13 17:36:51 cws-midd Exp $
 */
class XMLAssetImporter extends XMLImporter {
		
	/**
	 * 	Constructor; parses XML if passed file
	 * 
	 * 
	 * @return object XMLAssetImporter
	 * @access public
	 * @since 10/6/05
	 */
	function XMLAssetImporter () {
		parent::XMLImporter();
	}

	/**
	 * Constructor with XML File to parse
	 * 
	 * @param string
	 * @param string
	 * @param string
	 * @return object mixed
	 * @access public
	 * @since 10/11/05
	 */
	function &withFile ($filepath, $type, $class = 'XMLAssetImporter') {
		return parent::withFile($filepath, $type, $class);
	}

	/**
	 * Constructor with XMLFile and starting object
	 * 
	 * @param object mixed
	 * @param string
	 * @param string
	 * @param string
	 * @return object mixed
	 * @access public
	 * @since 10/11/05
	 */
	function &withObject (&$object, $filepath, $type, $class = 'XMLAssetImporter') {
		return parent::withObject($object, $filepath, $type, $class);
	}

	/**
	 * Sets up importer's self-knowledge
	 * 
	 * @access public
	 * @since 10/6/05
	 */
	function setupSelf () {
		$this->_childImporterList = array("XMLAssetImporter", 
			"XMLRecordImporter", "XMLFileRecordImporter");
		$this->_childElementList = array("asset", "record", "filerecord");
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
		if ($element->nodeName == "asset")
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
		
		if (!method_exists($this->_parent, "createAsset")) {
			$this->_stepParent =& $this->_parent;
			$this->_parent =& $this->_stepParent->getRepository();
		}
		
		$this->getNodeInfo();
		
		if ($this->_node->hasAttribute("isExisting") && 		
			($this->_node->getAttribute("isExisting") == TRUE)) {
			$this->_myId =& $idManager->getId($this->_node->getAttribute("id"));
			$this->_object =& $this->_parent->getAsset($this->_myId);
		} else if (($this->_type == "insert") || 
			(!$this->_node->hasAttribute("id"))) {
			$this->_object =& $this->_parent->createAsset(
				$this->_info['name'], $this->_info['description'], 
				$this->_info['type']);
			$this->_myId =& $this->_object->getId();
			if (isset($this->_stepParent))
				$this->_stepParent->addAsset($this->_myId);
			if (isset($this->_info['effectivedate']))
				$this->_object->updateEffectiveDate(DateAndTime::fromString(
					$this->_info['effectivedate']));
			if (isset($this->_info['expirationdate']))
				$this->_object->updateExpirationDate(DateAndTime::fromString(
					$this->_info['expirationdate']));
		} else {
			$this->_myId =& $idManager->getId($this->_node->getAttribute("id"));
			$this->_object =& $this->_parent->getAsset($this->_myId);
		}
		if ($this->_type == "update")
				$this->update();	

		if ($this->_node->hasAttribute("maintainOrder") &&
			($this->_node->getAttribute("maintainOrder") == TRUE))
			$this->doSets();
	}

	/**
	 * Does anything needed to sets
	 * 
	 * @access public
	 * @since 10/6/05
	 */
	function doSets () {
		$sets =& Services::getService("Sets");
		$this->_set =& $sets->getPersistentSet($this->_myId);
	}

	/**
	 * Relegates Children to their classes
	 * 
	 * @access public
	 * @since 10/6/05
	 */
	function relegateChildren () {
		foreach ($this->_node->childNodes as $element)
			foreach ($this->_childImporterList as $importer) {
				eval('$result = '.$importer.'::isImportable($element);');
				if ($result) {
					$imp =& new $importer();
					if (isset($this->_set) && $element->nodeName == "asset")
						$this->_set->addItem($imp->import($element,
							$this->_type, $this->_object));
					else
						$imp->import($element, $this->_type, $this->_object);
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
		if ($this->_info['name'] != $this->_object->getDisplayName())
			$this->_object->updateDisplayName($this->_info['name']);
		if ($this->_info['description'] != $this->_object->getDescription())
			$this->_object->updateDescription($this->_info['description']);
		if (isset($this->_info['effectivedate']) && 
			(DateAndTime::fromString($this->_info['effectivedate']) != 
			$this->_object->getEffectiveDate()))
			$this->_object->updateEffectiveDate(DateAndTime::fromString(
				$this->_info['effectivedate']));
		if (isset($this->_info['effectivedate']) && 
			(DateAndTime::fromString($this->_info['expirationdate']) !=
			$this->_object->getExpirationDate()))
			$this->_object->updateExpirationDate(DateAndTime::fromString(
				$this->_info['expirationdate']));
	}
}

?>