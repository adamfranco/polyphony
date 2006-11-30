<?php
/**
* @since 7/20/05
 * @package polyphony.library.repository_importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ExifAssetIterator.class.php,v 1.5 2006/11/30 22:02:39 adamfranco Exp $
 */ 

/**
* <##>
 * 
 * @since 7/20/05
 * @package polyphony.library.repository_importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ExifAssetIterator.class.php,v 1.5 2006/11/30 22:02:39 adamfranco Exp $
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
		$this->_addFiles($srcDir);		
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
	
	/**
	 * Add files to our list (recursively?).
	 * 
	 * @param string $dirName
	 * @return void
	 * @access public
	 * @since 7/20/06
	 */
	function _addFiles ($dirName) {
		$toIgnore = array(
			"schema.xml",
			"__MACOSX",
			".DS_Store"
		);
		$dir = opendir($dirName);
		while($file = readdir($dir)) {
			if (ereg('^[^\.]', $file) && !in_array($file, $toIgnore)) {
				if (!is_dir($file))
					$this->_assetList[] = $dirName.$file;
// 				else
// 					$this->_addFiles($file);
			}
		}
		closedir($dir);
	}
}

?>