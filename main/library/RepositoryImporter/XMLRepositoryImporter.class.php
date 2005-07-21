<?php
/**
* @since 7/20/05
 * @package polyphony.repositoryImporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLRepositoryImporter.class.php,v 1.1 2005/07/21 13:59:46 cws-midd Exp $
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
 * @version $Id: XMLRepositoryImporter.class.php,v 1.1 2005/07/21 13:59:46 cws-midd Exp $
 */
class XMLRepositoryImporter
extends RepositoryImporter
{
	
	/**
	* Constructor for XML	
	 * 
	 * @param String filename
	 * @return object
	 * @access public
	 * @since 7/20/05
	 */
	function XMLRepositoryImporter ($filename, $repositoryId) {
		$this->import($filename, $repositoryId, "XML");
		$this->_assetIteratorClass = "XMLAssetIterator";
	}
	
	
	/**
	* Get single asset info
	 * 
	 * @param mixed input
	 * @return array
	 * @access public
	 * @since 7/20/05
	 */
	function &getSingleAssetInfo (& $input) {
		
		$assetInfo = array();
		$assetInfo[0] = $input->childNodes[0]->getText();
		if ($assetInfo[0] == "")
			$assetInfo[0] = "asset".$i;
		$assetInfo[1] = $input->childNodes[1]->getText();																// description for asset
		$assetInfo[2] = $input->childNodes[2]->getText();																	// type for asset, check for empty
		if ($assetInfo[2] == "")
			$assetInfo[2] = new HarmoniType("Asset Types", "Concerto", "Generic Asset");
		else
			$assetInfo[2] = new HarmoniType("Asset Types", "Concerto", $assetInfo[2]);
		
		return $assetInfo;
	}
	
	/**
	* get Single Asset Record List
	 * 
	 * @param mixed input
	 * @return array
	 * @access public
	 * @since 7/20/05
	 */
	function &getSingleAssetRecordList (&$input) {
		$iRecordList =& $input->childNodes;
		$recordList = array();
		foreach ($iRecordList as $record) {
			$recordListElement = array();
			if ($record->nodeName == "record") {
				$structureId = RepositoryImporter::matchSchema($record->getAttribute("schema"), $this->_destinationRepository);
				if(!$structureId)
					throwError(new Error("the schema does not exist", "polyphony.RepositoryImporter", true));
				$recordListElement[] = $structureId;
				$partArray = array();
				$parts = array();
				foreach ($record->childNodes as $field) {
					$partArray[] = $field->getAttribute("name");
					$parts[] = $field->getText();
				}
				$partStructureIds = RepositoryImporter::matchPartStructures($this->_destinationRepository->getRecordStructure($structureId), $partArray);
				
				if(!$partStructureIds)
					throwError(new Error("One or more of the Parts specified in the xml file are not valid.", "polyphony.RepositoryImporter", true));
				
				$recordListElement[] = $partStructureIds;
				$recordListElement[] = $parts;
				$recordList[]=$recordListElement;
			}
			
		}
		return $recordList;
	}
}

?>