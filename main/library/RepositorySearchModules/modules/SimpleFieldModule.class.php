<?php
/**
 * @package polyphony.library.repository.search
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SimpleFieldModule.class.php,v 1.6 2006/04/27 21:02:58 adamfranco Exp $
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
 * @version $Id: SimpleFieldModule.class.php,v 1.6 2006/04/27 21:02:58 adamfranco Exp $
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
	function createSearchForm (&$repository, $action ) {
		ob_start();
		
		print "<form action='$action' method='post'>\n<div>\n";
		
		$this->createSearchFields($repository);
		
		print "\t<input type='submit' />\n";
		print "</div>\n</form>";
		
		$form = ob_get_contents();
		ob_end_clean();
		return $form;
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
		return "\t<input type='text' name='".RequestContext::name($this->_fieldname)."' value=\"".RequestContext::value($this->_fieldname)."\" />\n";
	}
	
	/**
	 * Get the formatted search terms based on the submissions of the form
	 * 
	 * @param object Repository $repository
	 * @return mixed
	 * @access public
	 * @since 10/28/04
	 */
	function getSearchCriteria ( &$repository ) {
		return RequestContext::value($this->_fieldname);
	}
	
	/**
	 * Get an array of the current values to be added to a url. The keys of the
	 * arrays are the field-names in the appropriate context.
	 * 
	 * @return array
	 * @access public
	 * @since 04/25/06
	 */
	function getCurrentValues () {
		if (RequestContext::value($this->_fieldname))
			return array(
						RequestContext::name($this->_fieldname) => 
						RequestContext::value($this->_fieldname));
		else
			return array();
	}
}

?>