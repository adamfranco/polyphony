<?php
/**
* @since 7/20/05
 * @package polyphony.repositoryImporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ExifRepositoryImporter.class.php,v 1.1 2005/07/26 15:24:40 ndhungel Exp $
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
 * @version $Id: ExifRepositoryImporter.class.php,v 1.1 2005/07/26 15:24:40 ndhungel Exp $
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
	function ExifRepositoryImporter ($filepath, $repositoryId) {
		$this->_assetIteratorClass = "ExifAssetIterator";
		parent::RepositoryImporter($filepath, $repositoryId);
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
			$assetInfo['type'] = new HarmoniType("Asset Types", "Concerto", "Generic Asset");
		else
			$assetInfo['type'] = new HarmoniType("Asset Types", "Concerto", $assetInfo['type']);

		return $assetInfo;
	}
	
	/**
	 * get parameters for createRecord
	 * 
	 * @param mixed input
	 * @return array
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
		
			$this->_fileStructureId = RepositoryImporter::matchSchema(
				"File", $this->_destinationRepository);
			$fleparts = array("File Name", "Thumbnail Data");
			$this->_fileNamePartIds = RepositoryImporter::matchPartStructures(
				$this->_destinationRepository->getRecordStructure($this->_fileStructureId),
				$fleparts);
				
			$this->_structureId = RepositoryImporter::matchSchema(
				$schema, $this->_destinationRepository);
			
			if ($this->_structureId == false)
				throwError(new Error("Schema <emph>".$schema.
					"</emph> does not exist in the collection", "polyphony.RepositoryImporter", true));
					
			$this->_partStructureIds = RepositoryImporter::matchPartStructures(
				$this->_destinationRepository->getRecordStructure($this->_structureId), $this->_partArray);
			if (!$this->_partStructureIds)
				throwError(new Error("Schema part does not exist", "polyphony.RepositoryImporter", true));
		}
		
		$recordList = array();
		$recordListElement = array();
		
		$headerData = get_jpeg_header_data($input);
		
		$fileMetaData1 =& ExifRepositoryImporter::extractPhotoshopMetaData($this->_tagNameArray);
		printpre($fileMetaData1);
		$fileMetaData2 =& ExifRepositoryImporter::extractExifMetaData($input, $this->_tagNameArray);
		$fileMetaData = array_merge($fileMetaData1, $fileMetaData2);
		$parts = array();
		foreach($this->_tagNameArray as $tag)
			if(isset($fileMetaData[$tag]))
				$parts[] = $fileMetaData[$tag];
			else $parts[] = "";
		$recordListElement['structureId'] =& $this->_fileStructureId;
		$recordListElement['partStructureIds'] =& $this->_fileNamePartIds;
		$recordListElement['parts'] = array(basename($input), "");
		$recordList[] = $recordListElement;
		$recordListElement['structureId'] =& $this->_structureId;
		$recordListElement['partStructureIds'] =& $this->_partStructureIds;
		$recordListElement['parts'] = $parts;
		$recordList[] =& $recordListElement;
		return $recordList;

	}
	
	function extractPhotoshopMetaData($tagNameArray){
		$results = array();
		
		if($this->_photoshopIPTC) {
			foreach ($this->_photoshopIPTC as $array) {
				if(in_array($array['RecName'], $tagNameArray))
					$results[$array['RecName']] = $array['RecData'];
			}
		}
		printpre($results);
		return $results;
	}
	
	function extractExifMetadata ($imageFileName, &$tagNameArray) {
		$metadataArrrays = array();
		$metadataArrrays[] = get_EXIF_JPEG($imageFileName);
		$metadataArrrays[] = get_Meta_JPEG($imageFileName);
		$metadataArrrays[] = get_EXIF_TIFF($imageFileName);

		$results = array();
	
		foreach ($metadataArrrays as $metadataArray) {
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
	function &getChildAssetList (&$input) {
		return null;
	}
}

?>