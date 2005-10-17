<?php
/**
 * @since 9/20/05
 * @package polyphony.exporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLPartExporter.class.php,v 1.1 2005/10/17 20:45:31 cws-midd Exp $
 */ 

/**
 * Exports into XML for use with the XML Importer
 * 
 * @since 9/20/05
 * @package polyphony.exporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLPartExporter.class.php,v 1.1 2005/10/17 20:45:31 cws-midd Exp $
 */
class XMLPartExporter {
		
	/**
	 * Constructor
	 *
	 * 
	 * 
	 * @return <##>
	 * @access public
	 * @since 9/20/05
	 */
	function XMLPartExporter (&$xmlFile) {
		$this->_xml =& $xmlFile;
		
		$this->_childExporterList = null;
		$this->_childElementList = null;
	}

	/**
	 * Exporter of All things
	 * 
	 * @return <##>
	 * @access public
	 * @since 9/26/05
	 */
	function export (&$part) {
		$this->_object =& $part;
		$this->_myId =& $this->_object->getId();

		$pS =& $this->_object->getPartStructure();
		$pSId =& $pS->getId();

		fwrite($this->_xml,
"\t\t\t<part ".
"id=\"".$this->_myId->getIdString()."\" ".
"xml:id=\"".$pSId->getIdString()."\" ".
//isExisting?			
">".$this->_object->getValue()."</part>\n");

	}
}
?>