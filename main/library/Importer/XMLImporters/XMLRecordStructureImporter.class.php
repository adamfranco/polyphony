<?php
/**
 * @since 9/8/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLRecordStructureImporter.class.php,v 1.3 2005/09/26 17:56:22 cws-midd Exp $
 */ 

require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLPartStructureImporter.class.php");

/**
 * XMLRecordStructureImporter imports a RecordStructure via delegation to 
 * subclasses
 * 
 * @since 9/8/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLRecordStructureImporter.class.php,v 1.3 2005/09/26 17:56:22 cws-midd Exp $
 */
class XMLRecordStructureImporter extends XMLImporter {
		
	/**
	 * Constructor
	 * 
	 * @param object DOMIT_Node
	 * @access public
	 * @since 9/9/05
	 */
	function XMLRecordStructureImporter (&$element, $tableName, &$repository) {
		$this->_node =& $element;
		$this->_childImporterList = array("XMLPartStructureImporter");
		$this->_childElementList = array("partstructure");
		$this->_repository =& $repository;
		$this->_tableName = $tableName;
	}

	/**
	 * Filters nodes of incorrect type
	 * 
	 * @param object DOMIT_Node
	 * @return boolean
	 * @static
	 * @access public
	 * @since 9/9/05
	 */
	function isImportable (&$element) {
		if($element->nodeName == "recordstructure")
			return true;
		else
			return false;
	}
	
	/**
	 * Checks this node for any changes to make to this
	 * 
	 * @access public
	 * @since 9/9/05
	 */
	function importNode () {
		$idManager =& Services::getService("IdManager");
		
		$this->getNodeInfo();
		
		if (!$this->_node->hasAttribute("id"))
			$this->_recordStructure =&
				$this->_repository->createRecordStructure($this->_info['name'], 
				$this->_info['description'], $this->_info['format'],"");
		else {
			$idString = $this->_node->getAttribute("id");
			$id =& $idManager->getId($idString);
			$this->_recordStructure =& $this->_repository->getRecordStructure(
				$id);
			if ($this->_type == "update")
				$this->update();
		}
		$repId =& $this->_repository->getId();
		$idString = $repId->getIdString();
		$dbHandler =& Services::getService("DBHandler");
		$dbIndexConcerto =& $dbHandler->addDatabase(new 
			MySQLDatabase("localhost", "whitey_concerto", "test", "test"));
		$query =& new InsertQuery;
		$query->setTable($this->_tableName);
		$query->setColumns(array("xml_id", "conc_id"));
		$xmlid = $this->_node->getAttribute("xml:id");
		$id =& $this->_recordStructure->getId();
		$query->addRowOfValues(array("'".addslashes($xmlid)."'", "'".addslashes(
			$id->getIdString())."'"));
		
		$dbHandler->connect($dbIndexConcerto);
		$dbHandler->query($query, $dbIndexConcerto);
		$dbHandler->disconnect($dbIndexConcerto);
		
		$sets =& Services::getService("Sets");		
		$set =& $sets->getPersistentSet($repId);
		if (!$set->isInSet($this->_recordStructure->getId()))
			$set->addItem($this->_recordStructure->getId());
	}
	
	/**
	 * sets the node's info
	 * 
	 * @access public
	 * @since 9/9/05
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
	 * @since 9/9/05
	 */
	function relegateChildren () {				
		foreach ($this->_node->childNodes as $element)
			foreach ($this->_childImporterList as $importer) {
				eval('$result = '.$importer.'::isImportable($element);');
				if ($result) {
					$imp =& new $importer($element, $this->_tableName,
						$this->_recordStructure, $this->_repository);
					$imp->import($this->_type);
					unset($imp);
				}
			}
	}
	
	/**
	 * Looks for discrepencies between imported data and current data
	 * 
	 * @access public
	 * @since 9/9/05
	 */
	function update () {
		if ($this->_info['name'] != $this->_recordStructure->getDisplayName())
			$this->_recordStructure->updateDisplayName($this->_info['name']);
	}
}

?>