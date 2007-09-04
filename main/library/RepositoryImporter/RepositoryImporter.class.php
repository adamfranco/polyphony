<?php
/**
 * @since 7/20/05
 * @package polyphony.library.repository_importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: RepositoryImporter.class.php,v 1.32 2007/09/04 20:28:01 adamfranco Exp $
 */ 
require_once(HARMONI."/utilities/Dearchiver.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLImporter.class.php");
require_once(POLYPHONY."/main/library/RepositoryImporter/TabAssetIterator.class.php");
require_once(POLYPHONY."/main/library/RepositoryImporter/ExifAssetIterator.class.php");

/**
 * #insertion#
 * 
 * @since 7/20/05
 * @package polyphony.library.repository_importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: RepositoryImporter.class.php,v 1.32 2007/09/04 20:28:01 adamfranco Exp $
 */
class RepositoryImporter {
	
	
	/**
	 * Constructor
	 * 
	 * @param String filename
	 * @param object Id $repositoryId
	 * @return object
	 * @access public
	 * @since 7/20/05
	 */
	function RepositoryImporter ($filepath, $repositoryId, $dieOnError = false) {
		$this->_filepath = $filepath;
		$this->_repositoryId =$repositoryId;
		$this->_dieOnError = $dieOnError;
		$this->_errors = array();
		$this->_goodAssetIds = array();
		$this->_root = null;
	}
	
	/**
	 * Set An asset that will the parent of imported assets
	 * 
	 * @param object Asset
	 * @return void
	 * @access public
	 * @since 4/2/07
	 */
	function setParent ($asset) {
		$this->_root =$asset;
	}
	
	/**
	 * 
	 * 
	 * @param String filename
	 * @param object Id repositoryId
	 * @return obj
	 * @access public
	 * @since 7/20/05
	 */
	function import ($decompress = true) {
		$drManager = Services::getService("RepositoryManager");
		$this->_destinationRepository =$drManager->getRepository(
			$this->_repositoryId);
		$this->_srcDir = dirname($this->_filepath)."/";
		if ($decompress) {
			$this->decompress();
			if ($this->hasErrors())
				return;
		}
		$this->parse();
	}
	
	/**
	 * 
	 * 
	 * @return void
	 * @access public
	 * @since 7/20/05
	 */
	function decompress () {
		$statusStars = new StatusStars(_("Decompressing archive"));
		$statusStars->initializeStatistics(4);
		$statusStars->updateStatistics();
		
		$dearchiver = new Dearchiver();
		$worked = $dearchiver->uncompressFile($this->_filepath,
			dirname($this->_filepath));
		if ($worked == false) {
			$this->addError("Failed to decompress file: ".$this->_filepath.
				".  Unsupported archive extension.");
			if (Services::serviceRunning("Logging")) {
				$loggingManager = Services::getService("Logging");
				$log =$loggingManager->getLogForWriting("Harmoni");
				$formatType = new Type("logging", "edu.middlebury", 
					"AgentsAndNodes",
					"A format in which the acting Agent[s] and the target nodes affected are specified.");
				$priorityType = new Type("logging", "edu.middlebury", "Error",
								"Events involving critical system errors.");
				
				$item = new AgentNodeEntryItem("Importer Decompression Error",
					"Failed to decompress file: ".$this->_filepath.
					". Unsupported archive extension.");
				$log->appendLogWithTypes($item,	$formatType, $priorityType);
			}
		}
		unset($dearchiver);
		$statusStars->updateStatistics();
		$statusStars->updateStatistics();
		$statusStars->updateStatistics();
	}

	/**
	 * Parses the archive file according to its type i.e. properly
	 * 
	 * @return void
	 * @access public
	 * @since 7/20/05
	 */
	function parse () {
		$iteratorClass = $this->_assetIteratorClass;
		$assetIterator = new $iteratorClass($this->_srcDir, $this);
		if ($this->hasErrors())
			return;
		
		$false = false;
		$this->assetBuildingIteration($assetIterator, $this->_root, $false);
		unset($assetIterator);
	}

	/**
	 * Iterates through the building of the assets
	 * 
	 * @return boolean
	 * @access public
	 * @since 7/20/05
	 */
	function assetBuildingIteration ($assetIterator, $parent, $buildOrderedSet) {
		$setManager = Services::getService("Sets");
		$assetInfoIterator =$this->getAllAssetsInfoIterator($assetIterator);
		
		$statusStars = new StatusStars(_("Creating Assets"));
		$statusStars->initializeStatistics($assetInfoIterator->count());
		
		if (!$assetInfoIterator)
			return $assetInfoIterator; // false
		if ($buildOrderedSet)
			$set =$setManager->getPersistentSet($parent->getId());		
		while ($assetInfoIterator->hasNext()) {
			$info =$assetInfoIterator->next();
			$child =$this->buildAsset($info);
			if (!$child)
				return $child; // false
			if (!is_null($parent))
				$parent->addAsset($child->getId());
			if ($buildOrderedSet)
				$set->addItem($child->getId());
			
			$statusStars->updateStatistics();
		}
		unset($assetInfoIterator);
		$true = true;
		return $true;
	}
	
