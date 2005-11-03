<?php
/**
 * @since 10/6/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLPartStructureImporter.class.php,v 1.9 2005/11/03 21:13:15 cws-midd Exp $
 */ 

require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLImporter.class.php");

/**
 * XMLPartStructureImporter imports a PartStructure via delegation to 
 * subclasses
 * 
 * @since 10/6/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLPartStructureImporter.class.php,v 1.9 2005/11/03 21:13:15 cws-midd Exp $
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
		
		if ($this->_node->hasAttribute("id") && 
			in_array($this->_node->getAttribute("id"), $this->_existingArray)) {
			$this->_myId =& $idManager->getId($this->_node->getAttribute("id"));
			$this->_object =& $this->_parent->getPartStructure($this->_myId);
		} else if (($this->_type == "insert") || 
			(!$this->_node->hasAttribute("id"))) {
			$this->_object =&
				$this->_parent->createPartStructure(
				$this->_info['name'], $this->_info['description'],
				$this->_info['type'], 
				(($this->_info['isMandatory'] == "TRUE")?true:false),
				(($this->_info['isRepeatable'] == "TRUE")?true:false), 
				(($this->_info['isPopulated'] == "TRUE")?true:false));
			$this->_myId =& $this->_object->getId();
		} else {
			$this->_myId =& $idManager->getId($this->_node->getAttribute("id"));
			$this->_object =&
				$this->_parent->getPartStructure($this->_myId);
		}
		if ($this->_type == "update")
			$this->update();
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
		if ($this->_info['name'] != $this->_object->getDisplayName())
			$this->_object->updateDisplayName($this->_info['name']);
	}
}

?>