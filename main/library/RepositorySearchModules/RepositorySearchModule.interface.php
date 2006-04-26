<?php
/**
 * @package polyphony.library.repository.search
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: RepositorySearchModule.interface.php,v 1.4 2006/04/26 21:40:29 adamfranco Exp $
 */

/**
 * Search Modules generate forms for and collect/format the subitions of said forms
 * for various Digital Repository search types.
 *
 * @package polyphony.library.repository.search
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: RepositorySearchModule.interface.php,v 1.4 2006/04/26 21:40:29 adamfranco Exp $
 */

class RepositorySearchModuleInterface {
		
	/**
	 * Create a form for searching.
	 * 
	 * @param string $action The destination on form submit.
	 * @return string
	 * @access public
	 * @since 10/19/04
	 */
	function createSearchForm (&$repository, $action ) {
		die ("Method <b>".__FUNCTION__."()</b> declared in interface <b> ".__CLASS__."</b> has not been overloaded in a child class.");
	}
	
	/**
	 * Create the fields (without form tags) for searching
	 * 
	 * @param object Repository $repository
	 * @return string
	 * @access public
	 * @since 4/26/06
	 */
	function createSearchFields (&$repository) {
		die ("Method <b>".__FUNCTION__."()</b> declared in interface <b> ".__CLASS__."</b> has not been overloaded in a child class.");
	}
	
	/**
	 * Get the formatted search terms based on the submissions of the form
	 * 
	 * @return mixed
	 * @access public
	 * @since 10/28/04
	 */
	function getSearchCriteria () {
		die ("Method <b>".__FUNCTION__."()</b> declared in interface <b> ".__CLASS__."</b> has not been overloaded in a child class.");
	}
}

?>