	/**
	 * Iterates through the assets and gathers all required information for 
	 * creating assets
	 * 
	 * @return iterator or false on fatal error
	 * @access public
	 * @since 7/20/05
	 */
	function getAllAssetsInfoIterator ($assetIterator) {
		$allAssetInfo = array();
		while ($assetIterator->hasNext()) {
			$asset =$assetIterator->next();
			$info = array();
			$info["assetInfo"] =$this->getSingleAssetInfo($asset);
			$info["recordList"] =$this->getSingleAssetRecordList($asset);
			$info["childAssetList"] =$this->getChildAssetList($asset);
			$info["buildOrderedSet"] =$this->getBuildOrderedSet($asset);
			if ($info["recordList"] !== false)
				$allAssetInfo[] = $info;
			else if ($this->_dieOnError)
				return $info["recordList"]; // false
		}
		$obj = new HarmoniIterator($allAssetInfo);
		return $obj;
	}

	/**
	 * tries to match given string to a schema with the same name.
	 * 
	 * @return false if no schema is matched, and the schemaId if matched
	 * @access public
	 * @since 7/18/05
	 */

	function matchSchema ($schema, $repository) {
		$structures =$repository->getRecordStructures();
		$stop = true;
		while($structures->hasNext()) {
			$testStructure = $structures->next();
			if($testStructure->getDisplayName() == $schema) {
				$structureId = $testStructure->getId();
				return $structureId;
			}
		}
		return false;
	}

	/**
  	 * tries to match the given array with partstructure in the given structure
 	 * 
 	 * @return false if not matched and an array of partstructure ids
 	 * @access public
 	 * @since 7/18/05
 	 */

	function matchPartStructures ($schema, $partArray) {
		$partStructureIds = array();
		foreach ($partArray as $part) {
			$stop = true;
			$partStructures =$schema->getPartStructures();
			while ($partStructures->hasNext()) {
				$partStructure = $partStructures->next();
				if ($part == $partStructure->getDisplayName()) {
					$partStructureIds[] = $partStructure->getId();
					$stop = false;
					break;
				}	
			}
		if ($stop)
			return false;
		}
		return $partStructureIds;
	}

	/**
	 * builds asset in repository from assetinfo and records from recordlist
	 *
	 * @param array assetInfo
	 * @param array recordList
	 * @return asset or false on fatal error
	 * @access public
	 * @since 7/18/05
	 *
	 */
	function buildAsset($info) {
		$assetInfo =$info['assetInfo'];
		$recordList =$info['recordList'];
		$childAssetList =$info['childAssetList'];
		$buildOrderedSet =$info['buildOrderedSet'];
		$idManager = Services::getService("Id");
		$mime = Services::getService("MIME");
		$FILE_ID =$idManager->getId("FILE");
		$FILE_DATA_ID =$idManager->getId("FILE_DATA");
		$FILE_NAME_ID =$idManager->getId("FILE_NAME");
		$MIME_TYPE_ID =$idManager->getId("MIME_TYPE");
		$THUMBNAIL_DATA_ID =$idManager->getId("THUMBNAIL_DATA");
		$THUMBNAIL_MIME_TYPE_ID =$idManager->getId("THUMBNAIL_MIME_TYPE");

		
		$asset =$this->_destinationRepository->createAsset(
			$assetInfo['displayName'], $assetInfo['description'],
			$assetInfo['type']);
		$assetId =$asset->getId();
		// log creation
		if (Services::serviceRunning("Logging")) {
			$loggingManager = Services::getService("Logging");
			$log =$loggingManager->getLogForWriting("Harmoni");
			$formatType = new Type("logging", "edu.middlebury", 
				"AgentsAndNodes",
				"A format in which the acting Agent[s] and the target nodes affected are specified.");
			$priorityType = new Type("logging", "edu.middlebury",
				"Event_Notice",	"Normal Events.");
			$item = new AgentNodeEntryItem("Create Node", "Asset: ".
				$assetId->getIdString()." created.");
			$item->addNodeId($assetId);
			$item->addNodeId($this->_destinationRepository->getId());
			$log->appendLogWithTypes($item, $formatType, $priorityType);
		}
		$this->addGoodAssetId($asset->getId());
		RecordManager::setCacheMode(false);
		foreach($recordList as $entry) {
			$assetRecord =$asset->createRecord($entry['structureId']);
			$j = 0;
			printpre("creating record for: "); printpre($entry['structureId']);
			foreach ($entry['partStructureIds'] as $id) {
				if(!($entry['structureId']->isEqual($FILE_ID))) {
					if (is_array($entry['parts'][$j])) {
						for ($k = 0; $k < count($entry['parts'][$j]); $k++)
							$assetRecord->createPart($id, $entry['parts'][$j][$k]);
					} else {
						$assetRecord->createPart($id, $entry['parts'][$j]);
					}
					$j++;
				}
				else if ($entry['structureId']->isEqual($FILE_ID)) {
					$filename = trim($entry['parts'][0]);
					$mimetype = $mime->getMIMETypeForFileName($filename);
					$assetRecord->createPart($FILE_DATA_ID,
						file_get_contents($filename));
					$assetRecord->createPart($FILE_NAME_ID,
						basename($filename));
					$assetRecord->createPart($MIME_TYPE_ID,
						$mimetype);
					$imageProcessor = Services::getService("ImageProcessor");
					if(isset($entry['parts'][1]) && $entry['parts'][1] != "") {
						$assetRecord->createPart(
							$THUMBNAIL_DATA_ID,
							file_get_contents($this->_srcDir.
								$entry['parts'][1]));
					}
					else if ($imageProcessor->isFormatSupported($mimetype)) {
						$thumbData = $imageProcessor->generateThumbnailData($mimetype,
							file_get_contents($filename));
						if ($thumbData) {
							$assetRecord->createPart(
								$THUMBNAIL_DATA_ID,
								$thumbData);
							$assetRecord->createPart(
								$THUMBNAIL_MIME_TYPE_ID,
								$imageProcessor->getThumbnailFormat());
						}
					}
					break;
				}
			}
		}
		if (!is_null($childAssetList)) {
			$stop =$this->assetBuildingIteration(new HarmoniIterator(
				$childAssetList), $asset, $buildOrderedSet);	
			if (!$stop)
				return $stop; // false
		}
		return $asset;
	}
	
