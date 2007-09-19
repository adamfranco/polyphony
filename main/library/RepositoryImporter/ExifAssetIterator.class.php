<?php
/**
* @since 7/20/05
 * @package polyphony.repository_importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ExifAssetIterator.class.php,v 1.8 2007/09/19 14:04:48 adamfranco Exp $
 */ 

/**
* <##>
 * 
 * @since 7/20/05
 * @package polyphony.repository_importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ExifAssetIterator.class.php,v 1.8 2007/09/19 14:04:48 adamfranco Exp $
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
	function next() {
		$temp =$this->_assetList[$this->_current];
		$this->_current++;
		return $temp;
	}
	
	/**
	 * Add files to our list.
	 * 
	 * @param string $dirName
	 * @return void
	 * @access public
	 * @since 7/20/06
	 */
	function _addFiles ($dirName) {
		$dirName = preg_replace('/\/$/', '', $dirName).'/';
		$toIgnore = array(
			"schema.xml",
			"__MACOSX",
			".DS_Store"
		);
		$dir = opendir($dirName);
		while($file = readdir($dir)) {
			if (ereg('^[^\.]', $file) && !in_array($file, $toIgnore)) {
				$path = $dirName.$file;
				if (!is_dir($path))
					$this->_assetList[] = $path;
				else
					$this->_addFiles($path);
			}
		}
		closedir($dir);
	}
}

?>