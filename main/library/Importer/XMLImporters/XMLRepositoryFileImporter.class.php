<?php
/**
 * @since 10/18/05
 * @package polyphony.library.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLRepositoryFileImporter.class.php,v 1.8 2007/09/04 20:28:01 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLRepositoryImporter.class.php");

/**
 * XMLRepositoryFileImporter imports a repository via delegation to subclasses
 * 
 * @since 10/18/05
 * @package polyphony.library.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLRepositoryFileImporter.class.php,v 1.8 2007/09/04 20:28:01 adamfranco Exp $
 */
class XMLRepositoryFileImporter extends XMLImporter {

	/**
	 * 	Constructor
	 * 
	 * 
	 * @return object XMLRepositoryImporter
	 * @access public
	 * @since 10/5/05
	 */
	function XMLRepositoryFileImporter ($existingArray) {
		parent::XMLImporter($existingArray);
	}

	/**
	 * Sets up importer's self-knowledge
	 * 
	 * @access public
	 * @since 10/5/05
	 */
	function setupSelf () {
		$this->_childImporterList = array("XMLRepositoryImporter");
		$this->_childElementList = array("repository");
		$this->_info = array();
	}

	/**
	 * Filters nodes of incorrect type
	 * 
	 * @param object DOMIT_Node
	 * @return boolean
	 * @static
	 * @access public
	 * @since 10/5/05
	 */
	function isImportable ($element) {
		if($element->nodeName == "repositoryfile")
			return true;
		else
			return false;
	}


	/**
	 * Organizes the import
	 * 
	 * @param object mixed $topImporter is the importer instance that parsed the XML
	 * @param object DOMIT_Node
	 * @param string
	 * @param object mixed
	 * @param string
	 * @return object HarmoniId
	 * @access public
	 * @since 10/5/05
	 */
	function import ($topImporter, $node, $type, $parent) {
		$path = $node->getText();
		if (!ereg("^([a-zA-Z]+://|[a-zA-Z]+:\\|/)", $path))
			$path = $node->ownerDocument->xmlPath.$path;
	// @todo keep the topImporter passing down to new importer hierarchies!!!
		$imp = XMLRepositoryImporter::withFile($this->_existingArray, $path,
			$type);
		$imp->parseAndImport("asset");
		unset($imp);
	}
}