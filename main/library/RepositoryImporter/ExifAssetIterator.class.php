<?php
/**
* @since 7/20/05
 * @package Polyphony.RepositoryImporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ExifAssetIterator.class.php,v 1.3 2005/08/11 18:27:20 ndhungel Exp $
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
 * @version $Id: ExifAssetIterator.class.php,v 1.3 2005/08/11 18:27:20 ndhungel Exp $
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
			if($file != "schema.xml" && $file != "." && $file != ".." && !is_dir($file))
				$this->_assetList[] = $srcDir.$file;
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