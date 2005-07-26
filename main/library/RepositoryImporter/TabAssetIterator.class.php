<?php
/**
* @since 7/20/05
 * @package Polyphony.RepositoryImporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: TabAssetIterator.class.php,v 1.4 2005/07/26 21:31:22 cws-midd Exp $
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
 * @version $Id: TabAssetIterator.class.php,v 1.4 2005/07/26 21:31:22 cws-midd Exp $
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
	function TabAssetIterator ($srcDir, $parentRepositoryImporter) {		
		if ($meta = fopen($srcDir."/metadata.txt", "r")) {
			fgets($meta);
			fgets($meta);
			
			while ($line = ereg_replace("[\n\r]*$","",fgets($meta))) {
				$metadata = explode("\t", $line);
				$this->_assetList[] = $metadata;
			}
			
			if (count($this->_assetList) == 0)
				$parentRepositoryImporter->addError("There are no assets to import in: ".$srcDir."/metadata.txt.");

			fclose($meta);
		$this->_current = 0;
		}
		else
			$parentRepositoryImporter->addError("Tab-Delimited parse failed: ".$srcDir."/metadata.txt does not exist or is unreadable.");
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