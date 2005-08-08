<?php
/**
 * @since 7/20/05
 * @package polyphony.repositoryImporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: RepositoryImporter.class.php,v 1.15 2005/08/08 16:06:18 cws-midd Exp $
 */ 
require_once(HARMONI."/utilities/Dearchiver.class.php");
require_once(POLYPHONY."/main/library/RepositoryImporter/XMLAssetIterator.class.php");
require_once(POLYPHONY."/main/library/RepositoryImporter/TabAssetIterator.class.php");
require_once(POLYPHONY."/main/library/RepositoryImporter/ExifAssetIterator.class.php");

/**
 * #insertion#
 * 
 * @since 7/20/05
 * @package polyphony.repositoryImporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: RepositoryImporter.class.php,v 1.15 2005/08/08 16:06:18 cws-midd Exp $
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
		$this->_repositoryId =& $repositoryId;
		$this->_dieOnError = $dieOnError;
		$this->_errors = array();
		$this->_goodAssetIds = array();
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
	function import () {
		$drManager =& Services::getService("RepositoryManager");
		$this->_destinationRepository =& $drManager->getRepository(
			$this->_repositoryId);
		$this->_srcDir = dirname($this->_filepath)."/";
		$this->decompress();
		if ($this->hasErrors())
			return;
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
		$dearchiver =& new Dearchiver();
		$worked = $dearchiver->uncompressFile($this->_filepath,
			dirname($this->_filepath));
		if ($worked == false)
			$this->addError("Failed to decompress file: ".$this->_filepath.
				".  Unsupported archive extension.");
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
		$assetIterator =& new $iteratorClass($this->_srcDir, $this);
		if ($this->hasErrors())
			return;
		$null = null;
		$this->assetBuildingIteration($assetIterator, $null);
	}

	/**
	 * Iterates through the building of the assets
	 * 
	 * @return boolean
	 * @access public
	 * @since 7/20/05
	 */
	function &assetBuildingIteration (&$assetIterator, &$parent) {
		$assetInfoIterator =& $this->getAllAssetsInfoIterator($assetIterator);
		if (!$assetInfoIterator)
			return $assetInfoIterator; // false
		while ($assetInfoIterator->hasNext()) {
			$info = $assetInfoIterator->next();
			$child =& $this->buildAsset($info["assetInfo"], $info["recordList"],
				$info["childAssetList"]);
			if (!$child)
				return $child; // false
			if (!is_null($parent))
				$parent->addAsset($child->getId());
		}
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
	function &getAllAssetsInfoIterator (&$assetIterator) {
		$allAssetInfo = array();
		while ($assetIterator->hasNext()) {
			$asset =& $assetIterator->next();
			$info = array();
			$info["assetInfo"] =& $this->getSingleAssetInfo($asset);
			$info["recordList"] =& $this->getSingleAssetRecordList($asset);
			$info["childAssetList"] =& $this->getChildAssetList($asset);
			if ($info["recordList"] !== false)
				$allAssetInfo[] = $info;
			else if ($this->_dieOnError)
				return $info["recordList"]; // false
		}
		return new HarmoniIterator($allAssetInfo);
	}

	/**
	 * tries to match given string to a schema with the same name.
	 * 
	 * @return false if no schema is matched, and the schemaId if matched
	 * @access public
	 * @since 7/18/05
	 */

	function matchSchema ($schema, &$repository) {
		$structures =& $repository->getRecordStructures();
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

	function matchPartStructures ($schema, &$partArray) {
		$partStructureIds = array();
		foreach ($partArray as $part) {
			$stop = true;
			$partStructures =& $schema->getPartStructures();
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
	function &buildAsset($assetInfo, $recordList, $childAssetList) {
		$idManager = Services::getService("Id");
		$asset =& $this->_destinationRepository->createAsset(
			$assetInfo['displayName'], $assetInfo['description'],
			$assetInfo['type']);
		$this->addGoodAssetId($asset->getId());
		foreach($recordList as $entry) {
			$assetRecord =& $asset->createRecord($entry['structureId']);
			$j = 0;
			foreach ($entry['partStructureIds'] as $id) {
				if($entry['structureId']->getIdString() != "FILE") {
					$assetRecord->createPart($id, $entry['parts'][$j]);
					$j++;
				}
				else if ($entry['structureId']->getIdString() == "FILE") {
					$mime = new MIMETypes();
					$filename = trim($entry['parts'][0]);
					$mimetype = $mime->getMIMETypeForFileName($this->_srcDir.
						$filename);
					$assetRecord->createPart($idManager->getId("FILE_DATA"),
						file_get_contents($this->_srcDir.$filename));
					$assetRecord->createPart($idManager->getId("FILE_NAME"),
						basename($filename));
					$assetRecord->createPart($idManager->getId("MIME_TYPE"),
						$mimetype);
					$imageProcessor =& Services::getService("ImageProcessor");
					if(isset($entry['parts'][1]) && $entry['parts'][1] != "") {
						$assetRecord->createPart(
							$idManager->getId("THUMBNAIL_DATA"),
							file_get_contents($this->_srcDir.$entry['parts'][1]));
					}
					else if ($imageProcessor->isFormatSupported($mimetype)) {
						$assetRecord->createPart(
							$idManager->getId("THUMBNAIL_DATA"),
							$imageProcessor->generateThumbnailData($mimetype,
							file_get_contents($this->_srcDir.$filename)));
						$assetRecord->createPart(
							$idManager->getId("THUMBNAIL_MIME_TYPE"),
							$imageProcessor->getThumbnailFormat());
					}
				}
			}
		}
		if (!is_null($childAssetList)) {
			$stop =& $this->assetBuildingIteration(new HarmoniIterator(
				$childAssetList), $asset);	
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
	function getPartObject(&$structureId, &$partStructureId, $part) {
		$structure =& $this->_destinationRepository->getRecordStructure(
			$structureId);
		$partStructure =& $structure->getPartStructure($partStructureId);
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
				return ShortString::withValue($part);
				break;
			case "float":
				return Float::withValue($part);
				break;
			case "time":
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