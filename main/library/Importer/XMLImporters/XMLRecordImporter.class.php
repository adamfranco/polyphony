<?php
/**
 * @since 9/12/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLRecordImporter.class.php,v 1.2 2005/09/22 17:33:36 cws-midd Exp $
 */ 

require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLPartImporter.class.php");

/**
 * XMLAssetImporter imports an asset into a repository
 * 
 * @since 9/12/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLRecordImporter.class.php,v 1.2 2005/09/22 17:33:36 cws-midd Exp $
 */
class XMLRecordImporter extends XMLImporter {
		
	/**
	 * Constructor
	 * 
	 * @param object DOMIT_Node
	 * @param object HarmoniRepository
	 * @access public
	 * @since 9/12/05
	 */
	function XMLRecordImporter (&$element, &$asset) {
		$this->_node =& $element;
		$this->_childImporterList = array("XMLPartImporter");
		$this->_childElementLIst = array("part");
		$this->_asset =& $asset;
	}
	
	/**
	 * Filters nodes of incorrect type
	 * 
	 * @param object DOMIT_Node
	 * @return boolean
	 * @static
	 * @access public
	 * @since 9/12/05
	 */
	function isImportable (&$element) {
		if ($element->nodeName == "record")
			return true;
		else
			return false;
	}

	/**
	 * Imports the current node's information
	 * 
	 * @access public
	 * @since 9/12/05
	 */
	function importNode () {
		$idManager =& Services::getService("Id");
		
		$this->getNodeInfo();
		
		if (!$this->_node->hasAttribute("id"))
			$this->_record =& $this->_asset->createRecord(
				$this->_info['recordStructureId']);
		else {
			$idString = $this->_node->getAttribute("id");
			$id =& $idManager->getId($idString);
			$this->_record =& $this->_asset->getRecord($id);
			if ($this->_type == "update")
				$this->update();
		}
	}

	/**
	 * Sets the node's internal information
	 * 
	 * @access public
	 * @since 9/12/05
	 */
	function getNodeInfo () {
		$repository =& $this->_asset->getRepository();
		$repId =& $repository->getId();
		$idString = $repId->getIdString();
		$dbHandler =& Services::getService("DBHandler");
		$dbIndexConcerto =& $dbHandler->addDatabase(new 
			MySQLDatabase("localhost", "whitey_concerto", "test", "test"));
		$query =& new SelectQuery;
		$query->addTable("temp_xml_matrix");
		$query->addColumn("conc_id");
		$query->addColumn("xml_id");
		$id =& $this->_node->getAttribute("xml:id");
		$query->addWhere("xml_id = '".addslashes($id)."'");
		
		$dbHandler->connect($dbIndexConcerto);
		$results =& $dbHandler->query($query, $dbIndexConcerto);
		if ($results->getNumberOfRows() == 1)
		{
			$result =& $results->next();
			$idManager =& Services::getService("Id");
			$id =& $idManager->getId($result['conc_id']);
			$this->_info['recordStructureId'] =& $id;
			$results->free();
		}
		else {
			$results->free();
			throwError(new Error("Bad XML IDREF: ".$id, "Importer", TRUE));
		}
		$dbHandler->disconnect($dbIndexConcerto);
	}
	
	/**
	 * Relegates Children to their classes
	 * 
	 * @access public
	 * @since 9/12/05
	 */
	function relegateChildren () {
		foreach ($this->_node->childNodes as $element)
			foreach ($this->_childImporterList as $importer) {
				eval('$result = '.$importer.'::isImportable($element);');
				if ($result) {
					$imp =& new $importer($element, $this->_record, 
						$this->_asset);
					$imp->import($this->_type);
					unset($imp);
				}
			}
	}
	
	/**
	 * Looks for discrepencies between imported data and current data
	 * 
	 * @access public
	 * @since 9/12/05
	 */
	function update () {
	}
}

?>