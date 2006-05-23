<?php
/**
 * @since 10/17/05
 * @package polyphony.exporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLFileRecordExporter.class.php,v 1.9 2006/05/23 15:09:29 cws-midd Exp $
 */ 

//require_once(POLYPHONY."/main/library/Exporter/XMLPartExporter.class.php");

/**
 * Exports into XML for use with the XML Importer
 * 
 * @since 10/17/05
 * @package polyphony.exporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLFileRecordExporter.class.php,v 1.9 2006/05/23 15:09:29 cws-midd Exp $
 */
class XMLFileRecordExporter {
		
	/**
	 * Constructor
	 *
	 * Maintains the archive, xml file and destination folder for data files
	 * 
	 * @param object Archive_Tar
	 * @param resource
	 * @param string
	 * @access public
	 * @since 10/17/05
	 */
	function XMLFileRecordExporter (&$xmlFile, $fileDir) {
		$this->_xml =& $xmlFile;
		$this->_fileDir = $fileDir; 
		
		$this->_childExporterList = null;
		$this->_childElementList = null;
		}

	/**
	 * Exporter of All things
	 * 
	 * @param object HarmoniFileRecord
	 * @access public
	 * @since 10/17/05
	 */
	function export (&$record) {
		$this->_object =& $record;
		$this->_myId =& $this->_object->getId();

		$this->getFileParts();

		fwrite($this->_xml,
"\t\t<filerecord ".
"id=\"".$this->_myId->getIdString()."\">\n".
"\t\t\t<filedatapart>".$this->_info['f_name']."</filedatapart>\n".
"\t\t\t<filedimensionspart>\n".
"\t\t\t\t<width>".$this->_info['f_dime'][0]."</width>\n".
"\t\t\t\t<height>".$this->_info['f_dime'][1]."</height>\n".
"\t\t\t</filedimensionspart>\n".
"\t\t\t<mimepart>".$this->_info['f_mime']."</mimepart>\n".
"\t\t\t<thumbdatapart>".$this->_info['t_name']."</thumbdatapart>\n".
"\t\t\t<thumbdimensionspart>\n".
"\t\t\t\t<width>".$this->_info['t_dime'][0]."</width>\n".
"\t\t\t\t<height>".$this->_info['t_dime'][1]."</height>\n".
"\t\t\t</thumbdimensionspart>\n".
"\t\t\t<thumbmimepart>".$this->_info['t_mime']."</thumbmimepart>\n".
"\t\t</filerecord>\n");
	}

	/**
	 * Exporter of partstructures
	 * 
	 * Adds partstructure elements to the xml, which contain the necessary
	 * information to create the same partstructure.
	 * 
	 * @access public
	 * @since 10/17/05
	 */
	function getFileParts () {
		$idManager =& Services::getService("Id");
		$this->_info = array();
		
		$FILE_DATA_ID =& $idManager->getId("FILE_DATA");
		$FILE_NAME_ID =& $idManager->getId("FILE_NAME");
		$FILE_DIME_ID =& $idManager->getId("DIMENSIONS");
		$MIME_TYPE_ID =& $idManager->getId("MIME_TYPE");
		$THUMB_DATA_ID =& $idManager->getId("THUMBNAIL_DATA");
		$THUMB_MIME_ID =& $idManager->getId("THUMBNAIL_MIME_TYPE");
		$THUMB_DIME_ID =& $idManager->getId("THUMBNAIL_DIMENSIONS");
		
		$parts =& $this->_object->getPartsByPartStructure($FILE_NAME_ID);
		if ($parts->count() == 1) {
			$part =& $parts->next();
			$path = $this->_fileDir."/".$part->getValue();
// CHECK FOR FILE NAME UNIQUENESS HERE
			$this->_dataFile = fopen($path, "wb");
			
			$this->_info['f_name'] = basename($path);
		}
		$parts =& $this->_object->getPartsByPartStructure($FILE_DATA_ID);
		if ($parts->count() == 1) {
			$part =& $parts->next();
			fwrite($this->_dataFile, $part->getValue());
			fclose($this->_dataFile);
		}
		$parts =& $this->_object->getPartsByPartStructure($FILE_DIME_ID);
		if ($parts->count() == 1) {
			$part =& $parts->next();
			$this->_info['f_dime'] = $part->getValue();
		}
		$parts =& $this->_object->getPartsByPartStructure($MIME_TYPE_ID);
		if ($parts->count() == 1) {
			$part =& $parts->next();
			$this->_info['f_mime'] = $part->getValue();
		}
		$path = $this->_fileDir."/THUMB_".$this->_info['f_name'];
		$this->_info['t_name'] = basename($path);
		$this->_thumbFile = fopen($path, "wb");
		$parts =& $this->_object->getPartsByPartStructure($THUMB_DATA_ID);
		if ($parts->count() == 1) {
			$part =& $parts->next();
			fwrite($this->_thumbFile, $part->getValue());
			fclose($this->_thumbFile);
		}
		$parts =& $this->_object->getPartsByPartStructure($THUMB_MIME_ID);
		if ($parts->count() == 1) {
			$part =& $parts->next();
			$this->_info['t_mime'] = $part->getValue();
		}
		$parts =& $this->_object->getPartsByPartStructure($THUMB_DIME_ID);
		if ($parts->count() == 1) {
			$part =& $parts->next();
			$this->_info['t_dime'] = $part->getValue();
		}
	}	
}
?>
