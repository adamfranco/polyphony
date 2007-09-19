<?php
/**
 * @since 12/6/06
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLFileNamePartImporter.class.php,v 1.6 2007/09/19 14:04:47 adamfranco Exp $
 */ 
 
require_once(dirname(__FILE__)."/XMLFilePartImporter.class.php");

/**
 * <##>
 * 
 * @since 12/6/06
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLFileNamePartImporter.class.php,v 1.6 2007/09/19 14:04:47 adamfranco Exp $
 */
class XMLFileNamePartImporter
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
		return 'FILE_NAME';
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
		if ($element->nodeName == 'filenamepart')
			return true;
		else
			return false;
	}
	
}

?>