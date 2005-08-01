<?php
/**
* @since 7/20/05
 * @package polyphony.repositoryImporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ExifRepositoryImporter.class.php,v 1.6 2005/08/01 17:30:40 ndhungel Exp $
 */ 

require_once(dirname(__FILE__)."/RepositoryImporter.class.php");
require_once("/home/afranco/public_html/PHP_JPEG_Metadata_Toolkit_1.11/EXIF.php");

/**
* <##>
 * 
 * @since 7/20/05
 * @package polyphony.repositoryImporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ExifRepositoryImporter.class.php,v 1.6 2005/08/01 17:30:40 ndhungel Exp $
 */
class ExifRepositoryImporter
	extends RepositoryImporter
{
	
	/**
	 * Constructor for ExifImpoerter	
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
			$assetInfo['type'] = new HarmoniType("Asset Types", "Concerto",
				"Generic Asset");
		else
			$assetInfo['type'] = new HarmoniType("Asset Types", "Concerto",
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
		if (!isset($this->_structureId)) {
			$meta = fopen($this->_srcDir."/schema.txt", "r");
			$schema = ereg_replace("[\n\r]*$","",fgets($meta));
			$this->_partArray = array();
			$this->_tagNameArray = array();
			while($titleline = ereg_replace("[\n\r]*$", "", fgets($meta))){
				$titlelineParts = explode("\t", $titleline);
				$this->_partArray[] = $titlelineParts[0];
				$this->_tagNameArray[] = $titlelineParts[1];
			}
			fclose($meta);
		
			$this->_fileStructureId = $this->matchSchema(
				"File", $this->_destinationRepository);
			$fileparts = array("File Name", "Thumbnail Data");
			$this->_fileNamePartIds = $this->matchPartStructures(
				$this->_destinationRepository->getRecordStructure(
				$this->_fileStructureId), $fileparts);
				
			$this->_structureId = $this->matchSchema(
				$schema, $this->_destinationRepository);
			
			if (!$this->_structureId) {
				$this->addError("The schema: ".$schema.
					" does not exist in repository: ".$this->_repositoryId->getIdString());
				return $this->_structureId;
			}
					
			$this->_partStructureIds = $this->matchPartStructures(
				$this->_destinationRepository->getRecordStructure(
				$this->_structureId), $this->_partArray);
			if (!$this->_partStructureIds) {
				$this->addError("One or more of the Parts specified in the Exif file for Schema: ".
					$schema." are not valid.");
				return $this->_partStructureIds;
			}
		}
		
		$recordList = array();
		$recordListElement = array();
		
		$headerData = get_jpeg_header_data($input);
		
		$fileMetaData1 =& $this->extractPhotoshopMetaData($this->_tagNameArray);
		printpre($fileMetaData1);
		$fileMetaData2 =& $this->extractExifMetaData($input,
			$this->_tagNameArray);
		$fileMetaData = array_merge($fileMetaData1, $fileMetaData2);
		$parts = array();
		foreach($this->_tagNameArray as $tag)
			if(isset($fileMetaData[$tag]))
				$parts[] = $fileMetaData[$tag];
			else $parts[] = "";

		$partObjects = array();
		for ($i = 0; $i < count($this->_partStructureIds); $i++) {
			$partObject = $this->getPartObject($this->_structureId,
				$this->_partStructureIds[$i], $parts[$i]);
			if (!$partObject)
				return $partObject; // false
			$partObjects[] = $partObject;
		}

		$recordListElement['structureId'] =& $this->_fileStructureId;
		$recordListElement['partStructureIds'] =& $this->_fileNamePartIds;
		$recordListElement['parts'] = array(basename($input), "");
		$recordList[] = $recordListElement;
		$recordListElement['structureId'] =& $this->_structureId;
		$recordListElement['partStructureIds'] =& $this->_partStructureIds;
		$recordListElement['parts'] = $partObjects;
		$recordList[] =& $recordListElement;
		return $recordList;

	}
	
	/**
	 * matches the given array with exif data in the file
	 * 
	 * @param array tagNameArray
	 * @return array
	 * @access public
	 * @since 7/28/05
	 */
	
	function extractPhotoshopMetaData($tagNameArray){
		$results = array();
		
		if($this->_photoshopIPTC) {
			foreach ($this->_photoshopIPTC as $array) {
				if(in_array($array['RecName'], $tagNameArray))
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
	
	function extractExifMetadata ($imageFileName, &$tagNameArray) {
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
						if(in_array($array['Tag Name'], $tagNameArray))
							$results[$array['Tag name']] = $array['Text Value'];
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