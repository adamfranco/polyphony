<?php
/**
 * @since 9/12/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLAssetImporter.class.php,v 1.3 2005/09/26 17:56:21 cws-midd Exp $
 */ 

require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLRecordImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLFileRecordImporter.class.php");
require_once(HARMONI."oki2/repository/HarmoniRepository.class.php");
require_once(HARMONI."Primitives/Chronology/DateAndTime.class.php");

/**
 * XMLAssetImporter imports an asset into a repository
 * 
 * @since 9/12/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLAssetImporter.class.php,v 1.3 2005/09/26 17:56:21 cws-midd Exp $
 */
class XMLAssetImporter extends XMLImporter {
		
	/**
	 * Constructor
	 * 
	 * @param object DOMIT_Node
	 * @param object HarmoniRepository
	 * @access public
	 * @since 9/12/05
	 */
	function XMLAssetImporter (&$element, $tableName, &$repository, &$parent) {
		$this->_node =& $element;
		$this->_childImporterList = array("XMLAssetImporter", 
			"XMLRecordImporter", "XMLFileRecordImporter");
		$this->_childElementList = array("asset", "record", "filerecord");
		$this->_repository =& $repository;
		$this->_tableName = $tableName;
		Services::getService("Id");
		if (!is_null($parent))
			$this->_parent =& $parent;
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
		if ($element->nodeName == "asset")
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
		
		if (!$this->_node->hasAttribute("id")) {
			$this->_asset =& $this->_repository->createAsset(
				$this->_info['name'], $this->_info['description'], 
				$this->_info['type']);
			if (isset($this->_parent))
				$this->_parent->addAsset($this->_asset->getId());
			if (isset($this->_info['effectivedate']))
				$this->_asset->updateEffectiveDate(DateAndTime::fromString(
					$this->_info['effectivedate']));
			if (isset($this->_info['expirationdate']))
				$this->_asset->updateExpirationDate(DateAndTime::fromString(
					$this->_info['expirationdate']));
		}
		else {
			$idString = $this->_node->getAttribute("id");
			$id =& $idManager->getId($idString);
			$this->_asset =& $this->_repository->getAsset($id);
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
		foreach ($this->_node->childNodes as $element) {
			if (!in_array($element->nodeName, $this->_childElementList)) {
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
		if ($this->_node->hasAttribute("maintainOrder") &&
			($this->_node->getAttribute("maintainOrder") == TRUE)) {
			$setManager =& Services::getService("Sets");
			$this->_set =& $setManager->getPersistentSet(
				$this->_asset->getId());
		}
		foreach ($this->_node->childNodes as $element)
			foreach ($this->_childImporterList as $importer) {
				eval('$result = '.$importer.'::isImportable($element);');
				if ($result) {
					if (strtolower($importer) == get_class($this)) {
						$imp =& new $importer($element, $this->_tableName,
							$this->_repository, $this->_asset);
						$imp->import($this->_type);
						if (isset($this->_set))
							$this->_set->addItem($imp->getAssetId());
					}	
					else {
						$imp =& new $importer($element, $this->_tableName, 
							$this->_asset);
						$imp->import($this->_type);
					}
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
		if ($this->_info['name'] != $this->_asset->getDisplayName())
			$this->_asset->updateDisplayName($this->_info['name']);
		if ($this->_info['description'] != $this->_asset->getDescription())
			$this->_asset->updateDescription($this->_info['description']);
		if (DateAndTime::fromString($this->_info['effectivedate']) != 
			$this->_asset->getEffectiveDate())
			$this->_asset->updateEffectiveDate(DateAndTime::fromString(
				$this->_info['effectivedate']));
		if (DateAndTime::fromString($this->_info['expirationdate']) !=
			$this->_asset->getExpirationDate())
			$this->_asset->updateExpirationDate(DateAndTime::fromString(
				$this->_info['expirationdate']));
	}
	
	/**
	 * Returns the id obj of the object imported by this importer
	 * 
	 * @return object Id
	 * @access public
	 * @since 9/19/05
	 */
	function &getId () {
		return $this->_asset->getId();
	}
}

?>