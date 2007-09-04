<?php
/**
 * @since 10/6/05
 * @package polyphony.library.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLAssetImporter.class.php,v 1.19 2007/09/04 20:28:01 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLRecordImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLFileRecordImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLRemoteFileRecordImporter.class.php");
require_once(HARMONI."Primitives/Chronology/DateAndTime.class.php");
require_once(HARMONI."/utilities/StatusStars.class.php");

/**
 * XMLAssetImporter imports an asset into a repository
 * 
 * @since 10/6/05
 * @package polyphony.library.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLAssetImporter.class.php,v 1.19 2007/09/04 20:28:01 adamfranco Exp $
 */
class XMLAssetImporter extends XMLImporter {
		
	/**
	 * 	Constructor; parses XML if passed file
	 * 
	 * 
	 * @return object XMLAssetImporter
	 * @access public
	 * @since 10/6/05
	 */
	function XMLAssetImporter ($existingArray) {
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
	function withFile ($existingArray, $filepath, $type, $class = 'XMLAssetImporter') {
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
	function withObject ($existingArray, $object, $filepath, $type, $class = 'XMLAssetImporter') {
		return parent::withObject($existingArray, $object, $filepath, $type, $class);
	}

	/**
	 * Sets up importer's self-knowledge
	 * 
	 * @access public
	 * @since 10/6/05
	 */
	function setupSelf () {
		$this->_childImporterList = array("XMLAssetImporter", 
			"XMLRecordImporter", "XMLFileRecordImporter", "XMLRemoteFileRecordImporter");
		$this->_childElementList = array("asset", "record", "filerecord", "remotefilerecord");
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
	function isImportable ($element) {
		if ($element->nodeName == "asset")
			return true;
		else
			return false;
	}

	/**
	 * Imports the current node's information
	 * 
	 * @access public
	 * @since 10/6/05
	 */
	function importNode () {
		$idManager = Services::getService("Id");
		if (!method_exists($this->_parent, "createAsset")) {
			$this->_stepParent =$this->_parent;
			$this->_parent =$this->_stepParent->getRepository();
		}

		$this->getNodeInfo();

		$hasId = $this->_node->hasAttribute("id");
		if ($hasId && (in_array($this->_node->getAttribute("id"),
				$this->_existingArray)	|| $this->_type == "update")) {
			$this->_myId =$idManager->getId($this->_node->getAttribute("id"));
			$this->_object =$this->_parent->getAsset($this->_myId);
			$this->update();
		} else {
			$this->_object =$this->_parent->createAsset(
				$this->_info['name'], $this->_info['description'], 
				$this->_info['type']);
			$this->_myId =$this->_object->getId();
			if (Services::serviceRunning("Logging")) {
				$loggingManager = Services::getService("Logging");
				$log =$loggingManager->getLogForWriting("Harmoni");
				$formatType = new Type("logging", "edu.middlebury", "AgentsAndNodes",
								"A format in which the acting Agent[s] and the target nodes affected are specified.");
				$priorityType = new Type("logging", "edu.middlebury",
					"Event_Notice",	"Normal events.");
				$item = new AgentNodeEntryItem("Create Node",
					"Asset: ".$this->_myId->getIdString()." created.");
				$item->addNodeId($this->_myId);
				$item->addNodeId($this->_parent->getId());
			}	
			if (isset($this->_stepParent)) {
				$this->_stepParent->addAsset($this->_myId);
				if (isset($item))
					$item->addNodeId($this->_stepParent->getId());
			}
			if (isset($this->_info['effectivedate']))
				$this->_object->updateEffectiveDate(DateAndTime::fromString(
					$this->_info['effectivedate']));
			if (isset($this->_info['expirationdate']))
				$this->_object->updateExpirationDate(DateAndTime::fromString(
					$this->_info['expirationdate']));
			if (isset($item))
				$log->appendLogWithTypes($item, $formatType, $priorityType);
		}

		if ($this->_node->hasAttribute("maintainOrder") &&
			($this->_node->getAttribute("maintainOrder") == TRUE))
			$this->doSets();
	}

	/**
	 * Does anything needed to sets
	 * 
	 * @access public
	 * @since 10/6/05
	 */
	function doSets () {
		$sets = Services::getService("Sets");
		$this->_set =$sets->getPersistentSet($this->_myId);
	}

	/**
	 * Relegates Children to their classes
	 * 
	 * @param object mixed $topImporter is the importer instance that parsed the XML
	 * @access public
	 * @since 10/6/05
	 */
	function relegateChildren ($topImporter) {
		foreach ($this->_node->childNodes as $element) {
			foreach ($this->_childImporterList as $importer) {
				if (!is_subclass_of(new $importer($this->_existingArray), 'XMLImporter')) {
					$this->addError("Class, '$class', is not a subclass of 'XMLImporter'.");
					if (Services::serviceRunning("Logging")) {
						$loggingManager = Services::getService("Logging");
						$log =$loggingManager->getLogForWriting("Harmoni");
						$formatType = new Type("logging", "edu.middlebury", "AgentsAndNodes",
										"A format in which the acting Agent[s] and the target nodes affected are specified.");
						$priorityType = new Type("logging", "edu.middlebury", "Error",
										"Events involving critical system errors.");
						
						$item = new AgentNodeEntryItem("Instantiate Undefined Importer", "$class is not a subclass of Importer");
						$item->addNodeId($this->_myId);
						$log->appendLogWithTypes($item,	$formatType,
							$priorityType);
					}
					break;
				}
				eval('$result = '.$importer.'::isImportable($element);');
				if ($result) {
					$imp = new $importer($this->_existingArray);
					if (isset($this->_set) && $element->nodeName == "asset")
						$this->_set->addItem($imp->import($topImporter,
							$element, $this->_type, $this->_object));
					else
						$imp->import($topImporter, $element, $this->_type,
							$this->_object);
					if ($imp->hasErrors()) 
						foreach($imp->getErrors() as $error)
							$this->addError($error);
					unset($imp);
				}
			}
			if ($topImporter->_granule == $element->nodeName)
				$topImporter->_status->updateStatistics();
		}
	}
	
	/**
	 * Looks for discrepencies between imported data and current data
	 * 
	 * @access public
	 * @since 9/12/05
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
			$loggingManager = Services::getService("Logging");
			$log =$loggingManager->getLogForWriting("Harmoni");
			$formatType = new Type("logging", "edu.middlebury", "AgentsAndNodes",
							"A format in which the acting Agent[s] and the target nodes affected are specified.");
			$priorityType = new Type("logging", "edu.middlebury", "Event_Notice",
							"Normal Events.");
			
			$item = new AgentNodeEntryItem("Modified Node", "Asset: ".
				$this->_myId->getIdString()." modified.");
			$item->addNodeId($this->_myId);
			
			$log->appendLogWithTypes($item,	$formatType, $priorityType);
		}


// 		if (isset($this->_info['effectivedate']) && 
// 			(DateAndTime::fromString($this->_info['effectivedate']) != 
// 			$this->_object->getEffectiveDate()))
// 			$this->_object->updateEffectiveDate(DateAndTime::fromString(
// 				$this->_info['effectivedate']));
// 		if (isset($this->_info['effectivedate']) && 
// 			(DateAndTime::fromString($this->_info['expirationdate']) !=
// 			$this->_object->getExpirationDate()))
// 			$this->_object->updateExpirationDate(DateAndTime::fromString(
// 				$this->_info['expirationdate']));
	}

}

?>