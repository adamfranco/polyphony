<?php
/**
* @since 7/20/05
 * @package polyphony.repositoryImporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: TabRepositoryImporter.class.php,v 1.2 2005/07/21 16:13:09 cws-midd Exp $
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
 * @version $Id: TabRepositoryImporter.class.php,v 1.2 2005/07/21 16:13:09 cws-midd Exp $
 */
class TabRepositoryImporter
extends RepositoryImporter
{
	
	/**
	* Constructor for Tab	
	 * 
	 * @param String filename
	 * @return object
	 * @access public
	 * @since 7/20/05
	 */
	function TabRepositoryImporter ($filename, $repositoryId) {
		$this->_assetIteratorClass = "TabAssetIterator";
		$this->import($filename, $repositoryId);
	}
	
	
	/**
	* Get Single Asset info
	 * 
	 * @param mixed input
	 * @return array
	 * @access public
	 * @since 7/20/05
	 */
	function &getSingleAssetInfo (&$input) {
		$assetInfo = array();
		$assetInfo[0] = $input[0];
		$assetInfo[1] = $input[1];
		if($input[2] == "")
			$assetInfo[2] = new HarmoniType("Asset Types", "Concerto", "Generic Asset");
		else
			$assetInfo[2] = new HarmoniType("Asset Types", "Concerto", $input[2]);

		return $assetInfo;
	}
	
	
	/**
	* Get single asset recordlist
	 * 	
	 * @param mixed inpu	
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
			
			//$filenamePartArray = array("File Name");
			
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
			$fileElement[] =& $this->_fileStructureId;
			$fileElement[] =& $this->_filenamePartId;
			$fileElement[] = array($input[3]);
			$recordList[] =& $fileElement;
		}
			
		$recordListElement = array();
		$recordListElement[] =& $this->_structureId;
		$recordListElement[] =& $this->_partStructureIds;
		$recordListElement[] = array_slice($input, 4);
		$recordList[] =& $recordListElement;
		return $recordList;
	}
	
}

?>