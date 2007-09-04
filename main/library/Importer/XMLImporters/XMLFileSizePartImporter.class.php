<?php
/**
 * @since 12/6/06
 * @package polyphony.library.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLFileSizePartImporter.class.php,v 1.2 2007/09/04 20:28:01 adamfranco Exp $
 */ 
 
require_once(dirname(__FILE__)."/XMLFilePartImporter.class.php");

/**
 * <##>
 * 
 * @since 12/6/06
 * @package polyphony.library.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLFileSizePartImporter.class.php,v 1.2 2007/09/04 20:28:01 adamfranco Exp $
 */
class XMLFileSizePartImporter
	extends XMLFilePartImporter
{
		
	/**
	 * Answer the PartStructureIdString
	 * 
	 * @return string
	 * @access public
	 * @since 12/6/06
	 */
	function getPartStructureIdString () {
		return 'FILE_SIZE';
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
	function isImportable ($element) {
		if ($element->nodeName == 'filesizepart')
			return true;
		else
			return false;
	}
	
}

?>