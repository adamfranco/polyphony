<?php
/**
 * @package polyphony.library.repository.search
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SimpleFieldModule.class.php,v 1.3 2005/04/07 17:07:47 adamfranco Exp $
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
 * @version $Id: SimpleFieldModule.class.php,v 1.3 2005/04/07 17:07:47 adamfranco Exp $
 */

class SimpleFieldModule {
	
	/**
	 * Constructor
	 * 
	 * @param string $fieldName
	 * @return object
	 * @access public
	 * @since 10/28/04
	 */
	function SimpleFieldModule ( $fieldName ) {
		$this->_fieldname = $fieldName;
	}
	
	
	
	/**
	 * Create a form for searching.
	 * 
	 * @param string $action The destination on form submit.
	 * @return string
	 * @access public
	 * @since 10/19/04
	 */
	function createSearchForm ($action ) {
		ob_start();
		
		print "<form action='$action' method='post'>\n<div>\n";
		
		print "\t<input type='text' name='".$this->_fieldname."' />\n";
		
		print "\t<input type='submit' />\n";
		print "</div>\n</form>";
		
		$form = ob_get_contents();
		ob_end_clean();
		return $form;
	}
	
	/**
	 * Get the formatted search terms based on the submissions of the form
	 * 
	 * @return mixed
	 * @access public
	 * @since 10/28/04
	 */
	function getSearchCriteria () {
		return $_REQUEST[$this->_fieldname];
	}
}

?>