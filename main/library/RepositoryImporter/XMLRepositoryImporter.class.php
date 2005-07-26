<?php
/**
* @since 7/20/05
 * @package polyphony.repositoryImporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLRepositoryImporter.class.php,v 1.8 2005/07/26 21:31:22 cws-midd Exp $
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
 * @version $Id: XMLRepositoryImporter.class.php,v 1.8 2005/07/26 21:31:22 cws-midd Exp $
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
	function XMLRepositoryImporter ($filepath, $repositoryId, $dieOnError = false) {
		$this->_assetIteratorClass = "XMLAssetIterator";
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
		$assetInfo['displayName'] = $input->childNodes[0]->getText();
		$assetInfo['description'] = $input->childNodes[1]->getText();
		$assetInfo['type'] = $input->childNodes[2]->getText();
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
	 * @return array or false on fatal error
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
			
				if(!$structureId) {
					$this->addError("The Schema: ".$record->getAttribute("schema")." does not exist in Repository: ".$this->_repositoryId);
					return $structureId;
				}
				$recordListElement['structureId'] = $structureId;
				$partArray = array();
				$parts = array();
				foreach ($record->childNodes as $field) {
					$partArray[] = $field->getAttribute("name");
					$parts[] = $field->getText();
				}
				
				$partStructureIds = RepositoryImporter::matchPartStructures(
					$this->_destinationRepository->getRecordStructure($structureId), $partArray);
				
				if(!$partStructureIds) {
					$this->addError("One or more of the Parts specified in the xml file for Schema: ".$record->getAttribute("schema")." are not valid.");
					return $partStructureIds;
				}
				$recordListElement['partStructureIds'] = $partStructureIds;
				$recordListElement['parts'] = $parts;
				$recordList[]=$recordListElement;
			}
			
		}
		return $recordList;
	}

	/**
	 * get asset list for child assets
	 * 
	 * @param mixed input
	 * @return array
	 * @access public
	 * @since 7/20/05
	 */
	function &getChildAssetList (&$input) {
		$iChildAssetList =& $input->childNodes;
		$childAssetList = array();
		foreach ($iChildAssetList as $asset) {
			if ($asset->nodeName == "asset") {
				$childAssetList[] =& $asset;
			}
		}
		return $childAssetList;
	}
}

?>