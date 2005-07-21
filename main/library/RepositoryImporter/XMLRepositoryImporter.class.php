<?php
/**
* @since 7/20/05
 * @package polyphony.repositoryImporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLRepositoryImporter.class.php,v 1.3 2005/07/21 18:36:22 ndhungel Exp $
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
 * @version $Id: XMLRepositoryImporter.class.php,v 1.3 2005/07/21 18:36:22 ndhungel Exp $
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
		$this->_assetIteratorClass = "XMLAssetIterator";
		$this->import($filename, $repositoryId);
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
		$assetInfo['displayName'] = $input->childNodes[0]->getText();
		$assetInfo['description'] = $input->childNodes[1]->getText();
		$assetInfo['type'] = $input->childNodes[2]->getText();
		if ($assetInfo['type'] == "")
			$assetInfo['type'] = new HarmoniType("Asset Types", "Concerto", "Generic Asset");
		else
			$assetInfo['type'] = new HarmoniType("Asset Types", "Concerto", $assetInfo[2]);
		
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
		$iRecordList =& $input->childNodes;
		$recordList = array();
		foreach ($iRecordList as $record) {
			$recordListElement = array();
			if ($record->nodeName == "record") {
				$structureId = RepositoryImporter::matchSchema(
					$record->getAttribute("schema"), $this->_destinationRepository);
			
				if(!$structureId)
					throwError(new Error("the schema does not exist", "polyphony.RepositoryImporter", true));
				
				$recordListElement['structureId'] = $structureId;
				$partArray = array();
				$parts = array();
				foreach ($record->childNodes as $field) {
					$partArray[] = $field->getAttribute("name");
					$parts[] = $field->getText();
				}
				
				$partStructureIds = RepositoryImporter::matchPartStructures(
					$this->_destinationRepository->getRecordStructure($structureId), $partArray);
				
				if(!$partStructureIds)
					throwError(new Error("One or more of the Parts specified in the xml file are not valid.", "polyphony.RepositoryImporter", true));
				
				$recordListElement['partStructureIds'] = $partStructureIds;
				$recordListElement['parts'] = $parts;
				$recordList[]=$recordListElement;
			}
		}
		return $recordList;
	}
}

?>