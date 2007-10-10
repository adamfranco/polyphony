<?php
/**
 * @since 10/10/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLMIMEPartImporter.class.php,v 1.15 2007/10/10 22:58:48 adamfranco Exp $
 */ 
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLFilePartImporter.class.php");

/**
 * imports the mimetype of a file, how interesting
 * 
 * @since 10/10/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLMIMEPartImporter.class.php,v 1.15 2007/10/10 22:58:48 adamfranco Exp $
 */
class XMLMIMEPartImporter extends XMLFilePartImporter {
		
		
	/**
	 * Answer the PartStructureIdString
	 * 
	 * @return string
	 * @access public
	 * @since 12/6/06
	 */
	function getPartStructureIdString () {
		return 'MIME_TYPE';
	}
	
	/**
	 * Filters nodes of incorrect type
	 * 
	 * @param object DOMIT_Node
	 * @return boolean
	 * @static
	 * @access public
	 * @since 10/10/05
	 */
	static function isImportable ($element) {
		if ($element->nodeName == 'mimepart')
			return true;
		else
			return false;
	}
}

?>