<?php
/**
 * @since 10/17/05
 * @package polyphony.exporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLRecordStructureExporter.class.php,v 1.8 2007/09/19 14:04:45 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/Exporter/XMLPartStructureExporter.class.php");

/**
 * Exports into XML for use with the XML Importer
 * 
 * @since 10/17/05
 * @package polyphony.exporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLRecordStructureExporter.class.php,v 1.8 2007/09/19 14:04:45 adamfranco Exp $
 */
class XMLRecordStructureExporter {
		
	/**
	 * Constructor
	 *
	 * Maintains XML File
	 * 
	 * @param resource
	 * @access public
	 * @since 10/17/05
	 */
	function XMLRecordStructureExporter ($xmlFile) {
		$this->_xml =$xmlFile;
		
		$this->_childExporterList = array("XMLPartStructureExporter");
		$this->_childElementList = array("partstructures");
	}

	/**
	 * Exporter of All things
	 * 
	 * @param object HarmoniRecordStructure
	 * @access public
	 * @since 9/26/05
	 */
	function export ($rS) {
		$this->_object =$rS;
		$this->_myId =$this->_object->getId();
		$idString = $this->_myId->getIdString();
		

		fwrite($this->_xml,
"\t<recordstructure ".
"id=\"".$idString."\" ".
"xml:id=\"".$idString."\"");
		if (!ereg("^Repository", $idString))
			fwrite($this->_xml,
" isGlobal=\"TRUE\"");
		fwrite($this->_xml,
">\n".
"\t\t<name>".$this->_object->getDisplayName()."</name>\n".
"\t\t<description><![CDATA[".$this->_object->getDescription()."]]></description>\n".
"\t\t<format>".$this->_object->getFormat()."</format>\n");		
		
		foreach ($this->_childElementList as $child) {
			$exportFn = "export".ucfirst($child);
			if (method_exists($this, $exportFn))
				$this->$exportFn();
		}
		
		fwrite($this->_xml,
"\t</recordstructure>\n");
	}

	/**
	 * Exporter of partstructures
	 * 
	 * Adds partstructure elements to the xml, which contain the necessary
	 * information to create the same partstructure.
	 * 
	 * @access public
	 * @since 9/26/05
	 */
	function exportPartstructures () {
		$children =$this->_object->getPartStructures();
		
		while ($children->hasNext()) {
			$child =$children->next();
			
			$exporter = new XMLPartStructureExporter($this->_xml);
			
			$exporter->export($child); // ????
			unset($exporter);
		}
	}
}
?>