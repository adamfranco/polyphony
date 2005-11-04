<?php
/**
 * @since 10/5/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLRepositoryImporter.class.php,v 1.9 2005/11/04 20:33:30 cws-midd Exp $
 */ 

require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLAssetImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLRecordStructureImporter.class.php");

/**
 * XMLRepositoryImporter imports a repository via delegation to subclasses
 * 
 * @since 10/5/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLRepositoryImporter.class.php,v 1.9 2005/11/04 20:33:30 cws-midd Exp $
 */
class XMLRepositoryImporter extends XMLImporter {
		
	/**
	 * 	Constructor
	 * 
	 * 
	 * @return object XMLRepositoryImporter
	 * @access public
	 * @since 10/5/05
	 */
	function XMLRepositoryImporter (&$existingArray) {
		parent::XMLImporter($existingArray);
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
	function &withFile (&$existingArray, $filepath, $type, $class = 'XMLRepositoryImporter') {
		return parent::withFile($existingArray, $filepath, $type, $class);
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
	function &withObject (&$existingArray, &$object, $filepath, $type, $class = 'XMLRepositoryImporter') {
		return parent::withObject($existingArray, $object, $filepath, $type, $class);
	}
	
	/**
	 * Sets up importer's self-knowledge
	 * 
	 * @access public
	 * @since 10/5/05
	 */
	function setupSelf () {
		$this->_childImporterList = array("XMLRecordStructureImporter",
			"XMLAssetImporter");
		$this->_childElementList = array("asset", "recordstructure");
		$this->_info = array();
	}

	/**
	 * Filters nodes of incorrect type
	 * 
	 * @param object DOMIT_Node
	 * @return boolean
	 * @static
	 * @access public
	 * @since 10/5/05
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
	 * @since 10/5/05
	 */
	function importNode () {			
		$idManager =& Services::getService("Id");
		$repositoryManager =& Services::getService("RepositoryManager");
		
		$this->getNodeInfo();
		
		// make/find object
		$hasId = $this->_node->hasAttribute("id");
		if ($hasId && (in_array($this->_node->getAttribute("id"),
				$this->_existingArray)	|| $this->_type == "update")) {
			$this->_myId =& $idManager->getId($this->_node->getAttribute("id"));
			$this->_object =& $repositoryManager->getRepository($this->_myId);
			$this->update();
		} 
		if (!$this->_object) {
			$this->_object =& $repositoryManager->createRepository(
				$this->_info['name'], $this->_info['description'],
				$this->_info['type']);
			$this->_myId =& $this->_object->getId();

			// add FILE record structure to repository
			$this->doSets();
		}
	}

	/**
	 * Does anything needed to sets
	 * 
	 * @access public
	 * @since 10/6/05
	 */
	function doSets () {
		$idManager =& Services::getService("Id");
		$sets =& Services::getService("Sets");
		$set =& $sets->getPersistentSet($this->_myId);
		if (!$set->isInSet($idManager->getId("FILE")))
			$set->addItem($idManager->getId("FILE"));
	}

	/**
	 * Does what is necessary to the temporary table for internal id association
	 * 
	 * @access public
	 * @since 10/6/05
	 */
	function doIdMatrix () {
		$dbHandler =& Services::getService("DBHandler");
// define dbIndexConcerto for children
		$createTableQuery =& new GenericSQLQuery;
		$createTableQuery->addSQLQuery("Create temporary table if not exists
			xml_id_matrix ( xml_id varchar(50)
			not null , conc_id varchar(75) not null)");
		$dbHandler->disconnect(IMPORTER_CONNECTION);
		$dbHandler->connect(IMPORTER_CONNECTION);
		$dbHandler->query($createTableQuery, IMPORTER_CONNECTION);
	}

	/**
	 * Drops the temporary table for internal id association
	 * 
	 * @access public
	 * @since 10/6/05
	 */
	function dropIdMatrix () {
		$dbHandler =& Services::getService("DBHandler");
//		$dropTableQuery =& new GenericSQLQuery;
//		$dropTableQuery->addSQLQuery("Drop table if exists
//			xml_id_matrix");
		
//		$dbHandler->query($dropTableQuery, $dbIndexConcerto);
		$dbHandler->disconnect(IMPORTER_CONNECTION);
		$dbHandler->pconnect(IMPORTER_CONNECTION);
	}	
	
	/**
	 * Looks for discrepencies between imported data and current data
	 * 
	 * @access public
	 * @since10/5/05
	 */
	function update () {
		if ($this->_info['name'] != $this->_object->getDisplayName())
			$this->_object->updateDisplayName($this->_info['name']);
		if ($this->_info['description'] != $this->_object->getDescription())
			$this->_object->updateDescription($this->_info['description']);
		// DATES GO HERE
	}
}

?>