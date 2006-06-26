<?php
/**
 * @since 10/6/05
 * @package polyphony.library.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLPartStructureImporter.class.php,v 1.20 2006/06/26 19:22:41 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLImporter.class.php");

/**
 * XMLPartStructureImporter imports a PartStructure via delegation to 
 * subclasses
 * 
 * @since 10/6/05
 * @package polyphony.library.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLPartStructureImporter.class.php,v 1.20 2006/06/26 19:22:41 adamfranco Exp $
 */
class XMLPartStructureImporter extends XMLImporter {
		
	/**
	 * 	Constructor; parses XML if passed file
	 * 
	 * 
	 * @return object XMLPartStructureImporter
	 * @access public
	 * @since 10/6/05
	 */
	function XMLPartStructureImporter (&$existingArray) {
		parent::XMLImporter($existingArray);
	}

	/**
	 * Sets up importer's self-knowledge
	 * 
	 * @access public
	 * @since 10/6/05
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
	 * @since 10/6/05
	 */
	function isImportable (&$element) {
		if($element->nodeName == "partstructure")
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
	 * Checks this node for any changes to make to this
	 * 
	 * @access public
	 * @since 10/6/05
	 */
	function importNode () {
		$idManager =& Services::getService("IdManager");
		
		$this->getNodeInfo();

		$hasId = $this->_node->hasAttribute("id");
		if ($hasId && (in_array($this->_node->getAttribute("id"),
				$this->_existingArray)	|| $this->_type == "update")) {
			$this->_myId =& $idManager->getId($this->_node->getAttribute("id"));
			$this->_object =& $this->_parent->getPartStructure($this->_myId);
			$this->update();
		} else if ($this->validate($this->_info['type'])) {
			$this->_object =&
				$this->_parent->createPartStructure(
				$this->_info['name'], $this->_info['description'],
				$this->_info['type'], 
				(($this->_info['isMandatory'] == "TRUE")?true:false),
				(($this->_info['isRepeatable'] == "TRUE")?true:false), 
				(($this->_info['isPopulated'] == "TRUE")?true:false));
			$this->_myId =& $this->_object->getId();
		}
		else {
			$this->addError("bad PartStructure data Type");
			if (Services::serviceRunning("Logging")) {
				$loggingManager =& Services::getService("Logging");
				$log =& $loggingManager->getLogForWriting("Harmoni");
				$formatType =& new Type("logging", "edu.middlebury", "AgentsAndNodes",
								"A format in which the acting Agent[s] and the target nodes affected are specified.");
				$priorityType =& new Type("logging", "edu.middlebury", "Error",
								"Events involving critical system errors.");
				
				$item =& new AgentNodeEntryItem("PartStructure Importer", "Bad PartStructure DataType: ".$this->_info['type']->getKeyword()." undefined");				
				$log->appendLogWithTypes($item,	$formatType, $priorityType);
			}
		}
	}
	
	/**
	 *	Makes sure partstructures are of a valid type by checking them
	 *
	 * @param string $type the type for the partstructure that wants to be imported
	 * @return boolean
	 * @since 12/31/05
	 */
	 function validate($type) {
		// get a set of valid types from the DM
		$dm =& Services::getService("DataTypeManager");
		$validTypes = $dm->getRegisteredTypes();
		if (in_array($type->getKeyword(), $validTypes))
		 	return true;
		else
			return false;
	 }
	
	/**
	 * Does what is necessary to the temporary table for internal id association
	 * 
	 * @access public
	 * @since 10/6/05
	 */
	function doIdMatrix () {
		$dbHandler =& Services::getService("DBHandler");
// 		$dbIndexConcerto =& $dbHandler->addDatabase(new 
// 			MySQLDatabase("localhost", "whitey_concerto", "test", "test"));
		$query =& new InsertQuery;
		$query->setTable("xml_id_matrix");
		$query->setColumns(array("xml_id", "conc_id"));
		$xmlid = $this->_node->getAttribute("xml:id");
		$query->addRowOfValues(array("'".addslashes($xmlid)."'", "'".addslashes(
			$this->_myId->getIdString())."'"));
		
		//$dbHandler->connect($dbIndexConcerto);
		$dbHandler->query($query, IMPORTER_CONNECTION);
	}
	
	/**
	 * sets the node's info
	 * 
	 * @access public
	 * @since 10/6/05
	 */
	function getNodeInfo () {
		parent::getNodeInfo();
		
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
	 * Looks for discrepencies between imported data and current data
	 * 
	 * @access public
	 * @since 10/6/05
	 */
	function update () {
		if (isset($this->_info['name']) && !is_null($this->_info['name']) && ($this->_info['name'] != $this->_object->getDisplayName()))
			$this->_object->updateDisplayName($this->_info['name']);
	}
}

?>