<?php
/**
* @since 7/20/05
 * @package polyphony.repositoryImporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ExifRepositoryImporter.class.php,v 1.18 2006/05/30 20:18:45 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/RepositoryImporter.class.php");
require_once(EXIF);
require_once(DOMIT);
/**
* <##>
 * 
 * @since 7/20/05
 * @package polyphony.repositoryImporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ExifRepositoryImporter.class.php,v 1.18 2006/05/30 20:18:45 adamfranco Exp $
 */
class ExifRepositoryImporter
	extends RepositoryImporter
{

	/**
	 * Constructor for ExifImporter	
	 * 
	 * @param String filename
	 * @return object
	 * @access public
	 * @since 7/20/05
	 */
	function ExifRepositoryImporter ($filepath, $repositoryId, $dieOnError=false) {
		$this->_assetIteratorClass = "ExifAssetIterator";
		parent::RepositoryImporter($filepath, $repositoryId, $dieOnError);
	}

	/**
	 * Get parameters for createAsset
	 * 
	 * @param mixed input
	 * @return array
	 * @access public
	 * @since 7/20/05
	 */
	function &getSingleAssetInfo (& $input) {
		$assetInfo = array();

		$headerData = get_jpeg_header_data($input);
		$photoshopIRB = get_Photoshop_IRB($headerData);
		$this->_photoshopIPTC = get_Photoshop_IPTC($photoshopIRB);

		foreach ($this->_photoshopIPTC as $array) {
			if($array['RecName'] == "description")
			$assetInfo['description'] = $array['RecData'];
			else $assetInfo['description'] = "";
			if($array['RecName'] == "Object Name (Title)")
			$assetInfo['displayName'] = $array['RecData'];
			else $assetInfo['displayName'] = "";
		}

		$assetInfo['type'] = "";
		if ($assetInfo['type'] == "")
		$assetInfo['type'] = new HarmoniType("Asset Types", "edu.middlebury.concerto",
		"Generic Asset");
		else
		$assetInfo['type'] = new HarmoniType("Asset Types", "edu.middlebury.concerto",
		$assetInfo['type']);

		return $assetInfo;
	}

	/**
	 * get parameters for createRecord
	 * 
	 * @param mixed input
	 * @return array or false on fatal error
	 * @access public
	 * @since 7/20/05
	 */
	function &getSingleAssetRecordList (&$input) {
		if (Services::serviceRunning("Logging")) {
			$loggingManager =& Services::getService("Logging");
			$log =& $loggingManager->getLogForWriting("Harmoni");
			$formatType =& new Type("logging", "edu.middlebury", 
				"AgentsAndNodes",
				"A format in which the acting Agent[s] and the target nodes affected are specified.");
			$priorityType =& new Type("logging", "edu.middlebury", "Error",
							"Events involving critical system errors.");
		}			
		$idManager =& Services::getService("Id");
		$this->_fileStructureId =& $idManager->getId("FILE");
		$fileparts = array("File Name", "Thumbnail Data");
		$this->_fileNamePartIds = $this->matchPartStructures(
			$this->_destinationRepository->getRecordStructure(
			$this->_fileStructureId), $fileparts);
		if (!isset($this->_structureId)) {
			$import =& new DOMIT_Document();
			if ($import->loadXML($this->_srcDir."schema.xml")) {
				if (!($import->documentElement->hasChildNodes())){
					$this->addError("There are no schemas defined in : ".
						$this->_srcDir."schema.xml.");
					if (isset($log)) {
						$item =& new AgentNodeEntryItem("ExifImporter Error",
							"There are no schemas defined in: ".
							$this->_srcDir."schema.xml.");
						$log->appendLogWithTypes($item, $formatType, 
							$priorityType);
					}
					return false;
				}
			}
			else {
				$this->addError("XML parse failed: ".$this->_srcDir."schema.xml does not exist or contains poorly formed XML.");
				if (isset($log)) {
					$item =& new AgentNodeEntryItem("ExifImporter DOMIT Error",
						"XML parse failed: ".$this->_srcDir."schema.xml does not exist or contains poorly formed XML.");
					$log->appendLogWithTypes($item, $formatType, $priorityType);
				}
				return false;
			}
			$istructuresList =& $import->documentElement->childNodes;
			$this->_structureId = array();
			$this->_partsFinal = array();
			$this->_valuesFinal = array();
			foreach($istructuresList as $istructure) {
				$valuesPreFinal = array();
				$partStructuresArray = array();
				if($istructure->nodeName == "recordStructure") {
					//match the structure
					$ipartStructures =& $istructure->childNodes;
					if($ipartStructures[0]->getText() != ""){
						$matchedSchema = $idManager->getId($ipartStructures[0]->getText());
					} else
						$matchedSchema = $this->matchSchema($ipartStructures[1]->getText(), $this->_destinationRepository);
					if($matchedSchema == false) {
						$this->addError("Schema: ".
							$ipartStructures[1]->getText()." does not exist");
						if (isset($log)) {
							$item =& new AgentNodeEntryItem("ExifImporter 
								Error", "Schema: ".
								$ipartStructures[1]->getText().
								" does not exist.");
							$log->appendLogWithTypes($item, $formatType,
								$priorityType);
						}
						return false;	
					}
					else
						$this->_structureId[] = $matchedSchema;
					//match the partstructures
					foreach($ipartStructures as $ipartStructure) {
						if($ipartStructure->nodeName == "partStructure") {
							
							$ivaluesArray =& $ipartStructure->childNodes;
							if($ivaluesArray[0]->getText() != ""){
								$matchedId = $idManager->getId(
									$ivaluesArray[0]->getText());
							}else
								$matchedId = $this->getPartIdByName(
									$ivaluesArray[1]->getText(),
									$matchedSchema);
							if ($matchedId == false) {
								$this->addError("Part ".$ivaluesArray[1].
									" does not exist.");
								if (isset($log)) {
									$item =& new AgentNodeEntryItem(
										"ExifImporter Error", 
										"Part ".$ivaluesArray[1].
										" does not exist.");
									$log->appendLogWithTypes($item, $formatType,
										$priorityType);
								}
								return false;
							}
							$partStructuresArray[] = $matchedId;
							$valueArray = array();
							foreach($ivaluesArray as $ivalueField){
								if($ivalueField->nodeName == "value"){
									$ivaluesChildren = $ivalueField->childNodes;
									foreach($ivaluesChildren as $ivalue) {
										if($ivalue->nodeName == "exifElement")
										$valueArray[]="exif::".$ivalue->getText();
										if($ivalue->nodeName == "text")
										$valueArray[]="text::".$ivalue->getText();
									}
								}
								
							}							
							$valuesPreFinal[$matchedId->getIdString()] = $valueArray;
						}
						$this->_valuesFinal[$matchedSchema->getIdString()] = $valuesPreFinal;
					}
					$this->_partsFinal[$matchedSchema->getIdString()] = $partStructuresArray;
				}
			}
		}
		$recordList = array();
		$recordListElement = array();

		$headerData = get_jpeg_header_data($input);

		$fileMetaData1 =& $this->extractPhotoshopMetaData();
		$fileMetaData2 =& $this->extractExifMetaData($input);
		$fileMetaData = array_merge($fileMetaData1, $fileMetaData2);
		$recordListElement['structureId'] =& $this->_fileStructureId;
		$recordListElement['partStructureIds'] =& $this->_fileNamePartIds;
		$recordListElement['parts'] = array(basename($input), "");
		$recordList[] = $recordListElement;
		$recordListElement = array();
		foreach($this->_structureId as $structureId) {
			$parts = array();
			$recordListElement['structureId'] = $structureId;
			$recordListElement['partStructureIds'] = $this->_partsFinal[$structureId->getIdString()];
			$partValuesArray =& $this->_valuesFinal[$structureId->getIdString()];
			foreach($partValuesArray as $key=>$partsArray){
				$data = "";
				foreach($partsArray as $part){
					$checkExifField = explode("::", $part);
					if($checkExifField[0] == "exif"){
						if(isset($fileMetaData[$checkExifField[1]]))
							$data .= $fileMetaData[$checkExifField[1]];
					}
					else 
						$data .= $checkExifField[1];
				}
				$parts[] = $this->getPartObject($structureId, $idManager->getId($key), $data);
			
			}
			$recordListElement['parts'] = $parts;
			$recordList[] = $recordListElement;
		}
		//printpre($recordList);
		return $recordList;
	}

	/**
	 * get part id from the specified structure by name
	 * 
	 * @param string $partName
	 * @param object $structureId
	 * @return mixed partId
	 * @access public
	 * @since 8/11/05
	 */
	
	function getPartIdByName($partName, $structureId){
		$dr =& $this->_destinationRepository;
		$structure =& $dr->getRecordStructure($structureId);
		$parts = $structure->getPartStructures();
		while($parts->hasNext()){
			$part =& $parts->next();
			if($part->getDisplayName() == $partName)
				return $part->getId();
		}
		return false;
	}
	
	/**
	 * matches the given array with exif data in the file
	 * 
	 * @param array tagNameArray
	 * @return array
	 * @access public
	 * @since 7/28/05
	 */

	function extractPhotoshopMetaData(){
		$results = array();

		if($this->_photoshopIPTC) {
			foreach ($this->_photoshopIPTC as $array) {
				if(isset($results[$array['RecName']]))
					$results[$array['RecName']] = $results[$array['RecName']].", ".$array['RecData'];
				else $results[$array['RecName']] = $array['RecData'];
			}
		}
		return $results;
	}

	/**
	 * match given array with exifdata from file
	 * 
	 * @param string imageFileName
	 * @param array tagnameArray
	 * @return array
	 * @access public
	 * @since 7/28/05
	 */

	function extractExifMetadata ($imageFileName) {
		$metadataArrays = array();
		$metadataArrays[] = get_EXIF_JPEG($imageFileName);
		$metadataArrays[] = get_Meta_JPEG($imageFileName);
		$metadataArrays[] = get_EXIF_TIFF($imageFileName);

		$results = array();

		foreach ($metadataArrays as $metadataArray) {
			if (is_array($metadataArray)) {
				$exifArray = $metadataArray[0]['34665']['Data'][0];
				if (is_array($exifArray)) {
					foreach ($exifArray as $array) {
						$results[$array['Tag Name']] = $array['Text Value'];
					}
				}
			}
		}
		return $results;
	}


	/**
	 * get asset list for child assets
	 * 
	 * @param mixed input
	 * @return null
	 * @access public
	 * @since 7/20/05
	 */
	function &getChildAssetList (&$input) {
		return null;
	}
}

?>
