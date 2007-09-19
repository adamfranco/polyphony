<?php
/**
 * @since 10/17/05
 * @package polyphony.exporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLAssetExporter.class.php,v 1.11 2007/09/19 14:04:44 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/Exporter/XMLExporter.class.php");
require_once(POLYPHONY."/main/library/Exporter/XMLRecordExporter.class.php");
require_once(POLYPHONY."/main/library/Exporter/XMLFileRecordExporter.class.php");
require_once(POLYPHONY."/main/library/Exporter/XMLRemoteFileRecordExporter.class.php");

/**
 * Exports into XML for use with the XML Importer
 * 
 * @since 10/17/05
 * @package polyphony.exporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLAssetExporter.class.php,v 1.11 2007/09/19 14:04:44 adamfranco Exp $
 */
class XMLAssetExporter extends XMLExporter {
		
	/**
	 * Constructor
	 *
	 * @access public
	 * @since 10/17/05
	 */
	function XMLAssetExporter () {
		parent::XMLExporter();
	}

	/**
	 * Creates the child lists
	 * 
	 * @access public
	 * @since 10/31/05
	 */
	function setupSelf() {
		$this->_childExporterList = array("XMLRecordExporter", 
			"XMLAssetExporter");
		$this->_childElementList = array("records", "assets");
	}

	/**
	 * Constructor for starting an export
	 *
	 * @param string
	 * @param string
	 * @access public
	 * @since 10/31/05
	 */
	function withCompression($compression, $class = 'XMLAssetExporter') {
		return parent::withCompression($compression, $class);
	}	


	/*
	 * Constructor for adding repository to an export
	 *
	 * @param handle $xmlFile filehandle for the xml file
	 * @param string $fileDir the directory for the files to be written
	 * @access public
	 * @since 10/31/05
	 */
	function withDir($xmlFile, $fileDir) {
		$exporter = new XMLAssetExporter();
		$exporter->_xml =$xmlFile;
		$exporter->_fileDir = $fileDir;			
		
		return $exporter;
	}

	/**
	 * Exporter of Asset things
	 * 
	 * @param object HarmoniAsset
	 * @access public
	 * @since 10/17/05
	 */
	function export ($asset) {
		$this->_object =$asset;
		$this->_myId =$this->_object->getId();
		$type =$this->_object->getAssetType();

		fwrite($this->_xml,
"\t<asset ".
"id=\"".$this->_myId->getIdString()."\">\n".
"\t\t<name>".$this->_object->getDisplayName()."</name>\n".
"\t\t<description><![CDATA[".$this->_object->getDescription()."]]></description>\n".
"\t\t<type>\n\t\t\t<domain>".$type->getDomain()."</domain>\n".
"\t\t\t<authority>".$type->getAuthority()."</authority>\n".
"\t\t\t<keyword>".$type->getKeyword()."</keyword>\n");
		if ($type->getDescription() != "")
			fwrite($this->_xml,
"\t\t\t<description><![CDATA[".$type->getDescription()."]]></description>\n");
		fwrite($this->_xml,
"\t\t</type>\n");

//================== DATES GO HERE ===================//

		foreach ($this->_childElementList as $child) {
			$exportFn = "export".ucfirst($child);
			if (method_exists($this, $exportFn))
				$this->$exportFn();
		}
		
		fwrite($this->_xml, "\t</asset>\n");
	}
	
	/**
	 * Exporter of a list of assets, for selective asset exporting
	 * 
	 * @param array $idList a list of asset id's
	 * @return string $file is the directory where all the data is written
	 * @access public
	 * @since 12/13/05
	 */
	function exportList ($idList) {
		$harmoni = Harmoni::Instance();
		$repositoryManager = Services::getService("Repository");
		$listOfRS = array();
		$idListRS = array();
		$this->_fileDir = $this->_tmpDir;
		// prepare all the things we need to export
		foreach ($idList as $assetId) {
			$asset =$repositoryManager->getAsset($assetId);

			// build the list of recordstructures	
			$this->buildRSList($asset, $idListRS, $listOfRS);
		}

		// get the xml file ready
		$this->setupXML($this->_fileDir);
		fwrite($this->_xml, "<repository>\n");

		// export the necessary recordstructures
		foreach ($listOfRS as $rS) {
			$exporter = new XMLRecordStructureExporter($this->_xml);
			$exporter->export($rS);
			unset($exporter);
		}
		
		// clean up after the recordstructures
		unset($listOfRS, $idListRS);
		
		// export the assets
		$this->_status = new StatusStars(_("Exporting all Assets in the Collection"));
		$this->_status->initializeStatistics(count($idList), 100);
		
		foreach ($idList as $assetId) {
			$asset =$repositoryManager->getAsset($assetId);
			
			$this->export($asset);
			
			$this->_status->updateStatistics();	
		}
		fwrite($this->_xml, "</repository>");
		fclose($this->_xml);
		
		return $this->_tmpDir;
	}

	/**
	 * Builds a list of the necessary record structures to ex/import the asset
	 * 
	 * @param object HarmoniAsset $asset an asset that is being exporter
	 * @param array $listOfRS an array of all the necessary RecordStructures1
	 * @access public
	 * @since 12/13/05
	 */
	function buildRSList ($asset, $idListRS, $listOfRS) {
		$iterator =$asset->getRecordStructures();
		
		// go through RS's find those with different Id's
		while ($iterator->hasNext()) {
			$rS =$iterator->next();
			$id =$rS->getId();
			$idString = $id->getIdString();
			// new recordstructure add it to the list			
			if (!in_array($idString, $idListRS)) {
				$idListRS[] = $idString;
				$listOfRS[] =$rS;
			}
		}
		// recurse through the children
		$children =$asset->getAssets();
		while($children->hasNext()) {
			$child =$children->next();
			$this->buildRSList($child, $idListRS, $listOfRS);
		}
	}

	/**
	 * Exporter of records
	 * 
	 * Adds record elements to the xml, which contain the necessary
	 * information to create the same records.
	 * 
	 * @access public
	 * @since 10/17/05
	 */
	function exportRecords () {
		$idManager = Services::getService("Id");
		$children =$this->_object->getRecords();
		
		while ($children->hasNext()) {
			$child =$children->next();
			$rS =$child->getRecordStructure();
			if ($rS->getId() == $idManager->getId("FILE")) {
				$exporter = new XMLFileRecordExporter($this->_xml, 
					$this->_fileDir);
			} else if ($rS->getId() == $idManager->getId("REMOTE_FILE")) {
				$exporter = new XMLRemoteFileRecordExporter($this->_xml, 
					$this->_fileDir);
			} else 
				$exporter = new XMLRecordExporter($this->_xml);
			$exporter->export($child); // ????

			unset($exporter);
		}
	}

	/**
	 * Exporter of child Assets
	 * 
	 * @access public
	 * @since 10/17/05
	 */
	function exportAssets () {
		$children =$this->_object->getAssets();

		while ($children->hasNext()) {
			$child =$children->next();

			$exporter = XMLAssetExporter::withDir($this->_xml,
				$this->_fileDir);

			$exporter->export($child);
			unset($exporter);
		}
	}
}
?>