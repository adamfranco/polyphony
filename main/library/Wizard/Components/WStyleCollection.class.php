<?php
/**
 * @since 6/2/06
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WStyleCollection.class.php,v 1.1 2006/06/02 16:00:28 cws-midd Exp $
 */ 

/**
 * a wizard representation for style collections
 * 
 * @since 6/2/06
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WStyleCollection.class.php,v 1.1 2006/06/02 16:00:28 cws-midd Exp $
 */
class WStyleCollection extends WRepeatableComponentCollection {
		
	var $this->_addSP;
	var $this->_availableSPs;
    var $_SPs = array();

		
	/**
	 * constructor
	 * 
	 * @param ref object StyleCollection $style
	 * @return void
	 * @access public
	 * @since 6/2/06
	 */
	function WStyleCollection (&$style) {
		$gui =& Services::getService("GUI");
		
		// grab the list of supported SPs
		$this->_supportedSPs = $gui->getSupportedSPs();

		// grab the array of currently instantiated SPs
		$SPs =& $style->getSPs();
		
		// make a list  of SPs that can be added
		$this->_availableSPs = array_diff($this->_supportedSPs, array_keys($SPs));
		
		// initialize the array of SPs to the existant ones
		foreach (array_keys($SPs) as $key) {
			$this->_addSP($SPs[$key]);
		}
		
		
	}
	
	/**
	 * sets the available SPs array
	 * 
	 * @return void
	 * @access public
	 * @since 6/2/06
	 */
	function updateAvailableSPs () {
		$this->_availableSPs = array_diff($this->_supportedSPs, array_keys($this->_collections));
	}
	
	/**
	 * adds the wizard rep for the SP passed to the collection
	 * 
	 * @param ref object StyleProperty
	 * @return void
	 * @access public
	 * @since 6/2/06
	 */
	function _addSP (&$SP, $removable = 'true') {
		$this->_collections
	}
}

?>