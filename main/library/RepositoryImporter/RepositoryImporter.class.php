<?php
/**
 * @since 7/20/05
 * @package polyphony.repositoryImporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: RepositoryImporter.class.php,v 1.7 2005/07/25 15:32:04 cws-midd Exp $
 */ 

require_once(POLYPHONY."/main/library/RepositoryImporter/XMLAssetIterator.class.php");
require_once(POLYPHONY."/main/library/RepositoryImporter/TabAssetIterator.class.php");


/**
 * #insertion#
 * 
 * @since 7/20/05
 * @package polyphony.repositoryImporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: RepositoryImporter.class.php,v 1.7 2005/07/25 15:32:04 cws-midd Exp $
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
	function RepositoryImporter ($filepath, $repositoryId) {
		$this->_filepath = $filepath;
		$this->_repositoryId =& $repositoryId;
		$this->_decompressed = FALSE;
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
		$this->_destinationRepository =& $drManager->getRepository($this->_repositoryId);
		$this->_srcDir = dirname($this->_filepath);
		
		$this->decompress();
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
		if (!$this->_decompressed) {
			$dearchiver =& new Dearchiver();
			$dearchiver->uncompressFile($this->_filepath, dirname($this->_filepath));
			$this->_decompressed = TRUE;
		}
	}
	
	/**
	 * 
	 * 
	 * @return boolean
	 * @access public
	 * @since 7/20/05
	 */
	function isDataValid() {
		return true;
		$this->decompress();
		die("Method ".__FUNCTION__." declared in class '".__CLASS__."' was not overidden by a child class.");
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
		$assetIterator =& new $iteratorClass($this->_srcDir);
		$null = null;
		$this->assetBuildingIteration($assetIterator, $null);
	}

	/**
	 * Iterates through the building of the assets
	 * 
	 * @return void
	 * @access public
	 * @since 7/20/05
	 */
	function assetBuildingIteration (&$assetIterator, &$parent) {
		$assetInfoIterator =& $this->getAllAssetsInfoIterator($assetIterator);
		while ($assetInfoIterator->hasNext()) {
			$info =& $assetInfoIterator->next();
			$child =& $this->buildAsset($info["assetInfo"], $info["recordList"], $info["childAssetList"]);
			if (!is_null($parent))
				$parent->addAsset($child->getId());
		}
	}
	
	/**
	 * Iterates through the assets and gathers all required information for creating assets
	 * 
	 * @return iterator
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
			$allAssetInfo[] =& $info;
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
	 * @return asset 
	 * @access public
	 * @since 7/18/05
	 *
	 */
	function &buildAsset($assetInfo, $recordList, $childAssetList) {
		$idManager = Services::getService("Id");
		$asset =& $this->_destinationRepository->createAsset($assetInfo['displayName'],
			$assetInfo['description'], $assetInfo['type']);
		foreach($recordList as $entry) {
			$assetRecord =& $asset->createRecord($entry['structureId']);
			$j = 0;
			foreach ($entry['partStructureIds'] as $id) {
				if($entry['structureId']->getIdString() != "FILE") {
					$structure =& $this->_destinationRepository->getRecordStructure($entry['structureId']);
					$partStructure =& $structure->getPartStructure($id);
					$type = $partStructure->getType();
					$partObject = RepositoryImporter::getPartObject($type, $entry['parts'][$j]);
					$assetRecord->createPart($id, $partObject);
					$j++;
				}
				else if ($entry['structureId']->getIdString() == "FILE") {
					$mimeTypes = new MIMETypes();
					$mime = new MIMETypes();
					$filename = trim($entry['parts'][0]);
					$mimetype = $mime->getMIMETypeForFileName($this->_srcDir."/".$filename);
					$assetRecord->createPart($idManager->getId("FILE_DATA"),
						file_get_contents($this->_srcDir."/".$filename));
					$assetRecord->createPart($idManager->getId("FILE_NAME"), basename($filename));
					$assetRecord->createPart($idManager->getId("MIME_TYPE"), $mimetype);
					$imageProcessor =& Services::getService("ImageProcessor");
					if(isset($entry['parts'][1])) {
						$assetRecord->createPart($idManager->getId("THUMBNAIL_DATA"),
							file_get_contents($this->_srcDir."/".$entry['parts'][1]));
					} 
					// If our image format is supported by the image processor,
					// generate a thumbnail.
					else if ($imageProcessor->isFormatSupported($mimetype)) {
							$assetRecord->createPart($idManager->getId("THUMBNAIL_DATA"), 
								$imageProcessor->generateThumbnailData($mimetype, 
								file_get_contents($this->_srcDir."/".$filename)));
							$assetRecord->createPart($idManager->getId("THUMBNAIL_MIME_TYPE"),
								$imageProcessor->getThumbnailFormat());
					}
				}
			}
		}
		if (!is_null($childAssetList))
			$this->assetBuildingIteration(new HarmoniIterator($childAssetList), $asset);
		
		return $asset;
	}
	
	/**
	 * creates appropriate object from given primitive for part creation
	 * 
	 * @param object Harmonitype $type
	 * @param mixed more
	 * @return object mixed
	 * @access public
	 * @since 7/21/05
	 */
	function getPartObject($type, $more) {
		$typeString = $type->getKeyword();
		switch($typeString) {
			case "string":
				return String::withValue($more);
				break;
			case "integer":
				return Integer::withValue($more);
				break;
			case "boolean":
				return Boolean::withValue($more);
				break;
			case "shortstring":
				return ShortString::withValue($more);
				break;
			case "float":
				return Float::withValue($more);
				break;
			case "time":
				return DateAndTime::fromString($more);
				break;
			case "type": 
				return HarmoniType::stringToType($more);
				break;
			default:
				throwError(new Error("Unsupported PartStructure DataType, ".
					HarmoniType::typeToString($type), "polyphony.RepositoryImporter", true));
		}
	}

	
}

?>