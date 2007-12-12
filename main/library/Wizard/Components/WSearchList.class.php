<?php
/**
 * @since 12/7/07
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WSearchList.class.php,v 1.1 2007/12/12 17:19:23 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/WSearchField.class.php");

/**
 * This class builds a list of items that were found via search.
 * 
 * @since 12/7/07
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WSearchList.class.php,v 1.1 2007/12/12 17:19:23 adamfranco Exp $
 */
class WSearchList
	extends WSearchField
{
		
	/**
	 * @var array $list;  
	 * @access private
	 * @since 12/7/07
	 */
	private $list;
	
	/**
	 * Constructor
	 * 
	 * @return void
	 * @access public
	 * @since 12/7/07
	 */
	public function __construct () {
		parent::__construct();
		$this->list = array();
	}
	
	/**
	 * Answer the values for this field
	 * 
	 * @return array
	 * @access public
	 * @since 12/7/07
	 */
	public function getAllValues () {
		$values = array();
		foreach ($this->list as $result) {
			$values[] = $result->getIdString();
		}
		return $values;
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
	 * @since 12/12/07
	 */
	public function update ($fieldName) {
		// Update the list with any subtractions
		$removeId = RequestContext::value($fieldName."_remove");
		if ($removeId) {
			foreach ($this->list as $key => $result) {
				if ($removeId == $result->getIdString()) {
					$this->removeFromList($key);
					break;
				}
			}
		}
		
		// Update the list with any additions
		$addId = RequestContext::value($fieldName."_add");
		if ($addId) {
			foreach ($this->searchResults as $result) {
				if ($addId == $result->getIdString()) {
					$this->addToList($result);
					break;
				}
			}
		}
		
		// update the search field
		parent::update($fieldName);
	}
	
	/**
	 * Add a result to our list if it isn't already there.
	 * 
	 * @param object WSearchResult $result
	 * @return void
	 * @access private
	 * @since 12/12/07
	 */
	private function addToList (WSearchResult $result) {
		foreach ($this->list as $existingResult) {
			if ($existingResult->getIdString() == $result->getIdString())
				return;
		}
		
		$this->list[] = $result;
	}
	
	/**
	 * Add a value to the list.
	 * 
	 * @param object WSearchResult $result
	 * @return void
	 * @access public
	 * @since 12/12/07
	 */
	public function addValue (WSearchResult $result) {
		$this->addToList($result);
	}
	
	/**
	 * Add a result to our list if it isn't already there.
	 * 
	 * @param integer $listIndex
	 * @return void
	 * @access private
	 * @since 12/12/07
	 */
	private function removeFromList ($listIndex) {
		if (!array_key_exists($listIndex, $this->list))
			throw new Exception("Unknown list index, '$listIndex'.");
		
		array_splice($this->list, $listIndex, 1);
	}
	
	/**
	 * Returns a block of XHTML-valid code that contains markup for this specific
	 * component. 
	 *
	 * @param string $fieldName The field name to use when outputting form data or
	 * similar parameters/information.
	 * @return string
	 * @access public
	 * @since 12/7/07
	 */
	public function getMarkup ($fieldName) {
		ob_start();
		print "\n\t<table class='search_results' cellspacing='0'>";
		$colorKey = 0;
		$i = 0;
		foreach ($this->list as $result) {
			print "\n\t\t<tr class='search_result_item '>";
			print "\n\t\t\t<td class='color".$colorKey."'>";
			print "<input type='button' name=\"".RequestContext::name($fieldName."_removebutton_".$i)."\" value=\""._("- Remove")."\" onclick=\"";
			print "var chosenVal = document.createElement('input'); ";
			print "chosenVal.type = 'hidden'; ";
			print "chosenVal.name = '".RequestContext::name($fieldName."_remove")."'; ";
			print "chosenVal.value = '".$result->getIdString()."'; ";
			print "this.parentNode.appendChild(chosenVal); ";
			print "this.form.submit(); ";
			print "return true; ";
			print "\"/>";
			print " &nbsp; ";
			print $result->getMarkup();
			print "\n\t\t\t</td>";
			print "\n\t\t</tr>";
			$colorKey = intval(!$colorKey);
			$i++;
		}
		print "\n</table>";

		print "\n<div style='margin-top:10px;'>";
		print "\n\t<input id='".RequestContext::name($fieldName)."' ";
		print "name='".RequestContext::name($fieldName)."' type='text' value=\"".$this->searchTerm."\" ";
// 		print " onchange='this.controller.update();'";
		print "/>";
		print "\n\t<input type='submit' value=\""._("Search")."\"/>";
		print "\n</div>";
		
		print "\n\t<table class='search_results' cellspacing='0'>";
		$colorKey = 0;
		$i = 0;
		foreach ($this->searchResults as $result) {
			print "\n\t\t<tr class='search_result_item '>";
			
// 			print "\n\t\t\t<td class='choose_button color".$colorKey."'>";
			print "\n\t\t\t<td class='color".$colorKey."'>";

			print "<input type='button' name=\"".RequestContext::name($fieldName."_addbutton_".$i)."\" value=\""._("+ Add")."\" onclick=\"";
			print "var chosenVal = document.createElement('input'); ";
			print "chosenVal.type = 'hidden'; ";
			print "chosenVal.name = '".RequestContext::name($fieldName."_add")."'; ";
			print "chosenVal.value = '".$result->getIdString()."'; ";
			print "this.parentNode.appendChild(chosenVal); ";
			print "this.form.submit(); ";
			print "return true; ";
			print "\"/>";
			print " &nbsp; ";
// 			print "\n\t\t\t</td>";
// 			
// 			print "\n\t\t\t<td class='color".$colorKey."'>";
			print $result->getMarkup();
			print "\n\t\t\t</td>";
			print "\n\t\t</tr>";
			
			$colorKey = intval(!$colorKey);
			$i++;
		}
		print "\n</table>";
		
		return ob_get_clean();
	}
	
}

?>