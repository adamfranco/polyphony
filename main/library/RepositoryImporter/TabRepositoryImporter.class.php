<?php
/**
* @since 7/20/05
 * @package polyphony.repositoryImporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: TabRepositoryImporter.class.php,v 1.1 2005/07/21 13:59:46 cws-midd Exp $
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
 * @version $Id: TabRepositoryImporter.class.php,v 1.1 2005/07/21 13:59:46 cws-midd Exp $
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
		$this->import($filename, $repositoryId, "Tab-Delimited");
		$this->_assetIteratorClass = "TabAssetIterator";
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
		if($input[0]=="")
			$assetInfo[0] = "asset".$i;
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
		
		
			$this->_structureId = RepositoryImporter::matchSchema(
				$schema, $this->_destinationRepository);
		
			if ($this->_structureId == false)
				throwError(new Error("Schema <emph>".$schema.
					"</emph> does not exist in the collection", "polyphony.RepositoryImporter", true));
		
			$titles = explode ("\t", $titleline);
			$titlesSliced = array_slice($titles, 4);
			$this->_partStructureIds = RepositoryImporter::matchPartStructures(
				$this->_destinationRepository->getRecordStructure($this->_structureId), $titlesSliced);
			
			if (!$this->_partStructureIds)
				throwError(new Error("Schema part does not exist", "polyphony.RepositoryImporter", true));
		}
	
		$recordListElement = array();
		$recordList = array();
		$recordListElement[] =& $this->_structureId;
		$recordListElement[] =& $this->_partStructureIds;
		$recordListElement[] = array_slice($input, 4);
		$recordList[] = $recordListElement;
		return $recordList;
	}
	
}

?>