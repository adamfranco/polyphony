<?php
/**
 * @since Aug 1, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WNewOnlyEditableRepeatableComponentCollection.class.php,v 1.3 2006/04/24 22:36:55 adamfranco Exp $
 */ 

/**
 * This component allows for the creation of repeatable components or groups of 
 * components. Only the newly added comonents are editable. Existing ones can only
 * be removed.
 * 
 * @since Aug 1, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WNewOnlyEditableRepeatableComponentCollection.class.php,v 1.3 2006/04/24 22:36:55 adamfranco Exp $
 */

class WNewOnlyEditableRepeatableComponentCollection
	extends WRepeatableComponentCollection 
{
	/**
	 * Adds a collection of {@link WizardComponent}s indexed by field name to the list of collections.
	 * This is useful when pre-populating the list with old/previous values.
	 * @param ref array $collection Indexed by field name.
	 * @param boolean $removable Can this collection be removed by the user?
	 * @access public
	 * @return ref array An array of the components created with the values passed.
	 */
	function &addValueCollection (&$collection, $removable = true) {
		// @todo - make sure that the correct fields/classes are represented
		$newCollection =& $this->_addElement($removable);
		foreach (array_keys($newCollection) as $key) {
			if (isset($collection[$key]))
				$newCollection[$key]->setValue($collection[$key]);
				if (!in_array($key, array("_remove", "_add")))
					$newCollection[$key]->setEnabled(false, true);
		}
		
		return $newCollection;
	}
}
?>