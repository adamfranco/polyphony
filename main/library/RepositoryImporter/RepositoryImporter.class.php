<?php
/**
 * @since 7/20/05
 * @package polyphony.repositoryImporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: RepositoryImporter.class.php,v 1.1 2005/07/21 13:59:46 cws-midd Exp $
 */ 

/**
 * #insertion#
 * 
 * @since 7/20/05
 * @package polyphony.repositoryImporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: RepositoryImporter.class.php,v 1.1 2005/07/21 13:59:46 cws-midd Exp $
 */
class RepositoryImporter {
	
	
	/**
	 * Constructor
	 * 
	 * @param String filename
	 * @return object
	 * @access public
	 * @since 7/20/05
	 */
	function RepositoryImporter ($filename, $repositoryId) {
		die("override the constructor in a child class");
	}
	/**
	 * Constructor
	 * 
	 * @param String filename
	 * @return obj
	 * @access public
	 * @since 7/20/05
	 */
	function import ($filename, $repositoryId) {
		$drManager =& Services::getService("RepositoryManager");
		$this->_destinationRepository =& $drManager->getRepository($repositoryId);
		$this->_srcDir = dirname($filename);
		
		$dearchiver =& new Dearchiver();
		$dearchiver->uncompressFile($filename, dirname($filename));
		$this->parse();
	}
	
	/**
	 * 
	 * 
	 * @return void
	 * @access public
	 * @since 7/20/05
	 */
	function parse () {
		// retain list of assets and info
		$assetInfo =& $this->getAssetInfo();
		while ($assetInfo->hasNext()) {
			$info =& $assetInfo->next();
			$this->buildAsset($this->_destinationRepository, $info["assetInfo"], $info["recordList"], $this->_srcDir);
		}
	}


	/**
	 * tries to match given string to a schema with the same name.
	 * 
	 * @return false if no schema is matched, and the schemaId if matched
	 * @access public
	 * @since 7/18/05
	 */

	function matchSchema ($schema, $repository) {
		$structures =& $repository->getRecordStructures();
		$stop = true;
		while($structures->hasNext()) {
			$testStructure = $structures->next();
			if($testStructure->getDisplayName() == $schema) {
				$structureId = $testStructure->getId();														// retain structureId
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
			$partStructures =& $schema->getPartStructures();
			while ($partStructures->hasNext()) {
				$partStructure = $partStructures->next();
				if ($part == $partStructure->getDisplayName()) {										// find the corresponding partStructure
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
	 * @access public
	 * @since 7/18/05
	 *
	*/

	function buildAsset($repository, $assetInfo, $recordList, $newPath) {
		$idManager = Services::getService("Id");
		$asset =& $repository->createAsset($assetInfo[0], $assetInfo[1], $assetInfo[2]);
		foreach($recordList as $entry) {
			$assetRecord =& $asset->createRecord($entry[0]);													// create record with stored id
			$j = 0;																								// counter for parallel arrays
			foreach ($entry[1] as $id) {
				if($entry[0]->getIdString() != "FILE"){
					$structure =& $repository->getRecordStructure($entry[0]);
					$partStructure =& $structure->getPartStructure($id);
					$type = $partStructure->getType();
					$partObject = RepositoryImporter::getPartObject($type, $entry[2][$j]);
					$assetRecord->createPart($id, $partObject);										// access parallel arrays to create parts
					$j++;																			// increment
				}
				else if ($entry[0]->getIdString() == "FILE") {
					$mimeTypes = new MIMETypes();
					$mime = new MIMETypes();
					$filename = trim($entry[2][0]);
					$mimetype = $mime->getMIMETypeForFileName($newPath."/data/".$filename);
					$assetRecord->createPart($idManager->getId("FILE_DATA"), file_get_contents($newPath."/data/".$filename));
					$assetRecord->createPart($idManager->getId("FILE_NAME"), $filename);
					$assetRecord->createPart($idManager->getId("MIME_TYPE"), $mimetype);
					$assetRecord->createPart($idManager->getId("THUMBNAIL_DATA"), file_get_contents($newPath."/data/".$filename));
				}
			}
		}
	}

	function getPartObject($type, $more) {
		$typeString = $type->getKeyword();
		switch($typeString) {
			case "string":
			return new String($more);
			break;
			case "integer":
			return new Integer($more);
			break;
			case "boolean":
			return new Boolean($more);
			break;
			case "shortstring":
			return new ShortString($more);
			break;
			case "float":
			return new Float($more);
			break;
			case "time":
			return new Time($more);
			break;
			default:
			return new OkiType($more);
		}
	}
	
	/**
	 * getAssetInfo
	 * 
	 * @return array
	 * @access public
	 * @since 7/20/05
	 */
	function &getAssetInfo () {
		$allAssetInfo = array();
		$iteratorClass = $this->_iteratorClass;
		$assetIterator =& new $iteratorClass($this->_srcDir);
		
		while ($assetIterator->hasNext()) {
			$asset =& $assetIterator->next();
			$info = array();
			$info["assetInfo"] =& $this->getSingleAssetInfo($asset);
			$info["recordList"] =& $this->getSingleAssetRecordList($asset);
			$allAssetInfo[] =& $info; 
		}
		return new HarmoniIterator($allAssetInfo);
	}
	
}

?>