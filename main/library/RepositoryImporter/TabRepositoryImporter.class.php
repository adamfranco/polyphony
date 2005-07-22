<?php
/**
* @since 7/20/05
 * @package polyphony.repositoryImporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: TabRepositoryImporter.class.php,v 1.4 2005/07/22 13:07:33 cws-midd Exp $
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
 * @version $Id: TabRepositoryImporter.class.php,v 1.4 2005/07/22 13:07:33 cws-midd Exp $
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
	function TabRepositoryImporter ($filepath, $repositoryId) {
		$this->_assetIteratorClass = "TabAssetIterator";
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
	 * @return array
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
				array("File Name"));
			
			$this->_structureId = RepositoryImporter::matchSchema(
				$schema, $this->_destinationRepository);
		
			if ($this->_structureId == false)
				throwError(new Error("Schema <emph>".$schema.
					"</emph> does not exist in the collection", "polyphony.RepositoryImporter", true));
		
			$titles = explode ("\t", $titleline);
			$partArray = array_slice($titles, 4);
			$this->_partStructureIds = RepositoryImporter::matchPartStructures(
				$this->_destinationRepository->getRecordStructure($this->_structureId), $partArray);
			
			if (!$this->_partStructureIds)
				throwError(new Error("Schema part does not exist", "polyphony.RepositoryImporter", true));
		}

		$recordList = array();

		if ($input[3] != "") {
			$fileElement = array();
			$fileElement['structureId'] =& $this->_fileStructureId;
			$fileElement['partStructureIds'] =& $this->_filenamePartId;
			$fileElement['parts'] = array($input[3]);
			$recordList[] =& $fileElement;
		}
			
		$recordListElement = array();
		$recordListElement['structureId'] =& $this->_structureId;
		$recordListElement['partStructureIds'] =& $this->_partStructureIds;
		$recordListElement['parts'] = array_slice($input, 4);
		$recordList[] =& $recordListElement;
		return $recordList;
	}
	
}

?>