<?php
/**
* @since 7/20/05
 * @package polyphony.repository_importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: TabRepositoryImporter.class.php,v 1.21 2008/03/06 20:04:10 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/RepositoryImporter.class.php");

/**
* <##>
 * 
 * @since 7/20/05
 * @package polyphony.repository_importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: TabRepositoryImporter.class.php,v 1.21 2008/03/06 20:04:10 adamfranco Exp $
 */
class TabRepositoryImporter
	extends RepositoryImporter
{
	
	/**
   	 * Constructor for Tab-Delimited Importer	
 	 * 
	 * @param String filename
	 * @return object
	 * @access public
	 * @since 7/20/05
	 */
	function __construct ($filepath, $repositoryId, $dieOnError=false, $dataDir=NULL) {
		$this->_assetIteratorClass = "TabAssetIterator";
		$this->_dataDir = $dataDir;
		parent::__construct($filepath, $repositoryId, $dieOnError);
	}
	
	/**
	 * Get parameters for createAsset
	 * 
	 * @param mixed input
	 * @return array
	 * @access public
	 * @since 7/20/05
	 */
	function getSingleAssetInfo ($input) {
		$assetInfo = array();
		$assetInfo['displayName'] = $input[0];
		$assetInfo['description'] = $input[1];
		if($input[2] == "")
			$assetInfo['type'] = new HarmoniType("Asset Types", 
				"edu.middlebury.concerto",
				"Generic Asset");
		else
			$assetInfo['type'] = new HarmoniType("Asset Types",
				"edu.middlebury.concerto",
				$input[2]);

		return $assetInfo;
	}
	
	
	/**
	 * Get parameters for createRecord
	 * 	
	 * @param mixed input	
	 * @return array or false on fatal error
	 * @access public
	 * @since 7/20/05
	 */
	function getSingleAssetRecordList ($input) {
		if (Services::serviceRunning("Logging")) {
			$loggingManager = Services::getService("Logging");
			$log =$loggingManager->getLogForWriting("Harmoni");
			$formatType = new Type("logging", "edu.middlebury", "AgentsAndNodes",
							"A format in which the acting Agent[s] and the target nodes affected are specified.");
			$priorityType = new Type("logging", "edu.middlebury", "Error",
							"Events involving critical system errors.");
		}			
		if (!isset($this->_structureId)) {
			$meta = fopen($this->_srcDir."metadata.txt", "r");
			if($this->_dataDir !== NULL){
				$this->_srcDir = $this->_dataDir;
			}
			$schema = preg_replace("/[\n\r]*$/","",fgets($meta));
			$titleline = preg_replace("/[\n\r]*$/", "", fgets($meta));
			fclose($meta);
			
			$this->_fileStructureId = $this->matchSchema("File",
				$this->_destinationRepository);
			
			$checkPartsArray = array("File Name");
			$this->_filePartIds = $this->matchPartStructures(
				$this->_destinationRepository->getRecordStructure($this->_fileStructureId), 
				$checkPartsArray);			

			$this->_structureId = $this->matchSchema($schema,
				$this->_destinationRepository);
		
			if (!$this->_structureId) {
				$this->addError("The schema: ".$schema.
					" does not exist in repository: ".
					$this->_repositoryId->getIdString());
				if (isset($log)) {
					$item = new AgentNodeEntryItem("TabImporter Error",
						"The schema: $schema does not exist in repository: $this->_repositoryId->getIdString().");
					$log->appendLogWithTypes($item, $formatType, $priorityType);
				}
				return $this->_structureId; // false
			}

			$titles = explode ("\t", $titleline);
			$partArray = array_slice($titles, 4);
			$this->_partStructureIds = $this->matchPartStructures(
				$this->_destinationRepository->getRecordStructure($this->_structureId), 
				$partArray);
			
			if (!$this->_partStructureIds) {
				$this->addError("One or more of the Parts in the Tab-Delimited file for Schema: ".
					$schema." are not valid.");
				if (isset($log)) {
					$item = new AgentNodeEntryItem("TabImporter Error",
						"One or more of the parts in the Tab-Delimited file for Schema: $schema are not valid.");
					$log->appendLogWithTypes($item, $formatType, $priorityType);
				}
				return $this->_partStructureIds; // false
			}
		}
		if ($this->_structureId && $this->_partStructureIds) {
			$recordList = array();
			if ($input[3] != "") {
				if (is_readable($this->_srcDir.$input[3])) {
					$fileElement = array();
					
					$fileElement['structureId'] =$this->_fileStructureId;
					$fileElement['partStructureIds'] =$this->_filePartIds;
					$fileElementParts[] = $input[3];
					
					$fileElement['parts'] = $fileElementParts;
					$recordList[] = $fileElement;
				}
				else {
					$this->addError("File: ".$this->_srcDir.$input[3].
						" does not exist for import.");
					if (isset($log)) {
						$item = new AgentNodeEntryItem("TabImporter Error",
							"File: $this->_srcDir/$input[3] does not exist for import.");
						$log->appendLogWithTypes($item, $formatType, $priorityType);
					}
					$false = false;
					return $false;
				}
			}
	
			$partObjects = array();
			
			for ($i = 0; $i < count($this->_partStructureIds); $i++) {
				if (isset($input[$i + 4]))
					$partObject = $this->getPartObject($this->_structureId,
						$this->_partStructureIds[$i], $input[$i + 4]);
				else
					$partObject = null;
				if (!$partObject)
					continue;
				$partObjects[] = $partObject;
			}
			
			$recordListElement = array();
			$recordListElement['structureId'] =$this->_structureId;
			$recordListElement['partStructureIds'] =$this->_partStructureIds;
			$recordListElement['parts'] = $partObjects;
			$recordList[] =$recordListElement;
			
			return $recordList;
		}
	}

	/**
	 * get asset list for child assets
	 * 
	 * @param mixed input
	 * @return null
	 * @access public
	 * @since 7/20/05
	 */
	function getChildAssetList ($input) {
		return null;
	}
}

?>