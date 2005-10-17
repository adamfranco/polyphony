<?php
/**
 * @since 9/20/05
 * @package polyphony.exporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLRepositoryExporter.class.php,v 1.1 2005/10/17 20:45:31 cws-midd Exp $
 */ 

require_once(HARMONI."/Primitives/Chronology/DateAndTime.class.php");
require_once(POLYPHONY."/main/library/Exporter/XMLRecordStructureExporter.class.php");


/**
 * Exports into XML for use with the XML Importer
 * 
 * @since 9/20/05
 * @package polyphony.exporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLRepositoryExporter.class.php,v 1.1 2005/10/17 20:45:31 cws-midd Exp $
 */
class XMLRepositoryExporter {
		
	/**
	 * Constructor
	 *
	 * 
	 * 
	 * @return <##>
	 * @access public
	 * @since 9/20/05
	 */
	function XMLRepositoryExporter (&$archive, $repDir) {
		$this->_archive =& $archive;
		$this->_repDir = $repDir;						
		
		$this->_childExporterList = array("XMLRecordStructureExporter", 
			"XMLAssetExporter");
		$this->_childElementList = array("recordstructures", "assets");
	}

	/**
	 * Exporter of All things
	 * 
	 * @return <##>
	 * @access public
	 * @since 9/26/05
	 */
	function export (&$repId) {
		$this->_myId =& $repId;
		$rm =& Services::getService("Repository");
		
		$this->_object =& $rm->getRepository($this->_myId);
		$type =& $this->_object->getType();

		$this->_xml =& fopen($this->_repDir.
			"/".$this->_myId->getIdString().".xml");
		
		$this->_fileDir = $this->_repDir.
			"/".$this->_myId->getIdString()."_files";
		mkdir($this->_fileDir);
		
		fwrite($this->_xml,
"<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
"<repository id=\"".$this->_myId->getIdString()."\">\n".
"\t<name>".$this->_object->getDisplayName()."</name>\n".
"\t<description>".$this->_object->getDescription()."/<description>\n".
"\t<type>\n".
"\t\t<domain>".$type->getDomain()."</domain>\n".
"\t\t<authority>".$type->getAuthority()."</authority>\n".
"\t\t<keyword>".$type->getKeyword()."</keyword>\n");
		if ($type->getDescription() != "")
			fwrite($this->_xml,
"\t\t<description>".$type->getDescription()."</description>\n");
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

// ==================== ADD REPOSITORY XML TO ARCHIVE =======================//
//		$this->_archive->add(
	}

	/**
	 * Exporter of recordstructures
	 * 
	 * Adds recordstructure elements to the xml, which contain the necessary
	 * information to create the same recordstructure.
	 * 
	 * @return <##>
	 * @access public
	 * @since 9/26/05
	 */
	function exportRecordstructures () {
		$children =& $this->_object->getRecordStructures();
		
		while ($children->hasNext()) {
			$child =& $children->next();
			
			$exporter =& new XMLRecordStructureExporter($this->_xml);
			
			$exporter->export($child); // ????
		}
	}

	/**
	 * Exporter of Assets
	 * 
	 * @return <##>
	 * @access public
	 * @since 9/26/05
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

			$exporter =& new XMLAssetExporter($this->_archive, $this->_xml,
				$this->_fileDir);

			$exporter->export($child);
		}
	}
	
}

?>