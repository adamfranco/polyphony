<?php
/**
* @since 7/20/05
 * @package Polyphony.RepositoryImporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLAssetIterator.class.php,v 1.2 2005/07/21 18:36:22 ndhungel Exp $
 */ 

/**
* <##>
 * 
 * @since 7/20/05
 * @package Polyphony.RepositoryImporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLAssetIterator.class.php,v 1.2 2005/07/21 18:36:22 ndhungel Exp $
 */
class XMLAssetIterator 
extends HarmoniIterator 
{
	/**
	* Constructor
	 * 
	 * @return object
	 * @access public
	 * @since 7/20/05
	 */
	function XMLAssetIterator ($srcDir) {		
		$import =& new DOMIT_Document();
		
		if ($import->loadXML($srcDir."/metadata.xml")) {
			if (!($import->documentElement->hasChildNodes()))
				throwError(new Error("There are no assets to import", "polyphony.RepositoryImporter", true));
		}
		else
			throwError(new Error("XML parse failed", "polyphony.RepositoryImporter", true));
		
		$this->_assetList =& $import->documentElement->childNodes;
		$this->_current = 0;
	}
	
	/**
	 * checks if the next element exists
	 * 
	 * @return boolean
	 * @access public
	 * @since 7/20/05
	 */
	function hasNext () {
		if ($this->_current < count($this->_assetList))
			return true;
		else
			return false;
	}
	
	/**
	* returns the next element
	 * 
	 * @return object DOMIT_Node
	 * @access public
	 * @since 7/20/05
	 */
	function &next() {
		$temp =& $this->_assetList[$this->_current];
		$this->_current++;
		return $temp;
	}
}

?>