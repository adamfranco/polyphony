<?php
/**
 * @package polyphony.library.datamanager_gui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PrimitiveIO.interface.php,v 1.3 2005/04/07 17:07:41 adamfranco Exp $
 */

/**
 * Privides an interface for any classes that will allow input or output between a certain DataType (in the DataManager of Harmoni) and the end user through HTML/browser.
 *
 * @package polyphony.library.datamanager_gui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PrimitiveIO.interface.php,v 1.3 2005/04/07 17:07:41 adamfranco Exp $
 * @author Gabe Schine
 */
class PrimitiveIO {
	/**
	 * Creates HTML form code from the value contained in $primitive specific to $index in $label.
	 * @param ref object $primitive A primitive data type object. $primitive may be null and should be handled accordingly by printing out form elements without values.
	 * @param string $label The label
	 * @param integer $index The index within $label
	 * @return string
	 */
	function mkFormHTML(&$primitive, $label, $index) {}

	/**
	 * Returns a primitive data type based on the form input contained in the FieldSet $fieldSet,
	 * specific to $index in $label. The input will correspond with the form code created in
	 * {@link PrimitiveIO::mkFormHTML()}.
	 * @param ref object $fieldSet A FieldSet object containing form input.
	 * @param string $label
	 * @param integer $index
	 * @return ref object a new Primitive Data Type object. should return NULL if data cannot be found in the form input.
	 */
	function &mkPrimitiveFromFormInput(&$fieldSet, $label, $index) {}
}