	/**
	 * creates appropriate object from given ids
	 * 
	 * @param Id $structureId
	 * @param Id $partStructureId
	 * @param String $part
	 * @return object mixed
	 * @access public
	 * @since 7/21/05
	 */
	function getPartObject($structureId, $partStructureId, $part) {
		$structure =$this->_destinationRepository->getRecordStructure(
			$structureId);
		$partStructure =$structure->getPartStructure($partStructureId);
		$type = $partStructure->getType();
		$typeString = $type->getKeyword();
		switch($typeString) {
			case "shortstring":
			case "string":
				$obj = String::withValue($part);
				return $obj;
				break;
			case "integer":
				$obj = Integer::withValue($part);
				return $obj;
				break;
			case "boolean":
				$obj = Boolean::withValue($part);
				return $obj;
				break;
			case "float":
				$obj = Float::withValue($part);
				return $obj;
				break;
			case "datetime":
				$obj = DateAndTime::fromString($part);
				return $obj;
				break;
			case "type": 
				$obj = HarmoniType::fromString($part);
				return $obj;
				break;
			default:
				$this->addError("Unsupported PartStructure DataType: ".
					HarmoniType::typeToString($type).".");
				$false = false;
				return $false;
		}
	}

	/**
	 * Gets whether or not to build an ordered list
	 *
	 * Should be overridden in subclasses that allow child assets
	 *
	 * @param obj $asset
	 * @return bool
	 * @since 8/16/05
	 */
	 function getBuildOrderedSet ($asset) {
	 	$false = false;
	 	return $false;
	 }

	/**
	 * Print the AssetIds for Assets created properly by the importer
	 *
	 * @param array $goodAssetIds
	 * @since 7/29/05
	 */
	 function printErrorMessages() {
	 	foreach ($this->_errors as $errorString) {
	 		print("Error: ".$errorString."<br />");
	 	}
	 }

	
	/**
	 * Print the AssetIds for Assets created properly by the importer
	 *
	 * @param array $goodAssetIds
	 * @since 7/29/05
	 */
	 function printGoodAssetIds() {
	 	foreach ($this->_goodAssetIds as $id) {
	 		print("Asset: ".$id->getIdString()."<br />");
	 	}
	 }

	/**
	 * gets error array
	 * 
	 * @return array
	 * @access public
	 * @since 7/26/05
	 */
	function getErrors() {
		return $this->_errors;
	}
	
	/**
	 * checks for errors
	 * 
	 * @return boolean
	 * @access public
	 * @since 7/26/05
	 */
	function hasErrors() {
		return (count($this->_errors) > 0);
	}

	/**
	 * adds an error to the  error array
	 * 
	 * @param String $error
	 * @access public
	 * @since 7/26/05
	 */
	function addError($error) {
		$this->_errors[] = $error;
	}

	/**
	 * gets created assset ids array
	 * 
	 * @return array
	 * @access public
	 * @since 7/26/05
	 */
	function getGoodAssetIds() {
		return $this->_goodAssetIds;
	}

	/**
	 * checks for built Assets
	 * 
	 * @return boolean
	 * @access public
	 * @since 7/26/05
	 */
	function hasAssets() {
		return (count($this->_goodAssetIds) > 0);
	}


	/**
	 * adds an error to the  error array
	 * 
	 * @param String $error
	 * @access public
	 * @since 7/26/05
	 */
	function addGoodAssetId($goodAssetId) {
		$this->_goodAssetIds[] = $goodAssetId;
	}
}
?>