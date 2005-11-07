<?php
/**
 * @since 10/17/05
 * @package polyphony.exporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLRepositoryExporter.class.php,v 1.6 2005/11/07 15:40:38 cws-midd Exp $
 */ 

require_once(POLYPHONY."/main/library/Exporter/XMLExporter.class.php");
require_once(POLYPHONY."/main/library/Exporter/XMLRecordStructureExporter.class.php");
require_once(POLYPHONY."/main/library/Exporter/XMLAssetExporter.class.php");

/**
 * Exports into XML for use with the XML Importer
 * 
 * @since 10/17/05
 * @package polyphony.exporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLRepositoryExporter.class.php,v 1.6 2005/11/07 15:40:38 cws-midd Exp $
 */
class XMLRepositoryExporter extends XMLExporter {
		
	/**
	 * Constructor
	 *
	 * Maintains archive and repository directory.
	 * 
	 * @param object Archive_Tar
	 * @param string
	 * @access public
	 * @since 10/17/05
	 */
	function XMLRepositoryExporter() {	
		parent::XMLExporter();
	}

	/**
	 * Creates the child lists
	 * 
	 * @access public
	 * @since 10/31/05
	 */
	function setupSelf() {
		$this->_childExporterList = array("XMLRecordStructureExporter", 
			"XMLAssetExporter");
		$this->_childElementList = array("recordstructures", "assets");
	}

	/*
	 * Constructor for adding repository to an export
	 *
	 * @param object Archive_Tar
	 * @param string
	 * @access public
	 * @since 10/31/05
	 */
	function &withArchive(&$archive, $repDir) {
		$exporter =& new XMLRepositoryExporter();
		$exporter->_archive =& $archive;
		$exporter->_repDir = $repDir;	
		
		return $exporter;
	}

	/**
	 * Constructor for starting an export
	 *
	 * @param string
	 * @param string
	 * @access public
	 * @since 10/31/05
	 */
	function &withCompression($compression, $class = 'XMLRepositoryExporter') {
		return parent::withCompression($compression, $class);
	}
	
	function exportAll(&$repId) {
		$this->_repDir = $this->_tmpDir."/".$repId->getIdString();
		mkdir($this->_repDir);
		$this->export($repId);
		return $this->_tmpDir.$this->_compression;
	}
		
	
	/**
	 * Exporter of Repository things
	 * 
	 * 
	 * @param object HarmoniId
	 * @access public
	 * @since 10/17/05
	 */
	function export (&$repId) {
		$rm =& Services::getService("Repository");
		
		$this->_myId =& $repId;
		$this->_object =& $rm->getRepository($this->_myId);
		$type =& $this->_object->getType();

		$this->_xml =& fopen($this->_repDir."/metadata.xml", "w");
				
		fwrite($this->_xml,
"<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
"<repository id=\"".$this->_myId->getIdString()."\">\n".	
"\t<name>".$this->_object->getDisplayName()."</name>\n".
"\t<description><![CDATA[".$this->_object->getDescription()."]]></description>\n".
"\t<type>\n".
"\t\t<domain>".$type->getDomain()."</domain>\n".
"\t\t<authority>".$type->getAuthority()."</authority>\n".
"\t\t<keyword>".$type->getKeyword()."</keyword>\n");
		if ($type->getDescription() != "")
			fwrite($this->_xml,
"\t\t<description><![CDATA[".$type->getDescription()."]]></description>\n");
		fwrite($this->_xml,
"\t</type>\n");
		
		// recordStructures
		foreach ($this->_childElementList as $child) {
			$exportFn = "export".ucfirst($child);
			if (method_exists($this, $exportFn))
				$this->$exportFn();
		}

		fwrite($this->_xml, 
"</repository>\n");

		fclose($this->_xml);
// ==================== ADD REPOSITORY XML TO ARCHIVE =======================//
		$this->_archive->addModify(
			array($this->_repDir."/metadata.xml"),
			$this->_myId->getIdString(),
			$this->_repDir);
	}

	/**
	 * Exporter of recordstructures
	 * 
	 * Adds recordstructure elements to the xml, which contain the necessary
	 * information to create the same recordstructure.
	 * 
	 * @access public
	 * @since 10/17/05
	 */
	function exportRecordstructures () {
		$children =& $this->_object->getRecordStructures();
		
		while ($children->hasNext()) {
			$child =& $children->next();
			
			$exporter =& new XMLRecordStructureExporter($this->_xml);
			
			$exporter->export($child); // ????
			unset($exporter);
		}
	}

	/**
	 * Exporter of Assets
	 * 
	 * @access public
	 * @since 10/17/05
	 */
	function exportAssets () {
		$hasRootSearch = FALSE;
		$rootSearchType =& new HarmoniType("Repository",
			"edu.middlebury.harmoni", "RootAssets", "");
		$searchTypes =& $this->_object->getSearchTypes();
		while ($searchTypes->hasNext()) {
			if ($rootSearchType->isEqual( $searchTypes->next() )) {
				$hasRootSearch = TRUE;
				break;
			}
		}
		if ($hasRootSearch) {
			$criteria = NULL;
			$children =& $this->_object->getAssetsBySearch($criteria,
				$rootSearchType, $searchProperties = NULL);
		} else
			$children =& $this->_object->getAssets();

		while ($children->hasNext()) {
			$child =& $children->next();

			$exporter =& XMLAssetExporter::withArchive($this->_archive,
				$this->_xml, $this->_repDir);

			$exporter->export($child);
			unset($exporter);
		}
	}
}
?>