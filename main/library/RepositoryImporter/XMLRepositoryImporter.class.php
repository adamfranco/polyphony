<?php
/**
* @since 7/20/05
 * @package polyphony.repositoryImporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLRepositoryImporter.class.php,v 1.16 2005/09/28 19:13:24 cws-midd Exp $
 */ 
require_once(DOMIT);

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
 * @version $Id: XMLRepositoryImporter.class.php,v 1.16 2005/09/28 19:13:24 cws-midd Exp $
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
	function XMLRepositoryImporter ($filepath, $repositoryId, $dieOnError=false) {
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
	function &getSingleAssetInfo (&$input) {
		$assetInfo = array();
		$assetInfo['displayName'] = $input->childNodes[0]->getText();
		$assetInfo['description'] = $input->childNodes[1]->getText();
		$assetInfo['type'] = $input->childNodes[2]->getText();
		if ($assetInfo['type'] == "")
			$assetInfo['type'] = new HarmoniType("Asset Types",
				"edu.middlebury.concerto", "Generic Asset");
		else
			$assetInfo['type'] = new HarmoniType("Asset Types",
				"edu.middlebury.concerto", $assetInfo['type']);

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
				$structureId = $this->matchSchema(
					$record->getAttribute("schema"),
					$this->_destinationRepository);
				if(!$structureId) {
					$this->addError("The Schema: ".
						$record->getAttribute("schema").
						" does not exist in Repository: ".
						$this->_repositoryId->getIdString());
					return $structureId;
				}
				$recordListElement['structureId'] = $structureId;

				$partArray = array();
				$parts = array();
				foreach ($record->childNodes as $field) {
					$partArray[] = $field->getAttribute("name");
					$parts[] = $field->getText();
				}

				$partStructureIds = $this->matchPartStructures(
					$this->_destinationRepository->getRecordStructure(
					$structureId), $partArray);
				if(!$partStructureIds) {
					$this->addError("One or more of the Parts specified in the xml file for Schema: ".
						$record->getAttribute("schema")." are not valid.");
					return $partStructureIds; //false
				}
				
				if ("File" == $record->getAttribute("schema")) {
					for ($i = 0; $i < count($partArray); $i++) {
						if (("File Name" == $partArray[$i]) &&
							!(is_file($this->_srcDir.
							trim($parts[$i])))) {
							$this->addError("File: ".$this->srcDir.
								trim($parts[$i]).
								" does not exist for import.");
							$false = false;
							return $false;
						}
					}
					$recordListElement['parts'] =& $parts;
				}
				else {
					$partObjects = array();
					for ($i = 0; $i < count($partStructureIds); $i++) {
						$partObject = $this->getPartObject($structureId,
							$partStructureIds[$i], $parts[$i]);
						if (!$partObject)
							return $partObject; // false
						$partObjects[] = $partObject;
					}
					$recordListElement['parts'] =& $partObjects;
				}
				$recordListElement['partStructureIds'] = $partStructureIds;
				$recordList[] = $recordListElement;
				unset($recordListElement);
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
				$childAssetList[] = $asset;
			}
		}
		return $childAssetList;
	}

	/**
	 * get whether or not to build ordered set from children
	 * 
	 * @param mixed input
	 * @return array
	 * @access public
	 * @since 7/20/05
	 */
	function &getBuildOrderedSet (&$input) {
		$buildOrderedSet = $input->getAttribute("buildOrderedSet");

		return $buildOrderedSet;
	}
}

?>
