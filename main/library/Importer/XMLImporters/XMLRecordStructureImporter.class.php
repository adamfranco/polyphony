<?php
/**
 * @since 10/6/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLRecordStructureImporter.class.php,v 1.24 2007/10/16 21:13:12 adamfranco Exp $
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
 * @version $Id: XMLRecordStructureImporter.class.php,v 1.24 2007/10/16 21:13:12 adamfranco Exp $
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
	function __construct ($existingArray) {
		parent::__construct($existingArray);
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
	 * @static
	 */
	public static function withFile ($existingArray, $filepath, $type, $class = 'XMLRecordStructureImporter') {
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
	 * @static
	 */
	public static function withObject ($existingArray, $object, $filepath, $type, $class = 'XMLRecordStructureImporter') {
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
	static function isImportable ($element) {
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
		$idManager = Services::getService("IdManager");
		
		$this->getNodeInfo();
				
		// make/find object
		$foundId = $this->RSExists();
		if ($foundId != false) {
			$this->_myId =$foundId;
			$this->_object =$this->_parent->getRecordStructure($this->_myId);
			if ($this->_type == "update")
				$this->update();
		} else if ($this->_node->hasAttribute("isGlobal") && 
					($this->_node->getAttribute("isGlobal") == "TRUE")) {
			
			// If we an id specified for this global Record Structure make use
			// it. This is to allow for pre-defined ids of important RecordStructures
			// like Dublin Core
			if ($this->_node->hasAttribute("id") && $this->_node->getAttribute("id")) {
				$id = $idManager->getId($this->_node->getAttribute("id"));
			} else 
				$id = null;
			
			$this->_object =
				$this->_parent->createRecordStructure(
				$this->_info['name'], $this->_info['description'],
				$this->_info['format'], "", $id, true);
			$this->_myId = $this->_object->getId();
		} else {
			$this->_object =$this->_parent->createRecordStructure(
				$this->_info['name'], $this->_info['description'], 
				$this->_info['format'], "");
			$this->_myId = $this->_object->getId();
		}
		// add structure to repository
		$this->doSets();
		
		if ($foundId) {
			$this->doIdMatrix();
			$this->doChildIdMatrices();
			return true;
		}
	}

	/**
	 * Does anything needed to sets
	 * 
	 * @access public
	 * @since 10/6/05
	 */
	function doSets () {
		$sets = Services::getService("Sets");
		$idManager = Services::getService("IdManager");
		if ($this->_myId->isEqual($idManager->getId(
				"edu.middlebury.harmoni.repository.asset_content")))
			return;
		$set =$sets->getPersistentSet($this->_parent->getId());
		if (!$set->isInSet($this->_myId))
			$set->addItem($this->_myId);
	}

	/**
	 * Does what is necessary to the temporary table for internal id association
	 * 
	 * @access public
	 * @since 10/6/05
	 */
	function doChildIdMatrices () {
		$imp = new XMLPartStructureImporter($this->_existingArray);
		foreach ($this->_node->childNodes as $child) {
			if ($child->nodeName == "partstructure") {
				$imp->_node =$child;
				$imp->_myId = $this->matchPartStructure($this->_object,
					$child);
				$imp->doIdMatrix();
			}
		}
		unset($imp);
	}
	
	/**
  	 * Matches a partstructure from a recordstructure
 	 * 
 	 * @return false if not matched and an array of partstructure ids
 	 * @access public
 	 * @since 7/18/05
 	 */
	function matchPartStructure ($rS, $partElement) {
		$partStructures =$rS->getPartStructures(); // get all ps's
		$dname = '';
		$type = '';
		foreach ($partElement->childNodes as $partPiece) {
			if ($partPiece->nodeName == "name")
				$dname = $partPiece->getText();
			else if ($partPiece->nodeName == "type") {
				foreach ($partPiece->childNodes as $typePart) {
					if($typePart->nodeName == "keyword") {
					 	$type = $typePart->getText();
					 }
				}
			}
		}
		while ($partStructures->hasNext()) {
			$partStructure = $partStructures->next();
			$pType =$partStructure->getType();
			if ($dname == $partStructure->getDisplayName() && 
					$type == $pType->getKeyword()) {
				return $partStructure->getId();
			}
		}
	}

	/**
	 * Does what is necessary to the child table for internal id association
	 * 
	 * @access public
	 * @since 10/6/05
	 */
	function doIdMatrix () {
		$dbHandler = Services::getService("DBHandler");
// 		$dbIndexConcerto =$dbHandler->addDatabase(new 
// 			MySQLDatabase("localhost", "whitey_concerto", "test", "test"));
		$query = new InsertQuery;
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
		if (isset($this->_info['name']) && !is_null($this->_info['name']) && ($this->_info['name'] != $this->_object->getDisplayName()))
			$this->_object->updateDisplayName($this->_info['name']);
	}
	
   /**
    * Attempts to find the an identical pre-existing global recordStructure
    *
    * @return object HarmoniId 
    */
	function RSExists() {
		$rS = array();
		$recordStructures =$this->_parent->getRecordStructures();
	// ===== CREATE ARRAY OF RS ESSENTIALS FOR MATCHING ===== //
		foreach ($this->_node->childNodes as $child) {
			if ($child->nodeName == "name")
				$rS['displayName'] = $child->getText();
			else if ($child->nodeName == "partstructure") {
				$pS = array();
				foreach ($child->childNodes as $gchild) {
					if ($gchild->nodeName == "name")
						$pS['name'] = $gchild->getText();
					else if ($gchild->nodeName == "type")
						foreach ($gchild->childNodes as $ggchild)
							if ($ggchild->nodeName == "keyword")
								$pS['type'] = $ggchild->getText();
				}
				$rS[] = $pS;
			}
		}
		$found = FALSE;
		while ($recordStructures->hasNext() && !$found) {
			$rStruct =$recordStructures->next();
			$found = $this->cmpRS($rS, $rStruct);
		}
		if ($found)
			return $rStruct->getId();
		return $found;
	}
	
   /**
   	* Compares an array of essential info to a recordStructure object
   	*
   	* @param array $rS an array containing displayName and partstructure info
   	* @param object HarmoniRecordStructure $rStruct object of comparison
	*/
	function cmpRS($rS, $rStruct) {
		if ($rS['displayName'] != $rStruct->getDisplayName())
			return FALSE;
		foreach ($rS as $key => $value) {
			if ($key != "displayName") {
				$partStructures =$rStruct->getPartStructures();
				$found = FALSE;
				while ($partStructures->hasNext() && !$found) {
					$pS =$partStructures->next();
					$type =$pS->getType();
					if (($pS->getDisplayName() == $value['name']) &&
						($type->getKeyword() == $value['type']))
						$found = TRUE;
				}
				if (!$found)
					return FALSE;
			}
		}
		return TRUE;
	}
}

?>