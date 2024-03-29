<?php
/**
* @since 7/20/05
 * @package polyphony.repository_importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: TabAssetIterator.class.php,v 1.12 2007/09/19 14:04:48 adamfranco Exp $
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
 * @version $Id: TabAssetIterator.class.php,v 1.12 2007/09/19 14:04:48 adamfranco Exp $
 */
class TabAssetIterator 
extends HarmoniIterator 
{
	/**
	 * Index for iterator
	 *
	 * @var integer $_current
	 */
	 
	var $_current;
	/**
	 * Array for Iterator
	 *
	 * @var array $_assetList
	 */
	var $_assetList;
	
	/**
	 * Constructor
	 * 
	 * @return object
	 * @access public
	 * @since 7/20/05
	 */
	function __construct ($srcDir, $parentRepositoryImporter) {		
		if (Services::serviceRunning("Logging")) {
			$loggingManager = Services::getService("Logging");
			$log =$loggingManager->getLogForWriting("Harmoni");
			$formatType = new Type("logging", "edu.middlebury", "AgentsAndNodes",
							"A format in which the acting Agent[s] and the target nodes affected are specified.");
			$priorityType = new Type("logging", "edu.middlebury", "Error",
							"Events involving critical system errors.");
		}			
		if (file_exists($srcDir."metadata.txt") && 
			$meta = fopen($srcDir."metadata.txt", "r")) 
		{
			fgets($meta);
			fgets($meta);
			
			while ($line = preg_replace("/[\n\r]*$/","",fgets($meta))) {
				$metadata = explode("\t", $line);
				$this->_assetList[] = $metadata;
			}
			
			if (count($this->_assetList) == 0) {
				$parentRepositoryImporter->addError("There are no assets to import in: ".$srcDir."metadata.txt.");
				if (isset($log)) {
					$item = new AgentNodeEntryItem("TabImporter Error",
						"There are no assets to import in: $srcDir/metadata.txt.");
					$log->appendLogWithTypes($item, $formatType, $priorityType);
				}
			}
			fclose($meta);
				$this->_current = 0;
		}
		else {
			$parentRepositoryImporter->addError("Tab-Delimited parse failed: ".$srcDir."metadata.txt does not exist or is unreadable.");
			if (isset($log)) {
				$item = new AgentNodeEntryItem("TabImporter Error",
					"Tab-Delimited parse failed: $srcDir/metadata.txt does not exist or is unreadable.");
				$log->appendLogWithTypes($item, $formatType, $priorityType);
			}
		}
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
}

?>