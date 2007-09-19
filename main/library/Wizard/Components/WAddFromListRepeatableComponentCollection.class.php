<?php
/**
 * @since Aug 1, 2005
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WAddFromListRepeatableComponentCollection.class.php,v 1.4 2007/09/19 14:04:51 adamfranco Exp $
 */ 

/**
 * This component allows for the creation of repeatable components or groups of 
 * components. Only the newly added comonents are editable. Existing ones can only
 * be removed.
 * 
 * @since Aug 1, 2005
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WAddFromListRepeatableComponentCollection.class.php,v 1.4 2007/09/19 14:04:51 adamfranco Exp $
 */

class WAddFromListRepeatableComponentCollection
	extends WRepeatableComponentCollection 
{
	function WAddFromListRepeatableComponentCollection() {
		parent::WRepeatableComponentCollection();
    	$this->_addButton = WChooseOptionButton::withLabel($this->_addLabel);
    	$this->_addButton->setParent($this);
    }
    
	/**
	 * Add an array of value collections for use when adding
	 * 
	 * @param string $name
	 * @param array $valueCollection
	 * @return void
	 * @access public
	 * @since 11/1/05
	 */
	function addOptionCollection ( $name, $valueCollection ) {
		$this->_addButton->addOptionValue($name, $valueCollection);
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
		$ok = true;
//		$this->_removeElements(array(2));
		// first update all our components in the collections
		$toRemove = array();
		foreach(array_keys($this->_collections) as $key) {
			foreach(array_keys($this->_collections[$key]) as $name) {
//				print "$name in $key is a ".gettype($this->_collections[$key][$name])."<br/>";
//				if (!is_object($this->_collections[$key][$name])) continue;
				if (!$this->_collections[$key][$name]->update($fieldName."_".$key."_".$name)) $ok = false;
			}
			if ($this->_collections[$key]["_remove"]->getAllValues()) $toRemove[] = $key;
		}
		$this->_removeElements($toRemove);
		
		// then, check if any "buttons" or anything were pressed to add/remove elements
		$this->_addButton->update($fieldName."_add");
		$addButtonValues = $this->_addButton->getAllValues();
		if ($addButtonValues['pressed']) {
//			print "adding element.<br/>";
			if (is_array($addButtonValues['option']))
				$this->addValueCollection($addButtonValues['option']);
			else
				$this->_addElement();
		}
		
		return $ok;
	}
}
?>