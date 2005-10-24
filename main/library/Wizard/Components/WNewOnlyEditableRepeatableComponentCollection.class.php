<?php
/**
 * @since Aug 1, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WNewOnlyEditableRepeatableComponentCollection.class.php,v 1.1 2005/10/24 20:32:38 adamfranco Exp $
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
 * @version $Id: WNewOnlyEditableRepeatableComponentCollection.class.php,v 1.1 2005/10/24 20:32:38 adamfranco Exp $
 */

class WNewOnlyEditableRepeatableComponentCollection
	extends WRepeatableComponentCollection 
{
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
				if (!in_array($key, array("_remove", "_add")))
					$newCollection[$key]->setReadOnly(true);
		}
		
		return $newCollection;
	}
}
?>