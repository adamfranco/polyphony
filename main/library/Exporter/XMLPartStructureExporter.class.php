<?php
/**
 * @since 10/17/05
 * @package polyphony.library.exporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLPartStructureExporter.class.php,v 1.6 2007/09/04 20:27:59 adamfranco Exp $
 */ 

/**
 * Exports into XML for use with the XML Importer
 * 
 * @since 10/17/05
 * @package polyphony.library.exporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLPartStructureExporter.class.php,v 1.6 2007/09/04 20:27:59 adamfranco Exp $
 */
class XMLPartStructureExporter {
		
	/**
	 * Constructor
	 *
	 * creates a new instantiation of the partstructure exporter
	 * 
	 * @param resource
	 * @access public
	 * @since 10/17/05
	 */
	function XMLPartStructureExporter ($xmlFile) {
		$this->_xml =$xmlFile;
		
		$this->_childExporterList = null;
		$this->_childElementList = null;
	}

	/**
	 * Exports the object given and children if available
	 * 
	 * @param object HarmoniPartStructure
	 * @access public
	 * @since 10/17/05
	 */
	function export ($pS) {
		$this->_object =$pS;
		$this->_myId =$this->_object->getId();
		$type =$this->_object->getType();

		fwrite($this->_xml,
"\t\t<partstructure ".
"id=\"".$this->_myId->getIdString()."\" ".
"xml:id=\"".$this->_myId->getIdString()."\" ".
"isMandatory=\"".(($this->_object->isMandatory())?"TRUE":"FALSE")."\" ".
"isRepeatable=\"".(($this->_object->isRepeatable())?"TRUE":"FALSE")."\" ".
"isPopulated=\"".(($this->_object->isPopulatedByRepository())?"TRUE":"FALSE").
"\">\n".
"\t\t\t<name>".$this->_object->getDisplayName()."</name>\n".
"\t\t\t<description><![CDATA[".$this->_object->getDescription()."]]></description>\n".
"\t\t\t<type>\n\t\t\t\t<domain>".$type->getDomain()."</domain>\n".
"\t\t\t\t<authority>".$type->getAuthority()."</authority>\n".
"\t\t\t\t<keyword>".$type->getKeyword()."</keyword>\n");
		if ($type->getDescription() != "")
			fwrite($this->_xml,
"\t\t\t\t<description><![CDATA[".$type->getDescription()."]]></description>\n");
		fwrite($this->_xml,
"\t\t\t</type>\n".
"\t\t</partstructure>\n");
	}
}
?>