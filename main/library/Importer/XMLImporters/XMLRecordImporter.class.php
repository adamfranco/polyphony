<?php
/**
 * @since 10/6/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLRecordImporter.class.php,v 1.10 2006/02/22 21:46:40 cws-midd Exp $
 */ 

require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLPartImporter.class.php");

/**
 * XMLRecordImporter imports an record into an asset
 * 
 * @since 10/6/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLRecordImporter.class.php,v 1.10 2006/02/22 21:46:40 cws-midd Exp $
 */
class XMLRecordImporter extends XMLImporter {
		
	/**
	 * 	Constructor; parses XML if passed file
	 * 
	 * 
	 * @return object XMLRecordImporter
	 * @access public
	 * @since 10/6/05
	 */
	function XMLRecordImporter (&$existingArray) {
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
		$this->_childElementLIst = array("part");
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
		if ($element->nodeName == "record")
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
			$this->_object =& $this->_parent->getRecord($this->_myId);
		} else /*insert*/ {
			$this->_object =& $this->_parent->createRecord(
				$this->_info['recordStructureId']);
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
// 		$dbIndexConcerto =& $dbHandler->addDatabase(new 
// 			MySQLDatabase("localhost", "whitey_concerto", "test", "test"));
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
			$this->_info['recordStructureId'] =& $idManager->getId(
				$result['conc_id']);
		} else {
			while ($results->hasNext()) {
				$row =& $results->next();
				printpre($row);
			}
//			throwError(new Error("table", "bad", TRUE));
			$this->addError("Bad XML IDREF: ".$id);
		}
		$results->free();
	}
}

?>