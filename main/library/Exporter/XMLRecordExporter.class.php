<?php
/**
 * @since 10/17/05
 * @package polyphony.exporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLRecordExporter.class.php,v 1.9 2007/09/19 14:04:45 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/Exporter/XMLPartExporter.class.php");

/**
 * Exports into XML for use with the XML Importer
 * 
 * @since 10/17/05
 * @package polyphony.exporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLRecordExporter.class.php,v 1.9 2007/09/19 14:04:45 adamfranco Exp $
 */
class XMLRecordExporter {
		
	/**
	 * Constructor
	 *
	 * Maintains the xmlfile
	 * 
	 * @param resource
	 * @access public
	 * @since 10/17/05
	 */
	function __construct ($xmlFile) {
		$this->_xml =$xmlFile;
		
		$this->_childExporterList = array("XMLPartExporter");
		$this->_childElementList = array("parts");
	}

	/**
	 * Exporter of All things
	 * 
	 * @param object HarmoniRecord
	 * @access public
	 * @since 10/17/05
	 */
	function export ($record) {
		$this->_object =$record;
		$this->_myId =$this->_object->getId();

		$rS =$this->_object->getRecordStructure();
		$rSId =$rS->getId();
		
		fwrite($this->_xml,
"\t\t<record ".
"id=\"".$this->_myId->getIdString()."\" ".
"xml:id=\"".$rSId->getIdString()."\">\n");
		
		foreach ($this->_childElementList as $child) {
			$exportFn = "export".ucfirst($child);
			if (method_exists($this, $exportFn))
				$this->$exportFn();
		}
		
		fwrite($this->_xml,
"\t\t</record>\n");
	}

	/**
	 * Exporter of parts
	 * 
	 * Adds part elements to the xml, which contain the necessary
	 * information to create the same part.
	 * 
	 * @access public
	 * @since 10/17/05
	 */
	function exportParts () {
		$children =$this->_object->getParts();
		
		while ($children->hasNext()) {
			$child =$children->next();
			$exporter = new XMLPartExporter($this->_xml);
			$exporter->export($child); // ????
			unset($exporter);
		}
	}	
}
?>