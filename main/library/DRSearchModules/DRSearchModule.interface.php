<?php
/**
 * @package polyphony.library.dr.search
 */

/**
 * Search Modules generate forms for and collect/format the subitions of said forms
 * for various Digital Repository search types.
 * 
 * @package polyphony.library.dr.search
 * @version $Id: DRSearchModule.interface.php,v 1.2 2005/02/04 23:06:05 adamfranco Exp $
 * @since $Date: 2005/02/04 23:06:05 $
 * @copyright 2004 Middlebury College
 */

class DRSearchModuleInterface {
		
	/**
	 * Create a form for searching.
	 * 
	 * @param string $action The destination on form submit.
	 * @return string
	 * @access public
	 * @since 10/19/04
	 */
	function createSearchForm ($action ) {
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