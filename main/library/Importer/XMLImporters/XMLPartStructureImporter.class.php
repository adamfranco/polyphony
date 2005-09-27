<?php
/**
 * @since 9/13/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLPartStructureImporter.class.php,v 1.4 2005/09/27 19:35:48 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLImporter.class.php");

/**
 * XMLPartStructureImporter imports a PartStructure via delegation to 
 * subclasses
 * 
 * @since 9/13/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLPartStructureImporter.class.php,v 1.4 2005/09/27 19:35:48 adamfranco Exp $
 */
class XMLPartStructureImporter extends XMLImporter {
		
	/**
	 * Constructor
	 * 
	 * @param object DOMIT_Node
	 * @access public
	 * @since 9/13/05
	 */
	function XMLPartStructureImporter (&$element, $tableName, &$recordStructure, &$repository) {
		$this->_node =& $element;
		$this->_childImporterList = NULL;
		$this->_chlidElementList = NULL;
		$this->_recordStructure =& $recordStructure;
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
	 * @since 9/13/05
	 */
	function isImportable (&$element) {
		if($element->nodeName == "partstructure")
			return true;
		else
			return false;
	}
	
	/**
	 * Checks this node for any changes to make to this
	 * 
	 * @access public
	 * @since 9/13/05
	 */
	function importNode () {
		$idManager =& Services::getService("IdManager");
		
		$this->getNodeInfo();
		
		if (!$this->_node->hasAttribute("id"))
			$this->_partStructure =&
				$this->_recordStructure->createPartStructure(
				$this->_info['name'], $this->_info['description'],
				$this->_info['type'], 
				(($this->_info['isMandatory'] == "TRUE")?true:false),
				(($this->_info['isRepeatable'] == "TRUE")?true:false), 
				(($this->_info['isPopulated'] == "TRUE")?true:false));
		else {
			$idString = $this->_node->getAttribute("id");
			$id =& $idManager->getId($idString);
			$this->_partStructure =&
				$this->_recordStructure->getPartStructure($id);
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
		$id =& $this->_partStructure->getId();
		$query->addRowOfValues(array("'".addslashes($xmlid)."'", "'".addslashes(
			$id->getIdString())."'"));
		
		$dbHandler->connect($dbIndexConcerto);
		$dbHandler->query($query, $dbIndexConcerto);
		$dbHandler->disconnect($dbIndexConcerto);
	}
	
	/**
	 * sets the node's info
	 * 
	 * @access public
	 * @since 9/13/05
	 */
	function getNodeInfo () {
		foreach ($this->_node->childNodes as $element) {
			$helper = "build".ucfirst($element->nodeName);
			$this->$helper($element);
		}
		if ($this->_node->hasAttribute("isMandatory"))
			$this->_info['isMandatory'] = $this->_node->getAttribute(
				"isMandatory");
		else $this->_info['isMandatory'] = FALSE;
		if ($this->_node->hasAttribute("isRepeatable"))
			$this->_info['isRepeatable'] = $this->_node->getAttribute(
				"isRepeatable");
		else $this->_info['isRepeatable'] = FALSE;
		if ($this->_node->hasAttribute("isPopulated"))
			$this->_info['isPopulated'] = $this->_node->getAttribute(
				"isPopulated");
		else $this->_info['isPopulated'] = FALSE;
	}

	/**
	 * Relegates Children to their classes
	 * 
	 * @access public
	 * @since 9/13/05
	 */
	function relegateChildren () {				
		foreach ($this->_node->childNodes as $element)
			foreach ($this->_childImporterList as $importer) {
				eval('$result = '.$importer.'::isImportable($element);');
				if ($result) {
					$imp =& new $importer($element, $this->_recordStructure);
					$imp->import($this->_type);
					unset($imp);
				}
			}
	}
	
	/**
	 * Looks for discrepencies between imported data and current data
	 * 
	 * @access public
	 * @since 9/13/05
	 */
	function update () {
		if ($this->_info['name'] != $this->_recordStructure->getDisplayName())
			$this->_recordStructure->updateDisplayName($this->_info['name']);
	}
}

?>