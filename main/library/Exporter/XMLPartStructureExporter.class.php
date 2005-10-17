<?php
/**
 * @since 9/20/05
 * @package polyphony.exporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLPartStructureExporter.class.php,v 1.1 2005/10/17 20:45:31 cws-midd Exp $
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
 * @version $Id: XMLPartStructureExporter.class.php,v 1.1 2005/10/17 20:45:31 cws-midd Exp $
 */
class XMLPartStructureExporter {
		
	/**
	 * Constructor
	 *
	 * creates a new instantiation of the partstructure exporter
	 * 
	 * @return <##>
	 * @access public
	 * @since 10/17/05
	 */
	function XMLPartStructureExporter (&$xmlFile) {
		$this->_xml =& $xmlFile;
		
		$this->_childExporterList = null;
		$this->_childElementList = null;
	}

	/**
	 * Exports the object given and children if available
	 * 
	 * @return <##>
	 * @access public
	 * @since 9/26/05
	 */
	function export (&$pS) {
		$this->_object =& $pS;
		$this->_myId =& $this->_object->getId();
		
		$type =& $this->getType();

		fwrite($this->_xml,
"\t\t<partstructure ".
"id=\"".$this->_myId->getIdString()."\" ".
"xml:id=\"".$this->_myId->getIdString()."\" ".
"isMandatory=\"".(($this->_object->isMandatory())?"TRUE":"FALSE")."\" ".
"isRepeatable=\"".(($this->_object->isRepeatable())?"TRUE":"FALSE")."\" ".
"isPopulated=\"".(($this->_object->isPopulated())?"TRUE":"FALSE")."\" ".	
// isExisting?			
">\n".
"\t\t\t<name>".$this->_object->getDisplayName()."</name>\n".
"\t\t\t<description>".$this->_object->getDescription()."/<description>\n".
"\t\t\t<type>\n\t\t\t\t<domain>".$type->getDomain()."</domain>\n".
"\t\t\t\t<authority>".$type->getAuthority()."</authority>\n".
"\t\t\t\t<keyword>".$type->getKeyword()."</keyword>\n");
		if ($type->getDescription() != "")
			fwrite($this->_xml,
"\t\t\t\t<description>".$type->getDescription()."</description>\n");
		fwrite($this->_xml,
"\t\t\t</type>\n".
"\t\t<\partstructure>\n");
	}
}
?>