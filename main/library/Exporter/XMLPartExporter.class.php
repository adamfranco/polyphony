<?php
/**
 * @since 10/17/05
 * @package polyphony.exporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLPartExporter.class.php,v 1.3 2005/11/03 21:13:15 cws-midd Exp $
 */ 

/**
 * Exports into XML for use with the XML Importer
 * 
 * @since 10/17/05
 * @package polyphony.exporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLPartExporter.class.php,v 1.3 2005/11/03 21:13:15 cws-midd Exp $
 */
class XMLPartExporter {
		
	/**
	 * Constructor
	 *
	 * Maintains the xml file.
	 * 
	 * @param resource
	 * @access public
	 * @since 10/17/05
	 */
	function XMLPartExporter (&$xmlFile) {
		$this->_xml =& $xmlFile;
		
		$this->_childExporterList = null;
		$this->_childElementList = null;
	}

	/**
	 * Exporter of All things
	 * 
	 * @param object HarmoniPart
	 * @access public
	 * @since 10/17/05
	 */
	function export (&$part) {
		$this->_object =& $part;
		$this->_myId =& $this->_object->getId();

		$pS =& $this->_object->getPartStructure();
		$pSId =& $pS->getId();
		$partValue =& $this->_object->getValue();

		fwrite($this->_xml,
"\t\t\t<part ".
"id=\"".$this->_myId->getIdString()."\" ".
"xml:id=\"".$pSId->getIdString()."\">".
"<![CDATA[".$partValue->asString()."]]></part>\n");

	}
}
?>