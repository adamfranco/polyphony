<?php
/**
 * @since 10/6/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLRecordStructureImporter.class.php,v 1.9 2005/11/04 20:33:30 cws-midd Exp $
 */ 

require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLPartStructureImporter.class.php");

/**
 * XMLRecordStructureImporter imports a RecordStructure via delegation to 
 * subclasses
 * 
 * @since 10/6/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLRecordStructureImporter.class.php,v 1.9 2005/11/04 20:33:30 cws-midd Exp $
 */
class XMLRecordStructureImporter extends XMLImporter {
		
	/**
	 * 	Constructor; parses XML if passed file
	 * 
	 * 
	 * @return object XMLRecordStructureImporter
	 * @access public
	 * @since 10/6/05
	 */
	function XMLRecordStructureImporter (&$existingArray) {
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
	function &withFile (&$existingArray, $filepath, $type, $class = 'XMLRecordStructureImporter') {
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
	function &withObject (&$existingArray, &$object, $filepath, $type, $class = 'XMLRecordStructureImporter') {
		return parent::withObject($existingArray, $object, $filepath, $type, $class);
	}

	/**
	 * Sets up importer's self-knowledge
	 * 
	 * @access public
	 * @since 10/6/05
	 */
	function setupSelf () {
		$this->_childImporterList = array("XMLPartStructureImporter");
		$this->_childElementList = array("partstructure");
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
		if($element->nodeName == "recordstructure")
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
		
		// make/find object
		$hasId = $this->_node->hasAttribute("id");
		if ($hasId && (in_array($this->_node->getAttribute("id"),
				$this->_existingArray)	|| $this->_type == "update")) {
			$this->_myId =& $idManager->getId($this->_node->getAttribute("id"));
			$this->_object =& $this->_parent->getRecordStructure($this->_myId);
			$this->update();
		} else {
			if ($this->_node->hasAttribute("isGlobal") && 
					($this->_node->getAttribute("isGlabal") == "TRUE"))
				$this->_object =&
					$this->_parent->createRecordStructure($this->_info['name'], 
					$this->_info['description'], $this->_info['format'],"",
					true);
			else
				$this->_object =& $this->_parent->createRecordStructure(
					$this->_info['name'], $this->_info['description'], 
					$this->_info['format']);
			$this->_myId = $this->_object->getId();
		}
		// add structure to repository
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
		$set =& $sets->getPersistentSet($this->_parent->getId());
		if (!$set->isInSet($this->_myId))
			$set->addItem($this->_myId);
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