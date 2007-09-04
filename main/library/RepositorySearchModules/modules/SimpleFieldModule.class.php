<?php
/**
 * @package polyphony.library.repository.search
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SimpleFieldModule.class.php,v 1.8 2007/09/04 20:28:03 adamfranco Exp $
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
 * @version $Id: SimpleFieldModule.class.php,v 1.8 2007/09/04 20:28:03 adamfranco Exp $
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
		$this->_initilaized = false;
	}
	
	/**
	 * Initialize this object
	 * 
	 * @return void
	 * @access public
	 * @since 5/15/06
	 */
	function init () {
		if (!$this->_initilaized) {
			$harmoni = Harmoni::instance();
			$harmoni->request->startNamespace('SimpleFieldModule');
			
			$this->_contextFieldName = RequestContext::name($this->_fieldname);
			if (RequestContext::value($this->_fieldname))
				$this->_value = RequestContext::value($this->_fieldname);
			else
				$this->_value = null;
	
			$harmoni->request->endNamespace();
			
			$this->_initilaized = true;
		}
	}
	
	/**
	 * Create a form for searching.
	 * 
	 * @param string $action The destination on form submit.
	 * @return string
	 * @access public
	 * @since 10/19/04
	 */
	function createSearchForm ($repository, $action ) {
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
	function createSearchFields ($repository) {
		$this->init();
		return "\t<input type='text' name='".$this->_contextFieldName."' value=\"".$this->_value."\" />\n";
	}
	
	/**
	 * Get the formatted search terms based on the submissions of the form
	 * 
	 * @param object Repository $repository
	 * @return mixed
	 * @access public
	 * @since 10/28/04
	 */
	function getSearchCriteria ( $repository ) {
		$this->init();
		return $this->_value;
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
		$this->init();
		if ($this->_value)
			return array($this->_contextFieldName => $this->_value);
		else
			return array();
	}
	
	/**
	 * Update the current values with data (maybe stored in the session for instance. 
	 * The keys of the arrays are the field-names in the appropriate context.
	 * This could have been originally fetched via getCurrentValues
	 * 
	 * @param array $values
	 * @return void
	 * @access public
	 * @since 04/25/06
	 */
	function setCurrentValues ($values) {
		$this->init();
		
		if (isset($values[$this->_contextFieldName])) {
			$this->_value = $values[$this->_contextFieldName];
		}
	}
}

?>