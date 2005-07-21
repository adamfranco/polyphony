<?php
/**
* @since 7/20/05
 * @package Polyphony.RepositoryImporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLAssetIterator.class.php,v 1.1 2005/07/21 13:59:46 cws-midd Exp $
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
 * @version $Id: XMLAssetIterator.class.php,v 1.1 2005/07/21 13:59:46 cws-midd Exp $
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
		$import =& new DOMIT_Document();																					// instantiate new DOMIT_Document
		
		if ($import->loadXML($srcDir."/metadata.xml")) {																	// parse the file
			if (!($import->documentElement->hasChildNodes()))																// check for assets
				throwError(new Error("There are no assets to import", "polyphony.RepositoryImporter", true));
		}
		else
			throwError(new Error("XML parse failed", "polyphony.RepositoryImporter", true));
		
		$this->_assetList =& $import->documentElement->childNodes;
		$this->_current = 0;
	}
	
	/**
	* 
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
	* returns the next ... element
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