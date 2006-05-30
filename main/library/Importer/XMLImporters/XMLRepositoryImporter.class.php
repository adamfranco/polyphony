<?php
/**
 * @since 10/5/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLRepositoryImporter.class.php,v 1.15 2006/05/30 20:18:45 adamfranco Exp $
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
 * @version $Id: XMLRepositoryImporter.class.php,v 1.15 2006/05/30 20:18:45 adamfranco Exp $
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
	 * This function determines the structure wanted and makes sure it is so
	 * 
	 * sub-classes that can start an import should overwrite this function
	 * @access public
	 * @since 2/23/06
	 */
	function _checkXMLStructure () {
		return ($this->_import->documentElement->nodeName == "repository");
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
//printpre($this->_node->nodeName);
		// make/find object
		$hasId = $this->_node->hasAttribute("id");
// printpre($hasId."::".$this->_node->getAttribute("id")."::".$this->_existingArray
// ."::".$this->type);
		
		if ($hasId && (in_array($this->_node->getAttribute("id"),
				$this->_existingArray)	|| $this->_type == "update")) {
			$this->_myId =& $idManager->getId($this->_node->getAttribute("id"));
			$this->_object =& $repositoryManager->getRepository($this->_myId);
			$this->update();
		} 
		else {
			$this->_object =& $repositoryManager->createRepository(
				$this->_info['name'], $this->_info['description'],
				$this->_info['type']);
			$this->_myId =& $this->_object->getId();
			// log repository creation
			if (Services::serviceRunning("Logging")) {
				$loggingManager =& Services::getService("Logging");
				$log =& $loggingManager->getLogForWriting("Harmoni");
				$formatType =& new Type("logging", "edu.middlebury", "AgentsAndNodes",
								"A format in which the acting Agent[s] and the target nodes affected are specified.");
				$priorityType =& new Type("logging", "edu.middlebury",
					"Event_Notice",	"Normal events.");
				$item =& new AgentNodeEntryItem("Create Node",
					"Repository: ".$this->_myId->getIdString()." created.");
				$item->addNodeId($this->_myId);
				$log->appendLogWithTypes($item, $formatType, $priorityType);
			}
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
			xml_id_matrix ( xml_id varchar(255)
			not null , conc_id varchar(255) not null)");
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
		$modified = false;
		if (isset($this->_info['name']) && !is_null($this->_info['name']) && 
 		 ($this->_info['name'] != $this->_object->getDisplayName())) {
			$modified = true;
			$this->_object->updateDisplayName($this->_info['name']);
		}
		if (isset($this->_info['description']) &&
		 !is_null($this->_info['description']) && 
		 ($this->_info['description'] != $this->_object->getDescription())) {
			$modified = true;
			$this->_object->updateDescription($this->_info['description']);
		}
		if (Services::serviceRunning("Logging") && $modified) {
			$loggingManager =& Services::getService("Logging");
			$log =& $loggingManager->getLogForWriting("Harmoni");
			$formatType =& new Type("logging", "edu.middlebury", 
				"AgentsAndNodes",
				"A format in which the acting Agent[s] and the target nodes affected are specified.");
			$priorityType =& new Type("logging", "edu.middlebury",
				"Event_Notice",	"Normal events.");
			$item =& new AgentNodeEntryItem("Modify Node",
				"Repository: ".$this->_myId->getIdString()." modified.");
			$item->addNodeId($this->_myId);
			$log->appendLogWithTypes($item, $formatType, $priorityType);
		}
		// DATES GO HERE
	}
}

?>