<?php
/**
 * @since 12/6/06
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLFileUrlPartImporter.class.php,v 1.4 2007/10/10 22:58:48 adamfranco Exp $
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
 * @version $Id: XMLFileUrlPartImporter.class.php,v 1.4 2007/10/10 22:58:48 adamfranco Exp $
 */
class XMLFileUrlPartImporter
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
		return 'FILE_URL';
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
		if ($element->nodeName == 'fileurlpart')
			return true;
		else
			return false;
	}
	
}

?>