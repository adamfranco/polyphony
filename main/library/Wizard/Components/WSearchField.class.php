<?php
/**
 * @since 11/27/07
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WSearchField.class.php,v 1.2 2007/11/29 17:50:20 adamfranco Exp $
 */ 

/**
 * The SearchField will take the input from a text area and send that via AJAX
 * to a search script which will then return a list of results. These results will
 * be cached until the contents of the search field is changed.
 * 
 * @since 11/27/07
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WSearchField.class.php,v 1.2 2007/11/29 17:50:20 adamfranco Exp $
 */
class WSearchField
	extends WizardComponent
{
	
	/**
	 * @var object SearchSource $searchSource;  
	 * @access private
	 * @since 11/27/07
	 */
	private $searchSource;
	
	/**
	 * @var string $searchTerm; The term entered 
	 * @access private
	 * @since 11/27/07
	 */
	private $searchTerm = '';
	
	/**
	 * @var array $searchResults;  
	 * @access private
	 * @since 11/27/07
	 */
	private $searchResults;
	
	/**
	 * Constructor
	 * 
	 * @return void
	 * @access public
	 * @since 11/27/07
	 */
	public function __construct () {
		$this->searchResults = array();
	}
	
	/**
	 * Set the search module, action, and term-field
	 * 
	 * @param string $module
	 * @param string $action
	 * @param string $termField
	 * @return void
	 * @access public
	 * @since 11/27/07
	 */
	public function setSearchSource (WSearchSource $searchSource) {
		$this->searchSource = $searchSource;
	}
	
	/**
	 * Tells the wizard component to update itself - this may include getting
	 * form post data or validation - whatever this particular component wants to
	 * do every pageload. 
	 * 
	 * @param string $fieldName The field name to use when outputting form data or
	 * similar parameters/information.
	 * @access public
	 * @return boolean - TRUE if everything is OK
	 * @since 11/27/07
	 */
	public function update ($fieldName) {
		$term = RequestContext::value($fieldName);
		
		if (!isset($this->searchSource))
			throw new Exception("No SearchSource set for field $fieldName");
		
		
		if (!is_null($term) && $term != $this->searchTerm) {
			$this->searchTerm = $term;
			
			$this->searchResults = $this->searchSource->getResults($term);	
		}
	}
	
	/**
	 * Answer the values for this field
	 * 
	 * @return array
	 * @access public
	 * @since 11/27/07
	 */
	public function getAllValues () {
		$values = array();
		foreach ($this->searchResults as $result) {
			$values[$result->getIdString()] = $result->isSelected();
		}
		return $values;
	}
	
	/**
	 * Returns a block of XHTML-valid code that contains markup for this specific
	 * component. 
	 *
	 * @param string $fieldName The field name to use when outputting form data or
	 * similar parameters/information.
	 * @return string
	 * @access public
	 * @since 11/27/07
	 */
	public function getMarkup ($fieldName) {
		ob_start();
		
		
		print "\n\t<input id='".RequestContext::name($fieldName)."' ";
		print "name='".RequestContext::name($fieldName)."' type='text' value=\"".$this->searchTerm."\" ";
// 		print " onchange='this.controller.update();'";
		print "/>";
		print "\n\t<input type='submit' value=\""._("Search")."\"/>";
		
		print $this->searchSource->getResultsMarkup($fieldName, $this->searchResults);
		
// 		print $this->getJS($fieldName);
		
		return ob_get_clean();
	}
	
	/**
	 * Answer a string of javascript that contains functions for searching
	 * 
	 * @param string $fieldName
	 * @return string
	 * @access protected
	 * @since 11/2/07
	 */
	protected function getJS ($fieldName) {
		ob_start();
		
		$outputId = $fieldName."_output";
		
		print "
	<script type='text/javascript'>
	// <![CDATA[

";
		print file_get_contents(POLYPHONY."/javascript/SearchFieldController.js");
		
		print "
		
		var searchField = document.get_element_by_id(\"$fieldName\");
		var outputBlock = document.get_element_by_id(\"$outputId\");
		
		searchField.controller = new SearchFieldController(searchField, outputBlock);
		
	// ]]>
	</script>
";
		return ob_get_clean();
	}
}

/**
 * The WSearchSource interface defines the methods that must be present for search
 * sources to operate with the WSearchField.
 * 
 * @since 11/27/07
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WSearchField.class.php,v 1.2 2007/11/29 17:50:20 adamfranco Exp $
 */
interface WSearchSource {
		
	/**
	 * Answer a valid url that will return an XML document containing the search
	 * results.
	 * 
	 * @param string $placeholder A placeholder which can be replaced with the search
	 * 		term on the client-side.
	 * @return string
	 * @access public
	 * @since 11/27/07
	 */
	public function getXmlUrl ($placeholder);
	
	/**
	 * Answer an array of search result objects after searching for the term passed
	 * 
	 * @param string $searchTerm
	 * @return array of WSearchResult objects
	 * @access public
	 * @since 11/27/07
	 */
	public function getResults ($searchTerm);
	
	/**
	 * Answer the markup for a set of search results
	 * 
	 * @param string $fieldName
	 * @param array $results An array of WSearchResult objects
	 * @return string
	 * @access public
	 * @since 11/27/07
	 */
	public function getResultsMarkup ($fieldName, $results);
	
}

/**
 * The WSearchResult interface defines the methods that must be present for search
 * results returned by a search source to the WSearchField.
 * 
 * @since 11/27/07
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WSearchField.class.php,v 1.2 2007/11/29 17:50:20 adamfranco Exp $
 */
interface WSearchResult {
		
	/**
	 * Answer the string Id of result
	 * 
	 * @return string
	 * @access public
	 * @since 11/27/07
	 */
	public function getIdString ();
	
	/**
	 * Answer true if the result has been selected.
	 * 
	 * @return boolean
	 * @access public
	 * @since 11/27/07
	 */
	public function isSelected ();
	
	/**
	 * Answer some XHTML markup that can be used to display the result.
	 * 
	 * @return string
	 * @access public
	 * @since 11/27/07
	 */
	public function getMarkup ();
	
}

?>