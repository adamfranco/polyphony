<?php
/**
 * @since 10/17/05
 * @package polyphony.exporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLRepositoryExporter.class.php,v 1.4 2005/10/20 18:33:38 cws-midd Exp $
 */ 

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
 * @version $Id: XMLRepositoryExporter.class.php,v 1.4 2005/10/20 18:33:38 cws-midd Exp $
 */
class XMLRepositoryExporter {
		
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
	function XMLRepositoryExporter (&$archive, $repDir) {
		$this->_archive =& $archive;
		$this->_repDir = $repDir;						
		
		$this->_childExporterList = array("XMLRecordStructureExporter", 
			"XMLAssetExporter");
		$this->_childElementList = array("recordstructures", "assets");
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

		$this->_xml =& fopen($this->_repDir.
			"/".$this->_myId->getIdString().".xml", "w");
		
		$this->_fileDir = $this->_repDir.
			"/".$this->_myId->getIdString()."_files";
		mkdir($this->_fileDir);
		
		fwrite($this->_xml,
"<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
"<repository id=\"".$this->_myId->getIdString()."\" ");
		if ($this->_myId->getIdString() ==
			"edu.middlebury.concerto.exhibition_repository")
			fwrite($this->_xml, "isExisting=\"TRUE\"");

		fwrite($this->_xml, ">\n".	
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
			array($this->_repDir."/".$this->_myId->getIdString().".xml"),
			"RepositoryDirectory/",
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

			$exporter =& new XMLAssetExporter($this->_archive, $this->_xml,
				$this->_fileDir);

			$exporter->export($child);
		}
	}
}
?>