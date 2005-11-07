<?php
/**
 * @since 10/17/05
 * @package polyphony.exporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLExporter.class.php,v 1.6 2005/11/07 15:40:38 cws-midd Exp $
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
 * @version $Id: XMLExporter.class.php,v 1.6 2005/11/07 15:40:38 cws-midd Exp $
 */
class XMLExporter {
		
	/**
	 * Constructor
	 * 
	 * @access public
	 * @since 10/17/05
	 */
	function XMLExporter () {
		$this->setupSelf();
	}

	/**
	 * Creates the child lists
	 * 
	 * @access public
	 * @since 10/31/05
	 */
	function setupSelf () {
		$this->_childExporterList = array("XMLRepositoryExporter");/*, "XMLSetImporter", "XMLHierarchyImporter", "XMLGroupImporter", "XMLAgentImporter");*/
		$this->_childElementList = array("repositories", "sets", "hierarchy", 
			"groups", "agents");
	}

	/**
	 * Initializes with an archive for the export
	 * 
	 * @param string
	 * @access public
	 * @since 10/31/05
	 */
	function &withCompression ($compression, $class = 'XMLExporter') {
		if (!(strtolower($class) == strtolower('XMLExporter')
			|| is_subclass_of(new $class, 'XMLExporter')))
		{
			die("Class, '$class', is not a subclass of 'XMLExporter'.");
		}
		$exporter =& new $class;
		$now =& DateAndTime::now();
		$exporter->_tmpDir = "/tmp/export_".$now->asString();
		while (file_exists($exporter->_tmpDir)) {
			$now =& DateAndTime::now();
			$exporter->_tmpDir = "/tmp/export_".$now->asString();
		}		
		
		mkdir($exporter->_tmpDir);
		
		$exporter->_compression = $compression;
		
		$exporter->_archive =& new Archive_Tar(
			$exporter->_tmpDir.$exporter->_compression, 
			$exporter->getTarKey($exporter->_compression));
			
		return $exporter;
	}

	/**
	 * finds the appropriate key for Archive_Tar
	 * 
	 * @param string
	 * @return string
	 * @access public
	 * @since 10/31/05
	 */
	function getTarKey ($extension) {
		switch ($extension) {
			case ".tar.gz":
				return "gz";
			default :
				return "gz";
		}
	}

	/**
	 * Exporter of All things
	 * 
	 * @access public
	 * @since 10/17/05
	 */
	function exportAll () {
		$this->setupXML();
		
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
		
		return $this->_tmpDir.$this->_compression;
	}

	/**
	 * creates xmlfile and initializes it
	 * 
	 * @access public
	 * @since 10/31/05
	 */
	function setupXML () {
		$this->_xml =& fopen($this->_tmpDir."/metadata.xml", "w");
		fwrite($this->_xml,
"<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
"<import>\n");
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
		
		$children =& $rm->getRepositories();
		while ($children->hasNext()) {
			$child =& $children->next();
			$childId =& $child->getId();
			// define subdir
			$repDir = $this->_tmpDir."/".$childId->getIdString();
			mkdir($repDir);
			
			fwrite($this->_xml, "\t<repositoryfile>".$childId->getIdString().
				"/metadata.xml</repositoryfile>\n");
			
			$exporter =& XMLRepositoryExporter::withArchive($this->_archive,
				$repDir);
			
			$exporter->export($childId); // ????
			unset($exporter);
		}
	}
}
?>