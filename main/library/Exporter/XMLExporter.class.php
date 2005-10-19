<?php
/**
 * @since 10/17/05
 * @package polyphony.exporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLExporter.class.php,v 1.3 2005/10/19 18:56:40 cws-midd Exp $
 */ 

require_once("Archive/Tar.php");
require_once(HARMONI."/Primitives/Chronology/DateAndTime.class.php");
require_once(POLYPHONY."/main/library/Exporter/XMLRepositoryExporter.class.php");

/**
 * Exports into XML for use with the XML Importer
 * 
 * @since 10/17/05
 * @package polyphony.exporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLExporter.class.php,v 1.3 2005/10/19 18:56:40 cws-midd Exp $
 */
class XMLExporter {
		
	/**
	 * Constructor
	 *
	 * Makes a directory in the /tmp directory to store the export.
	 * Creates the archive that will be given to the end user.
	 * 
	 * @access public
	 * @since 10/17/05
	 */
	function XMLExporter () {
		$now =& DateAndTime::now();
		$this->_tmpDir = "/tmp/export_".$now->asString();
		while (file_exists($this->_tmpDir)) {
			$now =& DateAndTime::now();
			$this->_tmpDir = "/tmp/export_".$now->asString();
		}		
		
		mkdir($this->_tmpDir);
		
		$this->_archive =& new Archive_Tar($this->_tmpDir.".tar.gz", "gz");
		
		$this->_childExporterList = array("XMLRepositoryExporter");/*, "XMLSetImporter", "XMLHierarchyImporter", "XMLGroupImporter", "XMLAgentImporter");*/
		$this->_childElementList = array("repositories", "sets", "hierarchy", 
			"groups", "agents");
	}

	/**
	 * Exporter of All things
	 * 
	 * @access public
	 * @since 10/17/05
	 */
	function exportAll () {
		$this->_xml =& fopen($this->_tmpDir."/metadata.xml", "w");
		fwrite($this->_xml,
"<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
"<import>\n");
		
		foreach ($this->_childElementList as $child) {
			$exportFn = "export".ucfirst($child);
			if (method_exists($this, $exportFn))
				$this->$exportFn();
		}
		fwrite($this->_xml,
"</import>");

		fclose($this->_xml);
		$this->_archive->addModify(
			array($this->_tmpDir."/metadata.xml"),
			 "", $this->_tmpDir);
			
		printpre($this->_archive->listContent());
		exit();
	}

	/**
	 * Exporter of repositories
	 * 
	 * Adds repositoryfile elements to the xml, which pass off to individual
	 * repository Importers.
	 * 
	 * @return <##>
	 * @access public
	 * @since 10/17/05
	 */
	function exportRepositories () {
		$rm =& Services::getService("Repository");
		
		// define subdir
		$this->_repDir = $this->_tmpDir."/RepositoryDirectory";
		mkdir($this->_repDir);
		
		$children =& $rm->getRepositories();
		while ($children->hasNext()) {
			$child =& $children->next();
			$childId =& $child->getId();
			
			fwrite($this->_xml, "\t<repositoryfile>".$this->_repDir."/".
				$childId->getIdString().".xml</repositoryfile>\n");
			
			$exporter =& new XMLRepositoryExporter($this->_archive,
				$this->_repDir);
			
			$exporter->export($childId); // ????
		}
	}
}
?>