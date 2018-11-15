<?php
/**
* @since 7/20/05
 * @package polyphony.repository_importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLAssetIterator.class.php,v 1.8 2007/09/19 14:04:48 adamfranco Exp $
 */ 

require_once(DOMIT);

/**
* <##>
 * 
 * @since 7/20/05
 * @package polyphony.repository_importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLAssetIterator.class.php,v 1.8 2007/09/19 14:04:48 adamfranco Exp $
 */
class XMLAssetIterator 
extends HarmoniIterator 
{
	/**
	* Constructor
	 * 
	 * @param String $sourceDirectory
	 * @param object $parentRepositoryImporter
	 * @return object
	 * @access public
	 * @since 7/20/05
	 */
	function __construct ($srcDir, $parentRepositoryImporter) {		
		$import = new DOMIT_Document();
		
		if ($import->loadXML($srcDir."metadata.xml")) {
			if (!($import->documentElement->hasChildNodes()))
				$parentRepositoryImporter->addError("There are no assets to import in: ".$srcDir."metadata.xml.");

			$this->_assetList =$import->documentElement->childNodes;
			$this->_current = 0;
		}
		else
			$parentRepositoryImporter->addError("XML parse failed: ".$srcDir."metadata.xml does not exist or contains poorly formed XML.");
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
	function next() {
		$temp =$this->_assetList[$this->_current];
		$this->_current++;
		return $temp;
	}
}

?>