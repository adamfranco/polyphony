<?php
/**
* @since 7/20/05
 * @package Polyphony.RepositoryImporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ExifAssetIterator.class.php,v 1.1 2005/07/26 15:24:39 ndhungel Exp $
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
 * @version $Id: ExifAssetIterator.class.php,v 1.1 2005/07/26 15:24:39 ndhungel Exp $
 */

class ExifAssetIterator
	extends HarmoniIterator
{

	/**
	 * Constructor
	 * 
	 * @return object
	 * @access public
	 * @since 7/20/05
	 */
	function ExifAssetIterator ($srcDir) {		
		$dir = opendir($srcDir);
		
		while($file = readdir($dir)) {
			if($file != "schema.txt" && $file != "." && $file != "..")
				$this->_assetList[] = $srcDir."/".$file;
		}
		closedir($dir);		
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