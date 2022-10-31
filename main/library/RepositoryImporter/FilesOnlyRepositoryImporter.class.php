<?php
/**
* @since 7/20/06
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: FilesOnlyRepositoryImporter.class.php,v 1.5 2007/09/19 14:04:48 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/RepositoryImporter.class.php");
/**
* <##>
 * 
 * @since 7/20/06
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: FilesOnlyRepositoryImporter.class.php,v 1.5 2007/09/19 14:04:48 adamfranco Exp $
 */
class FilesOnlyRepositoryImporter
	extends RepositoryImporter
{

	/**
	 * Constructor for FileOnlyRepositoryImporter	
	 * 
	 * @param String filename
	 * @return object
	 * @access public
	 * @since 7/20/05
	 */
	function __construct ($filepath, $repositoryId, $dieOnError=false) {
		$this->_assetIteratorClass = "ExifAssetIterator";
		parent::__construct($filepath, $repositoryId, $dieOnError);
	}

	/**
	 * Get parameters for createAsset
	 * 
	 * @param mixed input
	 * @return array
	 * @access public
	 * @since 7/20/05
	 */
	function getSingleAssetInfo ($input) {
		$assetInfo = array();

		$assetInfo['description'] = "";
		$assetInfo['displayName'] = basename($input);


		$mime = Services::getService("MIME");
		$mimeType = $mime->getMimeTypeForFileName(basename($input));
		$generalType = substr($mimeType, 0, strpos($mimeType, '/'));
		
		if ($generalType == "application" || !$generalType)
			$assetInfo['type'] = new HarmoniType("Asset Types", "edu.middlebury",
				"Generic Asset");
		else
			$assetInfo['type'] = new HarmoniType("Asset Types", "edu.middlebury",
				ucfirst($generalType));

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
	function getSingleAssetRecordList ($input) {
		if (Services::serviceRunning("Logging")) {
			$loggingManager = Services::getService("Logging");
			$log =$loggingManager->getLogForWriting("Harmoni");
			$formatType = new Type("logging", "edu.middlebury", 
				"AgentsAndNodes",
				"A format in which the acting Agent[s] and the target nodes affected are specified.");
			$priorityType = new Type("logging", "edu.middlebury", "Error",
							"Events involving critical system errors.");
		}			
		$idManager = Services::getService("Id");
		$this->_fileStructureId =$idManager->getId("FILE");
		$fileparts = array("File Name", "Thumbnail Data");
		$this->_fileNamePartIds = $this->matchPartStructures(
			$this->_destinationRepository->getRecordStructure(
			$this->_fileStructureId), $fileparts);

		$recordList = array();
		$recordListElement = array();
		$recordListElement['structureId'] =$this->_fileStructureId;
		$recordListElement['partStructureIds'] =$this->_fileNamePartIds;
		$recordListElement['parts'] = array($input, "");
		$recordList[] = $recordListElement;
		$recordListElement = array();
		//printpre($recordList);
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
	function getChildAssetList ($input) {
		return null;
	}
}