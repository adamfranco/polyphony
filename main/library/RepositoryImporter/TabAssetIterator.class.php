<?php
/**
* @since 7/20/05
 * @package Polyphony.RepositoryImporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: TabAssetIterator.class.php,v 1.2 2005/07/21 18:36:22 ndhungel Exp $
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
 * @version $Id: TabAssetIterator.class.php,v 1.2 2005/07/21 18:36:22 ndhungel Exp $
 */
class TabAssetIterator 
extends HarmoniIterator 
{
	/**
	* Constructor
	 * 
	 * @return object
	 * @access public
	 * @since 7/20/05
	 */
	function TabAssetIterator ($srcDir) {		
		$meta = fopen($srcDir."/metadata.txt", "r");
		fgets($meta);
		fgets($meta);
		
		$allAssetInfo = array();
		while ($line = ereg_replace("[\n\r]*$","",fgets($meta))) {
			$metadata = explode("\t", $line);
			$this->_assetList[] = $metadata;
		}
		
		fclose($meta);
		
		$this->_current = 0;
	}
	
	/**
	 * checks for existence of next element
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