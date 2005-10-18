<?php
/**
 * @since 9/20/05
 * @package polyphony.exporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLRecordExporter.class.php,v 1.1 2005/10/18 13:36:20 cws-midd Exp $
 */ 

require_once(POLYPHONY."/main/library/Exporter/XMLPartExporter.class.php");

/**
 * Exports into XML for use with the XML Importer
 * 
 * @since 9/20/05
 * @package polyphony.exporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLRecordExporter.class.php,v 1.1 2005/10/18 13:36:20 cws-midd Exp $
 */
class XMLRecordExporter {
		
	/**
	 * Constructor
	 *
	 * 
	 * 
	 * @return <##>
	 * @access public
	 * @since 9/20/05
	 */
	function XMLRecordExporter (&$xmlFile) {
		$this->_xml =& $xmlFile;
		
		$this->_childExporterList = array("XMLPartExporter");
		$this->_childElementList = array("parts");
	}

	/**
	 * Exporter of All things
	 * 
	 * @return <##>
	 * @access public
	 * @since 9/26/05
	 */
	function export (&$record) {
		$this->_object =& $record;
		$this->_myId =& $this->_object->getId();

		$rS =& $this->_object->getRecordStructure();
		$rSId =& $rS->getId();
		
		fwrite($this->_xml,
"\t\t<record ".
"id=\"".$this->_myId->getIdString()."\" ".
"xml:id=\"".$rSId->getIdString()."\" ".
//isExisting?			
">\n");
		
		foreach ($this->_childElementList as $child) {
			$exportFn = "export".ucfirst($child);
			if (method_exists($this, $exportFn))
				$this->$exportFn();
		}
		
		fwrite($this->_xml,
"\t\t</record>\n");
	}

	/**
	 * Exporter of partstructures
	 * 
	 * Adds partstructure elements to the xml, which contain the necessary
	 * information to create the same partstructure.
	 * 
	 * @return <##>
	 * @access public
	 * @since 9/26/05
	 */
	function exportParts () {
		$children =& $this->_object->getParts();
		
		while ($children->hasNext()) {
			$child =& $children->next();
			
			$exporter =& new XMLPartExporter($this->_xml);
			
			$exporter->export($child); // ????
		}
	}

	/**
	 * <##>
	 * 
	 * @param <##>
	 * @return <##>
	 * @access public
	 * @since 9/26/05
	 */
	function <##> (<##>) {
		<##>
	}
	
}

?>