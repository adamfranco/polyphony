<?php
/**
* @since 7/20/05
 * @package polyphony.repositoryImporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: TabRepositoryImporter.class.php,v 1.7 2005/07/26 21:31:22 cws-midd Exp $
 */ 

require_once(dirname(__FILE__)."/RepositoryImporter.class.php");

/**
* <##>
 * 
 * @since 7/20/05
 * @package polyphony.repositoryImporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: TabRepositoryImporter.class.php,v 1.7 2005/07/26 21:31:22 cws-midd Exp $
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
	function TabRepositoryImporter ($filepath, $repositoryId, $dieOnError = false) {
		$this->_assetIteratorClass = "TabAssetIterator";
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
	function &getSingleAssetInfo (&$input) {
		$assetInfo = array();
		$assetInfo['displayName'] = $input[0];
		$assetInfo['description'] = $input[1];
		if($input[2] == "")
			$assetInfo['type'] = new HarmoniType("Asset Types", "Concerto", "Generic Asset");
		else
			$assetInfo['type'] = new HarmoniType("Asset Types", "Concerto", $input[2]);

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
	function &getSingleAssetRecordList (&$input) {
		if (!isset($this->_structureId)) {
			$meta = fopen($this->_srcDir."/metadata.txt", "r");
			$schema = ereg_replace("[\n\r]*$","",fgets($meta));
			$titleline = ereg_replace("[\n\r]*$", "", fgets($meta));
			fclose($meta);
			
			$this->_fileStructureId = RepositoryImporter::matchSchema(
				"File", $this->_destinationRepository);
						
			$this->_filenamePartId = RepositoryImporter::matchPartStructures(
				$this->_destinationRepository->getRecordStructure($this->_fileStructureId),
				array("File Name", "Thumbnail Data"));
			
			$this->_structureId = RepositoryImporter::matchSchema(
				$schema, $this->_destinationRepository);
		
			if (!$this->_structureId) {
				$this->addError("The schema: ".$record->getAttribute("schema")." does not exist in repository: ".$this->_repositoryId);
				return $this->_structureId;
			}

			$titles = explode ("\t", $titleline);
			$partArray = array_slice($titles, 5);
			$this->_partStructureIds = RepositoryImporter::matchPartStructures(
				$this->_destinationRepository->getRecordStructure($this->_structureId), $partArray);
			
			if (!$this->_partStructureIds) {
				$this->addError("One or more of the Parts specified in the xml file for Schema: ".$record->getAttribute("schema")." are not valid.");
				return $this->_partStructureIds;
			}
		}

		$recordList = array();

		if ($input[3] != "") {
			$fileElement = array();
			$fileElement['structureId'] =& $this->_fileStructureId;
			$fileElement['partStructureIds'] =& $this->_filenamePartId;
			$interestingArray[] = $input[3];
			//$fileElement['parts'] = array($input[3]);
						
			if ($input[4] != "") {
				$interestingArray[] = array($input[4]);
			}
			$fileElement['parts'] = $interestingArray;
			$recordList[] =& $fileElement;
		}
		
		$recordListElement = array();
		$recordListElement['structureId'] =& $this->_structureId;
		$recordListElement['partStructureIds'] =& $this->_partStructureIds;
		$recordListElement['parts'] = array_slice($input, 5);
		$recordList[] =& $recordListElement;
		return $recordList;
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