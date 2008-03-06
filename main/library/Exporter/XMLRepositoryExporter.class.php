<?php
/**
 * @since 10/17/05
 * @package polyphony.exporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLRepositoryExporter.class.php,v 1.14 2008/03/06 19:03:21 adamfranco Exp $
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
 * @version $Id: XMLRepositoryExporter.class.php,v 1.14 2008/03/06 19:03:21 adamfranco Exp $
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

	/**
	 * Constructor for starting an export
	 *
	 * @param string
	 * @param string
	 * @access public
	 * @since 10/31/05
	 * @static
	 */
	static function withCompression($compression, $class = 'XMLRepositoryExporter') {
		return parent::withCompression($compression, $class);
	}	

	/**
	 * Constructor for in an export
	 *
	 * @param string
	 * @param string
	 * @access public
	 * @since 10/31/05
	 * @static
	 */
	static function withDir($tmpDir, $class = 'XMLRepositoryExporter') {
		$exporter = new $class;
		$exporter->_tmpDir = $tmpDir;
		return $exporter;
	}	
	
	/**
	 * Exporter of Repository things
	 * 
	 * 
	 * @param object HarmoniId
	 * @access public
	 * @since 10/17/05
	 */
	function export ($repId) {
	// ===== CREATE THE DIRECTORY FOR THIS OBJECT ===== //
		$this->_repDir = $this->_tmpDir."/".$repId->getIdString();
		mkdir($this->_repDir);

	// ===== SELF KNOWLEDGE ===== //
		$this->_myId =$repId;
		$rm = Services::getService("Repository");
		$this->_object =$rm->getRepository($this->_myId);
		$type =$this->_object->getType();

	// ===== SELF EXPORT ===== //
		$this->setupXML($this->_repDir);				
		fwrite($this->_xml,
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
		
	// ===== CHILD CLASSES ARE EXPORTED HERE ===== //		
		foreach ($this->_childElementList as $child) {
			$exportFn = "export".ucfirst($child);
			if (method_exists($this, $exportFn)) {
				$this->$exportFn();
			}
		}

	// ===== CLOSE SELF XML ===== //
		fwrite($this->_xml, "</repository>\n");
		fclose($this->_xml);

	// ===== RETURN THE NAME OF THE ARCHIVE (important when top level) ===== //
		return $this->_tmpDir;
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
		$children =$this->_object->getRecordStructures();
		while ($children->hasNext()) {
			$child =$children->next();
			$exporter = new XMLRecordStructureExporter($this->_xml);
			$exporter->export($child); // ????
		}
		unset($children, $child, $exporter);
	}

	/**
	 * Exporter of Assets
	 * 
	 * @access public
	 * @since 10/17/05
	 */
	function exportAssets () {
		$hasRootSearch = FALSE;
		$rootSearchType = new HarmoniType("Repository",
			"edu.middlebury.harmoni", "RootAssets", "");
		$searchTypes =$this->_object->getSearchTypes();
		while ($searchTypes->hasNext()) {
			if ($rootSearchType->isEqual( $searchTypes->next() )) {
				$hasRootSearch = TRUE;
				break;
			}
		}
		if ($hasRootSearch) {
			$criteria = NULL;
			$children =$this->_object->getAssetsBySearch($criteria,
				$rootSearchType, new HarmoniProperties(new Type('null', 'null', 'null')));
		} else
			$children =$this->_object->getAssets();
		
		$this->_status = new StatusStars(str_replace('%1', $this->_object->getDisplayName(), _("Exporting all Assets in the Collection, '%1'")));
		$this->_status->initializeStatistics($children->count(), 100);
		
		while ($children->hasNext()) {
			$child =$children->next();
			$exporter = XMLAssetExporter::withDir($this->_xml, $this->_repDir);
			$exporter->export($child);
			$this->_status->updateStatistics();	
		}
			unset($exporter);
	}
}
?>