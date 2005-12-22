<?php
/**
 * @since 10/6/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLPartImporter.class.php,v 1.12 2005/12/22 22:58:03 cws-midd Exp $
 */ 

require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLImporter.class.php");

/**
 * XMLPartImporter imports a part into a record
 * 
 * @since 10/6/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLPartImporter.class.php,v 1.12 2005/12/22 22:58:03 cws-midd Exp $
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
		$query =& new SelectQuery;
		$query->addTable("xml_id_matrix");
		$query->addColumn("conc_id");
		$query->addColumn("xml_id");
		$id =& $this->_node->getAttribute("xml:id");
		$query->addWhere("xml_id = '".addslashes($id)."'");
		
		//$dbHandler->connect($dbIndexConcerto);
		$results =& $dbHandler->query($query, IMPORTER_CONNECTION);
		
		if ($results->getNumberOfRows() == 1) {
			$result =& $results->next();
			$idManager =& Services::getService("Id");
			$this->_info['partStructureId'] =& $idManager->getId(
				$result['conc_id']);
		} else if ($results->getNumberOfRows() > 1) {
			$this->addError("Multiple PartStructure matches: ");
			while ($results->hasNext()) {
				$result =& $results->next();
				$this->addError("\tmatch: ".$result['conc_id']);
			}
			$this->_info['partStructureId'] =& $idManager->getId(
				$result['conc_id']);
		} else 
			$this->addError("Bad XML IDREF: ".$id);
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
		if (!is_null($this->_info['value']) && ($this->_info['value'] != $this->_object->getValue()))
			$this->_object->updateValue($this->_info['value']);
	}
	
	/**
	 * creates appropriate object from given ids
	 * 
	 * @return object mixed
	 * @access public
	 * @since 7/21/05
	 */
	function getPartObject (&$part) {
		$recordStructure =& $this->_parent->getRecordStructure();
		$partStructure =& $recordStructure->getPartStructure(
			$this->_info['partStructureId']);
		$type = $partStructure->getType();
		$typeString = $type->getKeyword();
		switch($typeString) {
			case "string":
				return String::withValue($part);
				break;
			case "integer":
				return Integer::withValue($part);
				break;
			case "boolean":
				return Boolean::withValue($part);
				break;
			case "shortstring":
				return String::withValue($part);
				break;
			case "float":
				return Float::withValue($part);
				break;
			case "datetime":
				return DateAndTime::fromString($part);
				break;
			case "type": 
				return HarmoniType::stringToType($part);
				break;
			default:
				$this->addError("Unsupported PartStructure DataType: ".
					HarmoniType::typeToString($type).".");
				$false = false;
				return $false;
		}
	}
}

?>