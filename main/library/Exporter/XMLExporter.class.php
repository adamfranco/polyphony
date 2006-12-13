<?php
/**
 * @since 10/17/05
 * @package polyphony.library.exporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLExporter.class.php,v 1.10 2006/12/13 15:29:31 adamfranco Exp $
 */ 

require_once("Archive/Tar.php");
require_once(HARMONI."/Primitives/Chronology/DateAndTime.class.php");
require_once(POLYPHONY."/main/library/Exporter/XMLRepositoryExporter.class.php");

/**
 * Exports into XML for use with the XML Importer
 * 
 * @since 10/17/05
 * @package polyphony.library.exporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLExporter.class.php,v 1.10 2006/12/13 15:29:31 adamfranco Exp $
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
	 * Initializes the export by creating a dir in /tmp/
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
		$this->setupXML($this->_tmpDir);
		fwrite($this->_xml, "<import>\n");
		
		foreach ($this->_childElementList as $child) {
			$exportFn = "export".ucfirst($child);
			if (method_exists($this, $exportFn))
				$this->$exportFn();
		}

		fwrite($this->_xml, "</import>");
		fclose($this->_xml);

		return $this->_tmpDir;
	}

	/**
	 * creates xmlfile and initializes it
	 * 
	 * @param string $dir the directory in which the xml file resides
	 * @access public
	 * @since 10/31/05
	 */
	function setupXML ($dir) {
		$this->_xml = fopen($dir."/metadata.xml", "w");
		fwrite($this->_xml, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
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
		$file = fopen("/tmp/memoryUsage.txt", "w");
		$children =& $rm->getRepositories();
		while ($children->hasNext()) {
			$child =& $children->next();
			$childId =& $child->getId();
			
			fwrite($this->_xml, "\t<repositoryfile>".$childId->getIdString().
				"/metadata.xml</repositoryfile>\n");
			
			$exporter =& XMLRepositoryExporter::withDir($this->_tmpDir, $file);
			
			$exporter->export($childId); // ????
			unset($exporter);
		}
	}
	
	/**
	 * Compress a directory with status stars. Return the resulting file path
	 * 
	 * @return string the new archive path.
	 * @static
	 * @access public
	 * @since 12/12/06
	 */
	function compressWithStatus () {
		$archiveBaseName = "export_".md5(time()." ".rand());
		
		// Get the number of files in the directory and initialize the status stars
		$status = new StatusStars(_("Compressing the archive"));
		$numFiles = numFiles($this->_tmpDir);
		$status->initializeStatistics($numFiles);
		$filesSeen = 1;
		
		
		$PID = run_in_background(
			'tar -v -czf /tmp/'.$archiveBaseName.$this->_compression.
			" -C ".str_replace(":", "\:", $this->_tmpDir)." . ",
			0, str_replace(":", "\:", $this->_tmpDir)."-compress_status");
		
		while(is_process_running($PID))	{
			$lines = count(file($this->_tmpDir."-compress_status"));
			for ($i = $filesSeen; $i < $lines; $i++) {
				$status->updateStatistics();
				$filesSeen++;
			}
			sleep(1);
		}
		// Finish off any last statistics
		for ($i = $filesSeen; $i <= $numFiles; $i++)
			$status->updateStatistics();
		
		// Remove our status file
		unlink($this->_tmpDir."-compress_status");
		
		// Remove the source directory
		shell_exec('rm -R '.str_replace(":", "\:", $this->_tmpDir));
		
		return '/tmp/'.$archiveBaseName.$this->_compression;
	}
}
?>
