<?php
/**
 * @since Aug 1, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WOrderedRepeatableComponentCollection.class.php,v 1.6 2006/05/17 16:56:54 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/Wizard/Components/WSelectList.class.php");

/**
 * This component allows for the creation of ordered repeatable components or groups of components. 
 * 
 * @since Aug 1, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WOrderedRepeatableComponentCollection.class.php,v 1.6 2006/05/17 16:56:54 adamfranco Exp $
 */

class WOrderedRepeatableComponentCollection 
	extends WRepeatableComponentCollection 
{

    var $_orderedSet;
    var $_nextId;
    
    function WOrderedRepeatableComponentCollection() {
    	parent::WRepeatableComponentCollection();
    	$idManager =& Services::getService("Id");
    	$this->_orderedSet =& new OrderedSet($idManager->getId("unimportant"));
    	$this->_nextId = 0;
    }
    	
	/**
	 * Adds a new element to the end of the list.
	 * @param boolean $removable Can this collection be removed by the user?
	 * @access private
	 * @return void
	 */
	function &_addElement ($removable = true) {
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
		$newArray["_remove"]->setEnabled($removable, !$removable);

		$newArray["_moveup"] =& WEventButton::withLabel(
			dgettext("polyphony", "Move Up"));
		$newArray["_moveup"]->setParent($this);
		
		$newArray["_movedown"] =& WEventButton::withLabel(
			dgettext("polyphony", "Move Down"));
		$newArray["_movedown"]->setParent($this);
		
		$this->_collections[$this->_nextId] =& $newArray;
		$idManager =& Services::getService("Id");
		$this->_orderedSet->addItem($idManager->getId(strval($this->_nextId)));
		$this->_nextId++;
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
			$idManager =& Services::getService("Id");
			$this->_orderedSet->removeItem($idManager->getId(strval($key)));
			$this->_num--;
		}
	}
	
	/**
	 * Rebuild our position selects
	 * 
	 * @return void
	 * @access public
	 * @since 5/16/06
	 */
	function rebuildPositionSelects () {
		// Populate our position list;
		$positionList = new WSelectList;
		$js = '
			var choiceName = this.name + \'Choice\';
			for (var i = 0; i < this.form.elements.length; i++) {
				if (this.form.elements[i].name == choiceName) {
					this.form.elements[i].value = \'true\';
					break;
				}
			}
			
			this.form.submit();
		
		';
		$positionList->setOnChange(preg_replace("/\s{2,}/", " ", preg_replace("/[\n\r\t]/", " ", $js)));
		$i = 0;
		$this->_orderedSet->reset();
		while ($this->_orderedSet->hasNext()) {
			$this->_orderedSet->next();
			$positionList->addOption($i, $i+1);
			$i++;			
		}
		
		// Rebuild the position lists.
		$this->_orderedSet->reset();
		while ($this->_orderedSet->hasNext()) {
			$collectionId =& $this->_orderedSet->next();
			$key = $collectionId->getIdString();
			
			$this->_collections[$key]["_moveToPosition"] =& $positionList->deepCopy();
			$this->_collections[$key]["_moveToPosition"]->setParent($this);
			$this->_collections[$key]["_moveToPosition"]->setValue(
				strval($this->_orderedSet->getPosition($collectionId)));
			$this->_collections[$key]["_moveToPositionChoice"] =& WHiddenField::withValue('false');
			$this->_collections[$key]["_moveToPositionChoice"]->setParent($this);
		}
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
		$idManager =& Services::getService("Id");
		$ok = true;
//		$this->_removeElements(array(2));
		// first update all our components in the collections
		$toRemove = array();
		foreach(array_keys($this->_collections) as $key) {
			foreach(array_keys($this->_collections[$key]) as $name) {
// 				print "$name in $key is a ".gettype($this->_collections[$key][$name])."<br/>";
				$rule =& ExtendsValidatorRule::getRule("WizardComponent");
				if (!$rule->check($this->_collections[$key][$name])) continue;
				if (!$this->_collections[$key][$name]->update($fieldName."_".$key."_".$name)) 
					$ok = false;
			}
			if ($this->_collections[$key]["_remove"]->getAllValues())
				$toRemove[] = $key;
			if ($this->_collections[$key]["_moveup"]->getAllValues()) 
				$this->_orderedSet->moveUp($idManager->getId(strval($key)));
				
			$id = $idManager->getId(strval($key));
			if ($this->_collections[$key]["_movedown"]->getAllValues()) 
				$this->_orderedSet->moveDown($id);

			if (isset($this->_collections[$key]["_moveToPosition"])
				&& isset($this->_collections[$key]["_moveToPositionChoice"])
				&& $this->_collections[$key]["_moveToPositionChoice"]->getAllValues() == 'true') 
			{
				$this->_orderedSet->moveToPosition($id, 
					$this->_collections[$key]["_moveToPosition"]->getAllValues());
			}
		}
		$this->_removeElements($toRemove);
		
		// then, check if any "buttons" or anything were pressed to add/remove elements
		$this->_addButton->update($fieldName."_add");
		if ($this->_addButton->getAllValues()) {
//			print "adding element.<br/>";
			$this->_addElement();
		}
		
		$this->rebuildPositionSelects();
		
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
		$this->_orderedSet->reset();
		while ($this->_orderedSet->hasNext()) {
			$collectionId =& $this->_orderedSet->next();
			$key = $collectionId->getIdString();
			foreach(array_keys($this->_collections[$key]) as $name) {
				$array[$key][$name] = $this->_collections[$key][$name]->getAllValues();
			}
		}
		return $array;
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
		
		
		$this->_orderedSet->reset();
		while ($this->_orderedSet->hasNext()) {
			$collectionId =& $this->_orderedSet->next();
			$key = $collectionId->getIdString();
			
			$this->_collections[$key]["_remove"]->setEnabled($includeRemove);
			$m .= "<tr><td valign='top' style='border-bottom: 1px solid #555;'>";
			$m .= $this->_collections[$key]["_remove"]->getMarkup(
				$fieldName."_".$key."__remove");
			if ($this->_orderedSet->getPosition($collectionId) > 0)
				$m .= "\n<br/>".$this->_collections[$key]["_moveup"]->getMarkup(
					$fieldName."_".$key."__moveup");
			
			// Display the list
			$m .= "\n<br/>".$this->_collections[$key]["_moveToPosition"]->getMarkup($fieldName."_".$key."__moveToPosition");
			$m .= $this->_collections[$key]["_moveToPositionChoice"]->getMarkup($fieldName."_".$key."__moveToPositionChoice");
			
			if ($this->_orderedSet->hasNext())
				$m .= "\n<br/>".
					$this->_collections[$key]["_movedown"]->getMarkup(
					$fieldName."_".$key."__movedown");
			$m .= "</td><td style='border-bottom: 1px solid #555;'>";
			$m .= Wizard::parseText($this->_text, $this->_collections[$key],
				$fieldName."_".$key."_");
			$m .= "</td></tr>\n";
		}
		
		$this->_addButton->setEnabled($includeAdd);
		$m .= "<tr><td colspan='2'>".$this->_addButton->getMarkup($fieldName."_add")."</td></tr>\n";
		$m .= "</table>\n";
		return $m;
	}
    
}
?>