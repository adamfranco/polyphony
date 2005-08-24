<?php

/**
 * @package polyphony.library.datamanager_gui
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 * @version $Id: PrimitiveIO.interface.php,v 1.5 2005/08/24 14:34:42 cws-midd Exp $
 **/

/**
 * Defines an interface for PrimitiveIO classes that allow interfacing between the {@link DataManager} and the {@link Wizard}.
 * Each PrimitiveIO class handles one data type (eg, "string" or "integer").
 *
 * @package default
 * @copyright Copyright &copy; 2005, Middlebury College
 * @author Gabriel Schine
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 * @version $Id: PrimitiveIO.interface.php,v 1.5 2005/08/24 14:34:42 cws-midd Exp $
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