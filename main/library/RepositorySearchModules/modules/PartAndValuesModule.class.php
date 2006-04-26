<?php
/**
 * @package polyphony.library.repository.search
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PartAndValuesModule.class.php,v 1.1 2006/04/26 21:40:29 adamfranco Exp $
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
 * @version $Id: PartAndValuesModule.class.php,v 1.1 2006/04/26 21:40:29 adamfranco Exp $
 */

class PartAndValuesModule {
	
	/**
	 * Constructor
	 * 
	 * @param string $fieldName
	 * @return object
	 * @access public
	 * @since 10/28/04
	 */
	function PartAndValuesModule ( $fieldName ) {
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
		$setManager =& Services::getService("Sets");
		$recordStructSet =& $setManager->getPersistentSet($repository->getId());
		$recordStructSet->reset();
		while ($recordStructSet->hasNext()) {
			$recordStruct =& $repository->getRecordStructure($recordStructSet->next());
			$partStructSet =& $setManager->getPersistentSet($recordStruct->getId());
			while ($partStructSet->hasNext()) {
				$partStruct =& $recordStruct->getPartStructure($partStructSet->next());
					
			}
		}
		return "\t<input type='text' name='".RequestContext::name($this->_fieldname)."' value=\"".RequestContext::value($this->_fieldname)."\" />\n";
	}
	
	/**
	 * Get the formatted search terms based on the submissions of the form
	 * 
	 * @return mixed
	 * @access public
	 * @since 10/28/04
	 */
	function getSearchCriteria () {
		return RequestContext::value($this->_fieldname);
	}
}

?>