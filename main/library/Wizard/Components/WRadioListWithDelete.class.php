<?php
/**
 * @since 5/19/08
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 
require_once(dirname(__FILE__).'/WRadioList.class.php');

/**
 * This component allow a choice between several options while allowing a 
 * 'delete' of some of them.
 * 
 * @since 5/19/08
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class WRadioListWithDelete 
	extends WRadioList
{
	var $_deletable = array();
	var $_toDelete = array();
	
	
	function __construct() {
		parent::__construct();
		
		$this->_eachPre = "\n<div class='WRadioListWithDelete'>";
		$this->_eachPost = "\n</div>";
	}
	
	/**
	 * Virtual constructor - creates this object with the specified layout.
	 * @param string $pre A string to prepend onto the markup block (ex, "<ul>")
	 * @param string $eachPre A string to put at the beginning of each of the 
	 *		elements (ex, "<li>")
	 * @param string $eachPost A string to put at the end of each of the 
	 *		elements (ex, "</li>")
	 * @param string $post A string to tack onto the end of the block (ex, "</ul>")
	 * @access public
	 * @return ref object
	 * @static
	 */
	static function withLayout ($pre, $eachPre, $eachPost, $post, $class='WRadioListWithDelete') {
		return parent::withLayout($pre, $eachPre, $eachPost, $post, $class);
	}
	
	/**
	 * Adds a radio option to this list.
	 * @param string $value The short value that represents the displayed text.
	 * @param optional string $displayText The text to show to the end user. Defaults to $value.
	 * @param optional string $extendedHtml Text to add to the item after the display text.
	 * @param optional boolean $deletable If true, a delete button will be added for this item.
	 * @access public
	 * @return void
	 */
	function addOption ($value, $displayText = null, $extendedHtml = null, $deletable = false) {
		parent::addOption($value, $displayText, $extendedHtml);
		$this->_deletable[$value] = $deletable;
	}
	
	/**
	 * Tells the wizard component to update itself - this may include getting
	 * form post data or validation - whatever this particular component wants to
	 * do every pageload. 
	 * @param string $fieldName The field name to use when outputting form data or
	 * similar parameters/information.
	 * @access public
	 * @return boolean - TRUE if everything is OK
	 */
	function update ($fieldName) {
		parent::update($fieldName);
		
		$deleteValues = RequestContext::value($fieldName.'_delete');
		if (strlen($deleteValues)) {
			$toDelete = explode('|||', $deleteValues);
			foreach ($toDelete as $key) {
				$this->_toDelete[] = $key;
				
				// remove the value from our list.
				unset($this->_deletable[$key]);
				unset($this->_items[$key]);
				unset($this->_extendedHtml[$key]);
				
				// reset our value if we are deleting the selected option
				if ($this->_value == $key) {
					$this->_value = null;
					
					// Set the value to the first one.
					if(count($this->_items)) {
						$values = array_keys($this->_items);
						$this->_value = $values[0];
					}
				}
			}
		}
	}
	
	/**
	 * Returns the values of wizard-components. Should return an array if children are involved,
	 * otherwise a whatever type of object is expected.
	 * @access public
	 * @return mixed
	 */
	function getAllValues () {
		$values = array();
		$values['selected'] = parent::getAllValues();
		$values['deleted'] = $this->_toDelete;
		return $values;
	}
	
	/**
	 * Returns a block of XHTML-valid code that contains markup for this specific
	 * component. 
	 * @param string $fieldName The field name to use when outputting form data or
	 * similar parameters/information.
	 * @access public
	 * @return string
	 */
	function getMarkup ($fieldName) {
		ob_start();
		print parent::getMarkup($fieldName);
		
		$name = RequestContext::name($fieldName.'_delete');
		$confirmString = dgettext('polyphony', "Are you sure that you wish to delete this item?");
		print <<<END
		
		<input type='hidden' name='$name' id='$name' value=''/>
		
		<script type='text/javascript'>
		// <![CDATA[
		
			/**
			 * A Class for methods relating to WRadioListWithDelete
			 * 
			 * @return null
			 * @access public
			 * @since 5/19/08
			 */
			function WRadioListWithDelete () {
			}
		
			/**
			 * Delete a radio-list item
			 * 
			 * @param DOMElement button
			 * @param string choiceField
			 * @param string deleteField
			 * @param string keyToDelete
			 * @return null
			 * @access public
			 * @since 5/19/08
			 */
			WRadioListWithDelete.delete = function (button, choiceField, deleteField, keyToDelete) {
// 				alert(button + "\\n" + deleteField + "\\n" + keyToDelete);
				if (confirm("$confirmString")) {
					var field = document.get_element_by_id(deleteField);
					field.value = field.value + "|||" + keyToDelete;
					button.parentNode.style.display = 'none';
					
					// Check the current value of the field
					for (var i = 0; i < button.form.elements.length; i++ ) {
						if (button.form.elements.item(i).name == choiceField
							&& button.form.elements.item(i).value == keyToDelete
							&& button.form.elements.item(i).checked == true) 
						{
							WRadioListWithDelete.resetToFirstOption(button, choiceField, keyToDelete);
						}
					}
				}
			}
			
			/**
			 * Reset the radio list to the first option
			 * 
			 * @param DOMElement button
			 * @param string choiceField
			 * @return null
			 * @access public
			 * @since 5/19/08
			 */
			WRadioListWithDelete.resetToFirstOption = function(button, choiceField, keyToDelete) {
				for (var i = 0; i < button.form.elements.length; i++ ) {
					if (button.form.elements.item(i).name == choiceField
						&& button.form.elements.item(i).value != keyToDelete)
					{
						button.form.elements.item(i).checked = true;
						break;
					}
				}
			}
		
		// ]]>
		</script>
		
END;
		
		return ob_get_clean();
	}
	
	/**
	 * Answer the extended HTML for an item
	 * 
	 * @param string $fieldName The field name to use when outputting form data or
	 * @param string $key
	 * @return string
	 * @access protected
	 * @since 5/19/08
	 */
	protected function getExtendedHtml ($fieldName, $key) {		
		ob_start();
		if ($this->_deletable[$key]) {
			$choiceName = RequestContext::name($fieldName);
			$deleteName = RequestContext::name($fieldName.'_delete');
			$name = RequestContext::name($fieldName.'_delete_'.$key);
			
			print " &nbsp; ";
			print "\n\t<input type='button' name='$name' value='"._("Delete")."'";
			print " onclick=\"";
			print " WRadioListWithDelete.delete(this, '$choiceName', '$deleteName', '$key');";
			print "\"";
			print "'/>";
		}
		return ob_get_clean().parent::getExtendedHtml($fieldName, $key);
	}
}

?>