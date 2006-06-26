<?php

/**
 * @package polyphony.library.datamanager_gui
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 * @version $Id: PrimitiveIO.interface.php,v 1.6 2006/06/26 12:51:42 adamfranco Exp $
 **/

/**
 * Defines an interface for PrimitiveIO classes that allow interfacing between the {@link DataManager} and the {@link Wizard}.
 * Each PrimitiveIO class handles one data type (eg, "string" or "integer").
 *
 * @package polyphony.library.datamanager_gui
 * @copyright Copyright &copy; 2005, Middlebury College
 * @author Gabriel Schine
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 * @version $Id: PrimitiveIO.interface.php,v 1.6 2006/06/26 12:51:42 adamfranco Exp $
 */
class PrimitiveIO extends WizardComponent
{
	/**
	 * Sets the value of this Component to the {@link SObject} passed.
	 * @param ref object $value The {@link SObject} value to use.
	 *
	 * @return void
	 **/
	function setValue(&$value)
	{
		
	}

} // END class PrimitiveIO

?>