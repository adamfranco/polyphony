<?php
/**
 * @since 9/8/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLRepositoryImporter.class.php,v 1.3 2005/09/26 17:56:22 cws-midd Exp $
 */ 

require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLAssetImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLRecordStructureImporter.class.php");

/**
 * XMLRepositoryImporter imports a repository via delegation to subclasses
 * 
 * @since 9/8/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLRepositoryImporter.class.php,v 1.3 2005/09/26 17:56:22 cws-midd Exp $
 */
class XMLRepositoryImporter extends XMLImporter {
		
	/**
	 * Constructor
	 * 
	 * @param object DOMIT_Node
	 * @access public
	 * @since 9/9/05
	 */
	function XMLRepositoryImporter (&$element, $tableName) {
		$this->_node =& $element;
		$this->_childImporterList = array("XMLRecordStructureImporter",
			"XMLAssetImporter");
		$this->_childElementList = array("asset", "recordstructure");
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
		if($element->nodeName == "repository")
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
		$idManager =& Services::getService("Id");
		$repositoryManager =& Services::getService("RepositoryManager");
		
		$this->getNodeInfo();
		
		if (!$this->_node->hasAttribute("id"))
			$this->_repository =& $repositoryManager->createRepository(
				$this->_info['name'], $this->_info['description'],
				$this->_info['type']);
		else {
			$idString = $this->_node->getAttribute("id");
			$id =& $idManager->getId($idString);
			$this->_repository =& $repositoryManager->getRepository($id);
			if ($this->_type == "update")
				$this->update();
		}
		$id =& $this->_repository->getId();
		$idString = $id->getIdString();
		$dbHandler =& Services::getService("DBHandler");
		$dbIndexConcerto =& $dbHandler->addDatabase(new 
			MySQLDatabase("localhost", "whitey_concerto", "test", "test"));
		$createTableQuery =& new GenericSQLQuery;
		$createTableQuery->addSQLQuery("Create table if not exists
			".$this->_tableName." ( xml_id varchar(50)
			not null , conc_id varchar(75) not null)");
		
		$dbHandler->connect($dbIndexConcerto);
		$dbHandler->query($createTableQuery, $dbIndexConcerto);
		$dbHandler->disconnect($dbIndexConcerto);
		
		$sets =& Services::getService("Sets");
		$set =& $sets->getPersistentSet($this->_repository->getId());
		$set->addItem($idManager->getId("FILE"));
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
					if ($importer == "XMLAssetImporter") {
						$null = null;
						$imp =& new $importer($element, $this->_tableName,
							$this->_repository, $null);
					}
					else
						$imp =& new $importer($element, $this->_tableName,
							$this->_repository);
					$imp->import($this->_type);
					unset($imp);
				}
			}
		$dbHandler =& Services::getService("DBHandler");
		$dbIndexConcerto =& $dbHandler->addDatabase(new 
			MySQLDatabase("localhost", "whitey_concerto", "test", "test"));
		$createTableQuery =& new GenericSQLQuery;
		$createTableQuery->addSQLQuery("Drop table if exists
			".$this->_tableName);
		
		$dbHandler->connect($dbIndexConcerto);
		$dbHandler->query($createTableQuery, $dbIndexConcerto);
		$dbHandler->disconnect($dbIndexConcerto);
	}
	
	
	/**
	 * Looks for discrepencies between imported data and current data
	 * 
	 * @access public
	 * @since 9/9/05
	 */
	function update () {
		if ($this->_info['name'] != $this->_repository->getDisplayName())
			$this->_repository->updateDisplayName($this->_info['name']);
		if ($this->_info['description'] != $this->_repository->getDescription())
			$this->_repository->updateDescription($this->_info['description']);
		// DATES GO HERE
	}
}

?>