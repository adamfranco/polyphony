<?php
/**
 * @since 10/6/05
 * @package polyphony.library.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLPartImporter.class.php,v 1.21 2006/06/26 19:22:41 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLImporter.class.php");

/**
 * XMLPartImporter imports a part into a record
 * 
 * @since 10/6/05
 * @package polyphony.library.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLPartImporter.class.php,v 1.21 2006/06/26 19:22:41 adamfranco Exp $
 */
class XMLPartImporter extends XMLImporter {
		
	/**
	 * 	Constructor
	 * 
	 * 
	 * @return object XMLRepositoryImporter
	 * @access public
	 * @since 10/6/05
	 */
	function XMLPartImporter (&$existingArray) {
		parent::XMLImporter($existingArray);
	}
	
	/**
	 * Sets up importer's self-knowledge
	 * 
	 * @access public
	 * @since 10/6/05
	 */
	function setupSelf () {
		$this->_childImporterList = array("XMLPartImporter");
		$this->_childElementList = array("part");
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
		if ($element->nodeName == "part")
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
	 * @since 10/6/05
	 */
	function importNode () {
		$idManager =& Services::getService("Id");
		
		$this->getNodeInfo();
		
		$hasId = $this->_node->hasAttribute("id");
		if ($hasId && (in_array($this->_node->getAttribute("id"),
				$this->_existingArray)	|| $this->_type == "update")) {
			$this->_myId =& $idManager->getId($this->_node->getAttribute("id"));
			$this->_object =& $this->_parent->getPart($this->_myId);
			$this->update();
		} else if (is_null($this->_info['value'])) {
			return;
		} else {
			$this->_object =& $this->_parent->createPart(
				$this->_info['partStructureId'], $this->_info['value']);
			$this->_myId =& $this->_object->getId();
		}
	}

	/**
	 * Sets the node's internal information
	 * 
	 * @access public
	 * @since 10/6/05
	 */
	function getNodeInfo () {
		$dbHandler =& Services::getService("DBHandler");
//		$dbIndexConcerto =& $dbHandler->addDatabase(new 
//			MySQLDatabase("localhost", "whitey_concerto", "test", "test"));
		if (Services::serviceRunning("Logging")) {
			$loggingManager =& Services::getService("Logging");
			$log =& $loggingManager->getLogForWriting("Harmoni");
			$formatType =& new Type("logging", "edu.middlebury",
				"AgentsAndNodes",
				"A format in which the acting Agent[s] and the target nodes affected are specified.");
			$priorityType =& new Type("logging", "edu.middlebury", "Error",
				"Events involving critical system errors.");
		}
		$query =& new SelectQuery;
		$query->addTable("xml_id_matrix");
		$query->addColumn("conc_id");
		$query->addColumn("xml_id");
		$id = $this->_node->getAttribute("xml:id");
		$query->addWhere("xml_id = '".addslashes($id)."'");
		
		//$dbHandler->connect($dbIndexConcerto);
		$results =& $dbHandler->query($query, IMPORTER_CONNECTION);
		
		if ($results->getNumberOfRows() == 1) {
			$result = $results->next();
			$idManager =& Services::getService("Id");
			$this->_info['partStructureId'] =& $idManager->getId(
				$result['conc_id']);
		} else if ($results->getNumberOfRows() > 1) {
			$this->addError("Multiple PartStructure matches for $result[xml_id]: ");
			if (isset($log))
				$string = "Multiple PartStructure matches for $result[xml_id]:";
			while ($results->hasNext()) {
				$result =& $results->next();
				$this->addError("\tmatch: ".$result['conc_id']);
				// add matches
				if (isset($log))
					$string .= " Match: $result[conc_id]<br/>";
			}
			if (isset($log)) {
				$item =& new AgentNodeEntryItem("PartImporter Error",
					$string);				
				$log->appendLogWithTypes($item,	$formatType, $priorityType);
			}
			$this->_info['partStructureId'] =& $idManager->getId(
				$result['conc_id']);
		} else {
			$this->addError("Bad XML IDREF: ".$id);
			// log error
			if (isset($log)) {
				$item =& new AgentNodeEntryItem("PartImport Error", 
					"Bad XML IDREF: $id");
				$log->appendLogWithTypes($item, $formatType, $priorityType);
			}
		}
		$results->free();

		$this->_info['value'] = $this->getPartObject($this->_node->getText());
	}
	
	/**
	 * Looks for discrepencies between imported data and current data
	 * 
	 * @access public
	 * @since 10/6/05
	 */
	function update () {
		if (isset($this->_info['value']) && !is_null($this->_info['value']) &&
		 ($this->_info['value'] != $this->_object->getValue()))
			$this->_object->updateValue($this->_info['value']);
	}
	
	/**
	 * creates appropriate object from given ids
	 * 
	 * @return object mixed
	 * @access public
	 * @since 7/21/05
	 */
	function &getPartObject ($part) {
		$dtm =& Services::getService("DataTypeManager");
		
		$recordStructure =& $this->_parent->getRecordStructure();
		$partStructure =& $recordStructure->getPartStructure(
			$this->_info['partStructureId']);
		$type = $partStructure->getType();
		$class = $dtm->primitiveClassForType($type->getKeyword());
		eval('$object =& '.$class.'::fromString($part);');
		
		if (!is_object($object)) {
			$this->addError("Unsupported PartStructure DataType: ".
						HarmoniType::typeToString($type).".");
			// Log error
			if (Services::serviceRunning("Logging")) {
				$loggingManager =& Services::getService("Logging");
				$log =& $loggingManager->getLogForWriting("Harmoni");
				$formatType =& new Type("logging", "edu.middlebury", "AgentsAndNodes",
								"A format in which the acting Agent[s] and the target nodes affected are specified.");
				$priorityType =& new Type("logging", "edu.middlebury", "Error",
								"Events involving critical system errors.");
				$item =& new AgentNodeEntryItem("PartImport Error",
					"Unsupported PartStructure DataType: ".
					HarmoniType::typeToString($type));
				$log->appendLogWithTypes($item, $formatType, $priorityType);
			}
			$false = false;
			return $false;
		}
		return $object;
	}
}

?>