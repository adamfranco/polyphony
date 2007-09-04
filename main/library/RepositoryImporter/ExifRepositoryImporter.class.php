<?php
/**
* @since 7/20/05
 * @package polyphony.library.repository_importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ExifRepositoryImporter.class.php,v 1.23 2007/09/04 20:28:01 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/RepositoryImporter.class.php");
require_once(EXIF);
require_once(DOMIT);
/**
* <##>
 * 
 * @since 7/20/05
 * @package polyphony.library.repository_importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ExifRepositoryImporter.class.php,v 1.23 2007/09/04 20:28:01 adamfranco Exp $
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
	function getSingleAssetInfo ($input) {
		$assetInfo = array();

		$headerData = get_jpeg_header_data($input);
		$photoshopIRB = get_Photoshop_IRB($headerData);
		$this->_photoshopIPTC = get_Photoshop_IPTC($photoshopIRB);
		
// 		printpre($this->_photoshopIPTC);
		$assetInfo['description'] = "";
		$assetInfo['displayName'] = "";
		foreach ($this->_photoshopIPTC as $array) {
			switch ($array['RecName']) {
				case 'Caption/Abstract':
				case 'description':
					$assetInfo['description'] = $array['RecData'];
					break;
				case "Object Name (Title)":
				case "title":
					$assetInfo['displayName'] = $array['RecData'];
			}
		}
// 		printpre($assetInfo);
// 		exit;
		
		$mime = Services::getService("MIME");
		$mimeType = $mime->getMimeTypeForFileName(basename($input));
		$generalType = substr($mimeType, 0, strpos($mimeType, '/'));
		
		if ($generalType == "application" || !$generalType)
			$assetInfo['type'] = new HarmoniType("Asset Types", "edu.middlebury",
				"Generic Asset");
		else
			$assetInfo['type'] = new HarmoniType("Asset Types", "edu.middlebury",
				ucfirst($generalType));

		return $assetInfo;
	}
	
	/**
	 * Answer the path of the schema.xml file
	 * 
	 * @param string $dirName
	 * @return string
	 * @access public
	 * @since 12/7/06
	 */
	function getSchemaPath ($dirName) {
		$dirName = preg_replace('/\/$/', '', $dirName).'/';
		if (file_exists($dirName."schema.xml"))
			return $dirName."schema.xml";
		
		$dir = opendir($dirName);
		while($file = readdir($dir)) {
			if ($file != '.' && $file != '..' && is_dir($dirName.$file)) {
				$result = $this->getSchemaPath($dirName.$file);
				if ($result) {
					closedir($dir);
					return $result;
				}				
			}
		}
		closedir($dir);
		return false;
	}

	/**
	 * get parameters for createRecord
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
			$formatType = new Type("logging", "edu.middlebury", 
				"AgentsAndNodes",
				"A format in which the acting Agent[s] and the target nodes affected are specified.");
			$priorityType = new Type("logging", "edu.middlebury", "Error",
							"Events involving critical system errors.");
		}			
		$idManager = Services::getService("Id");
		$this->_fileStructureId =$idManager->getId("FILE");
		$fileparts = array("File Name", "Thumbnail Data");
		$this->_fileNamePartIds = $this->matchPartStructures(
			$this->_destinationRepository->getRecordStructure(
			$this->_fileStructureId), $fileparts);
		if (!isset($this->_structureId)) {
			$import = new DOMIT_Document();
			
			if (!isset($this->_schemaPath)) {
				$this->_schemaPath = $this->getSchemaPath($this->_srcDir);
				if (!$this->_schemaPath)
					$this->_schemaPath = DEFAULT_EXIF_SCHEMA;
			}
			
			if ($import->loadXML($this->_schemaPath)) {
				if (!($import->documentElement->hasChildNodes())){
					$this->addError("There are no schemas defined in : ".
						$this->_schemaPath);
					if (isset($log)) {
						$item = new AgentNodeEntryItem("ExifImporter Error",
							"There are no schemas defined in: ".
							$this->_schemaPath);
						$log->appendLogWithTypes($item, $formatType, 
							$priorityType);
					}
					return false;
				}
			}
			else {
				$this->addError("XML parse failed: ".$this->_schemaPath." does not exist or contains poorly formed XML.");
				if (isset($log)) {
					$item = new AgentNodeEntryItem("ExifImporter DOMIT Error",
						"XML parse failed: ".$this->_schemaPath." does not exist or contains poorly formed XML.");
					$log->appendLogWithTypes($item, $formatType, $priorityType);
				}
				return false;
			}
			$istructuresList =$import->documentElement->childNodes;
			$this->_structureId = array();
			$this->_partsFinal = array();
			$this->_valuesFinal = array();
			foreach($istructuresList as $istructure) {
				$valuesPreFinal = array();
				$partStructuresArray = array();
				if($istructure->nodeName == "recordStructure") {
					//match the structure
					$ipartStructures =$istructure->childNodes;
					if($ipartStructures[0]->getText() != ""){
						$matchedSchema = $idManager->getId($ipartStructures[0]->getText());
					} else
						$matchedSchema = $this->matchSchema($ipartStructures[1]->getText(), $this->_destinationRepository);
					if($matchedSchema == false) {
						$this->addError("Schema: ".
							$ipartStructures[1]->getText()." does not exist");
						if (isset($log)) {
							$item = new AgentNodeEntryItem("ExifImporter 
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
							
							$ivaluesArray =$ipartStructure->childNodes;
							if($ivaluesArray[0]->getText() != ""){
								$matchedId = $idManager->getId(
									$ivaluesArray[0]->getText());
							}else
								$matchedId = $this->getPartIdByName(
									$ivaluesArray[1]->getText(),
									$matchedSchema);
							if ($matchedId == false) {
								$this->addError("Part ".$ivaluesArray[1]->getText().
									" does not exist.");
								if (isset($log)) {
									$item = new AgentNodeEntryItem(
										"ExifImporter Error", 
										"Part ".$ivaluesArray[1].
										" does not exist.");
									$log->appendLogWithTypes($item, $formatType,
										$priorityType);
								}
								return false;
							}
							$partStructuresArray[] = $matchedId;
							$repeatableValueArray = array();
							foreach($ivaluesArray as $ivalueField){
								if($ivalueField->nodeName == "value"){
									$valueArray = array();
									$ivaluesChildren = $ivalueField->childNodes;
									foreach($ivaluesChildren as $ivalue) {
										if($ivalue->nodeName == "exifElement")
										$valueArray[]="exif::".$ivalue->getText();
										if($ivalue->nodeName == "text")
										$valueArray[]="text::".$ivalue->getText();
									}
									$repeatableValueArray[] = $valueArray;
								}
							}							
							$valuesPreFinal[$matchedId->getIdString()] = $repeatableValueArray;
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

		$fileMetaData1 = $this->extractPhotoshopMetaData();
		$fileMetaData2 = $this->extractExifMetaData($input);
		$fileMetaData = array_merge($fileMetaData1, $fileMetaData2);
		$recordListElement['structureId'] =$this->_fileStructureId;
		$recordListElement['partStructureIds'] =$this->_fileNamePartIds;
		$recordListElement['parts'] = array($input, "");
		$recordList[] = $recordListElement;
		$recordListElement = array();
		foreach($this->_structureId as $structureId) {
			$parts = array();
			$recordListElement['structureId'] = $structureId;
			$recordListElement['partStructureIds'] = $this->_partsFinal[$structureId->getIdString()];
			
			$partValuesArray =$this->_valuesFinal[$structureId->getIdString()];
			foreach($partValuesArray as $key=>$repeatablePartsArray){
				$partValues = array();
				foreach($repeatablePartsArray as $partsComponentsArray) {
					// If we have a single entry in the value field, create 
					// multiple part values for any repeated source values.
					if (count($partsComponentsArray) == 1) {
						$checkExifField = explode("::", $partsComponentsArray[0]);
						// An Exif Value
						if($checkExifField[0] == "exif") {
							if(isset($fileMetaData[$checkExifField[1]])) {
								//multi-valued source values
								if (is_array($fileMetaData[$checkExifField[1]])) {
									foreach($fileMetaData[$checkExifField[1]] as $sourceValue)
										$partValues[] = $this->getPartObject($structureId, $idManager->getId($key), $sourceValue);
								} 
								// Single-valued source values
								else
									$partValues[] = $this->getPartObject($structureId, $idManager->getId($key), $fileMetaData[$checkExifField[1]]);
							}
						}
						// Text/other data
						else 
							$partValues[] = $this->getPartObject($structureId, $idManager->getId($key), $checkExifField[1]);
					} 
					// If we are concatenating several fields, concatanate
					// any repeated source values
					else {
						$data = "";
						foreach($partsComponentsArray as $partComponent){
							$checkExifField = explode("::", $partComponent);
							// An Exif Value
							if($checkExifField[0] == "exif"){
								if(isset($fileMetaData[$checkExifField[1]])) {
									if (is_array($fileMetaData[$checkExifField[1]]))
										$data .= implode(", ", $fileMetaData[$checkExifField[1]]);
									else
										$data .= $fileMetaData[$checkExifField[1]];
								}
							}
							// Text/other data
							else 
								$data .= $checkExifField[1];
						}
						$partValues[] = $this->getPartObject($structureId, $idManager->getId($key), $data);
					}
				}
				$parts[] = $partValues;
			}
			$recordListElement['parts'] = $parts;
			$recordList[] = $recordListElement;
		}
// 		printpre($recordList);
// 		exit;
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
		$dr =$this->_destinationRepository;
		$structure =$dr->getRecordStructure($structureId);
		$parts = $structure->getPartStructures();
		while($parts->hasNext()){
			$part =$parts->next();
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
				switch ($array['RecName']) {
					case 'Province/State':
					case 'Country/Primary Location Name':
						$values = explode(", ", $array['RecData']);
						$results[$array['RecName']] = $values[0];
						break;
					default:
						// build an array if multiple values exist
						if(isset($results[$array['RecName']])) {
							// if it is not already an array, create an array and add
							// the current value.
							if (!is_array($results[$array['RecName']])) {
								$tmp = $results[$array['RecName']];
								$results[$array['RecName']] = array();
								$results[$array['RecName']][] = $tmp;
							}
							
							$results[$array['RecName']][] = $array['RecData'];
							
						// Just use the string
						} else 
							$results[$array['RecName']] = $array['RecData'];					
				}
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
						if ($array['Tag Name'] && $array['Text Value'])
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
	function getChildAssetList ($input) {
		$null = null;
		return $null;
	}
}

?>
