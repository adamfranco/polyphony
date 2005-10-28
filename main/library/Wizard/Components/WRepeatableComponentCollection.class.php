<?php
/**
 * @since Aug 1, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WRepeatableComponentCollection.class.php,v 1.9 2005/10/28 16:33:59 adamfranco Exp $
 */ 

/**
 * This component allows for the creation of repeatable components or groups of components. 
 * 
 * @since Aug 1, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WRepeatableComponentCollection.class.php,v 1.9 2005/10/28 16:33:59 adamfranco Exp $
 */

class WRepeatableComponentCollection 
	extends WizardComponentWithChildren 
{

    var $_collections = array();
    var $_min = 0;
    var $_max = -1;
    var $_num = 1;
    var $_text = '';
    
    var $_addButton;
    
    function WRepeatableComponentCollection() {
    	$this->_addButton =& WEventButton::withLabel(dgettext("polyphony", "Add"));
    	$this->_addButton->setParent($this);
    }
    
    /**
	 * Sets the minimum number of elements that we allow in the collection.
	 * @param integer $min
	 * @access public
	 * @return void
	 */
	function setMiminum ($min) {
		$this->_min = $min;
	}
	
	/**
	 * Sets the maximum number of elements that we allow in this collection. A value of "-1" is no limit.
	 * @param integer $max
	 * @access public
	 * @return void
	 */
	function setMaximum ($max) {
		$this->_max = $max;
	}
    
    /**
	 * Sets the number of elements to display as a starting value.
	 * @param integer $start
	 * @access public
	 * @return void
	 */
	function setStartingNumber ($start) {
		$this->_num = $start;
	}
	
	/**
	 * Sets the textual layout of each element in the collection. They will be surrounded by html DIV tags.
	 * @param string $text
	 * @access public
	 * @return void
	 */
	function setElementLayout ($text) {
		$this->_text = $text;
	}
	
	/**
	 * Sets this step's content text. 
	 * This text will be parsed with {@link Wizard::parseText()}
	 * @param string $content;
	 * @access public
	 * @return void
	 */
	function setContent ($content) {
		$this->setElementLayout ($content);
	}
	
	/**
	 * Adds a collection of {@link WizardComponent}s indexed by field name to the list of collections.
	 * This is useful when pre-populating the list with old/previous values.
	 * @param ref array $collection Indexed by field name.
	 * @access public
	 * @return ref array An array of the components created with the values passed.
	 */
	function &addValueCollection (&$collection) {
		// @todo - make sure that the correct fields/classes are represented
		$newCollection =& $this->_addElement();
		foreach (array_keys($newCollection) as $key) {
			if (isset($collection[$key]))
				$newCollection[$key]->setValue($collection[$key]);
		}
		
		return $newCollection;
	}
	
	/**
	 * A wrapper for addValueCollection which adds a ValueCollection for every element
	 * in the array passet, to allow for adding values to nested 
	 * RepeatableComponentCollections.
	 * 
	 * @param ref array $collectionArray
	 * @return void
	 * @access public
	 * @since 10/27/05
	 */
	function setValue ( &$collectionArray ) {
		foreach(array_keys($collectionArray) as $key) {
			$this->addValueCollection($collectionArray[$key]);
		}
	}
	
	/**
	 * Adds a new element to the end of the list.
	 * @access private
	 * @return void
	 */
	function &_addElement () {
		if ($this->_max != -1 && $this->_num == $this->_max - 1) return;
//		printDebugBacktrace();
		// clone our base set (the getChildren() array)
		$newArray = array();
		$base =& $this->getChildren();
		foreach (array_keys($base) as $key) {
			$newArray[$key] =& $base[$key]->copy();
			$newArray[$key]->setParent($this);
		}
		$newArray["_remove"] =& WEventButton::withLabel(
			dgettext("polyphony", "Remove"));
		$newArray["_remove"]->setParent($this);
		$newArray["_remove"]->setOnClick("ignoreValidation(this.form);");

		$this->_collections[] =& $newArray;
		$this->_num++;

		return $newArray;
	}

	
	/**
	 * Removes the elements from our list.
	 * @param array $ar An array of element keys.
	 * @access private
	 * @return void
	 */
	function _removeElements ($ar) {
		if (($this->_num-count($ar)) < $this->_min) return;
		foreach ($ar as $key) {
			unset($this->_collections[$key]);
			$this->_num--;
		}
		$this->_collections = array_values($this->_collections); // re-index the array
	}
	
	/**
	 * Returns true if this component (and all child components if applicable) have valid values.
	 * By default, this will just return TRUE. Validate should be called usually before a save event
	 * is handled, to make sure everything went smoothly. 
	 * @access public
	 * @return boolean
	 */
	function validate () {
		$ok = true;
		foreach (array_keys($this->_collections) as $key) {
			foreach(array_keys($this->_collections[$key]) as $name) {
				if (!$this->_collections[$key][$name]->validate()) $ok = false;
			}
		}
		
		return $ok;
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
		if ($this->_addButton->getAllValues()) {
//			print "adding element.<br/>";
			$this->_addElement();
		}
		
		return $ok;
	}
	
	/**
	 * Returns the values of wizard-components. Should return an array if children are involved,
	 * otherwise a whatever type of object is expected.
	 * @access public
	 * @return mixed
	 */
	function getAllValues () {
		// make an array indexed by collection of all the values.
		$array = array();
		foreach (array_keys($this->_collections) as $key) {
			foreach(array_keys($this->_collections[$key]) as $name) {
				$array[$key][$name] = $this->_collections[$key][$name]->getAllValues();
			}
		}
		return $array;
	}
	
	/**
	 * Makes sure we have $num collections available.
	 * @param integer $num
	 * @access public
	 * @return void
	 */
	function _ensureNumber ($num) {
		$curr = count($this->_collections);
		if ($curr < $num) {
			for($i = $curr; $i < $num; $i++) {
				$this->_addElement();
			}
			$this->_num = $num;
		}
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
		// check if we have min/max values that are appropriate, etc.
		if ($this->_num < $this->_min) $this->_num = $this->_min;
		if ($this->_max != -1 && $this->_num > $this->_max) $this->_num = $this->_max;
		$this->_ensureNumber($this->_num);
		
		$includeAdd = !($this->_num == $this->_max);
		$includeRemove = !($this->_num == $this->_min);

		$m = "<table width='100%' border='0' cellspacing='0' cellpadding='2'>\n";
		
		foreach (array_keys($this->_collections) as $key) {
			$this->_collections[$key]["_remove"]->setEnabled($includeRemove);
			$m .= "<tr><td valign='top' style='border-bottom: 1px solid #555; width: 75px'>".$this->_collections[$key]["_remove"]->getMarkup($fieldName."_".$key."__remove")."</td><td style='border-bottom: 1px solid #555;'>";
			$m .= Wizard::parseText($this->_text, $this->_collections[$key], $fieldName."_".$key."_");
			$m .= "</td></tr>\n";
		}
		
		$this->_addButton->setEnabled($includeAdd);
		$m .= "<tr><td colspan='2'>".$this->_addButton->getMarkup($fieldName."_add")."</td></tr>\n";
		$m .= "</table>\n";
		return $m;
	}
    
}
?>