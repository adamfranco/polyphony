<?php

/**
 * Search Modules generate forms for and collect/format the subitions of said forms
 * for various Digital Repository search types.
 * 
 * @package polyphony.dr.search
 * @version $Id: RepositorySearchModule.interface.php,v 1.1 2005/01/27 21:47:24 adamfranco Exp $
 * @date $Date: 2005/01/27 21:47:24 $
 * @copyright 2004 Middlebury College
 */

class RepositorySearchModuleInterface {
		
	/**
	 * Create a form for searching.
	 * 
	 * @param string $action The destination on form submit.
	 * @return string
	 * @access public
	 * @date 10/19/04
	 */
	function createSearchForm ($action ) {
		die ("Method <b>".__FUNCTION__."()</b> declared in interface <b> ".__CLASS__."</b> has not been overloaded in a child class.");
	}
	
	/**
	 * Get the formatted search terms based on the submissions of the form
	 * 
	 * @return mixed
	 * @access public
	 * @date 10/28/04
	 */
	function getSearchCriteria () {
		die ("Method <b>".__FUNCTION__."()</b> declared in interface <b> ".__CLASS__."</b> has not been overloaded in a child class.");
	}
}

?>