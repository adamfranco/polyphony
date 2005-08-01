<?php
/**
* @since 7/20/05
 * @package polyphony.repositoryImporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: TabRepositoryImporter.class.php,v 1.10 2005/08/01 20:06:54 adamfranco Exp $
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
 * @version $Id: TabRepositoryImporter.class.php,v 1.10 2005/08/01 20:06:54 adamfranco Exp $
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
	function TabRepositoryImporter ($filepath, $repositoryId, $dieOnError=false) {
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
			$assetInfo['type'] = new HarmoniType("Asset Types", "edu.middlebury.concerto",
				"Generic Asset");
		else
			$assetInfo['type'] = new HarmoniType("Asset Types", "edu.middlebury.concerto",
				$input[2]);

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
			
			$this->_fileStructureId = $this->matchSchema("File",
				$this->_destinationRepository);
			
			$checkPartsArray = array("File Name","Thumbnail Data");
			$this->_filePartIds = $this->matchPartStructures(
				$this->_destinationRepository->getRecordStructure(
				$this->_fileStructureId), $checkPartsArray);			

			$this->_structureId = $this->matchSchema($schema,
				$this->_destinationRepository);
		
			if (!$this->_structureId) {
				$this->addError("The schema: ".$schema.
					" does not exist in repository: ".$this->_repositoryId->getIdString());
				return $this->_structureId; // false
			}

			$titles = explode ("\t", $titleline);
			$partArray = array_slice($titles, 5);
			$this->_partStructureIds = $this->matchPartStructures(
				$this->_destinationRepository->getRecordStructure(
				$this->_structureId), $partArray);
			
			if (!$this->_partStructureIds) {
				$this->addError("One or more of the Parts in the Tab-Delimited file for Schema: ".
					$schema." are not valid.");
				return $this->_partStructureIds; // false
			}
		}

		$recordList = array();

		if ($input[3] != "") {
			if (is_file($this->_srcDir."/".$input[3])) {
				$fileElement = array();
				
				$fileElement['structureId'] =& $this->_fileStructureId;
				$fileElement['partStructureIds'] =& $this->_filePartIds;
				$fileElementParts[] = $input[3];
							
				if ($input[4] != "") {
					$fileElementParts[] = $input[4];
				}
				$fileElement['parts'] = $fileElementParts;
				$recordList[] =& $fileElement;
			}
			else {
				$this->_addError("File: ".$this->srcDir."/".$input[3].
					" does not exist for import.");
				$false = false;
				return $false;
			}
		}

		$partObjects = array();
		for ($i = 0; $i < count($this->_partStructureIds); $i++) {
			$partObject = $this->getPartObject($this->_structureId,
				$this->_partStructureIds[$i], $input[$i + 5]);
			if (!$partObject)
				return $partObject; // false
			$partObjects[] = $partObject;
		}
			
		$recordListElement = array();
		$recordListElement['structureId'] =& $this->_structureId;
		$recordListElement['partStructureIds'] =& $this->_partStructureIds;
		$recordListElement['parts'] = $partObjects;
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