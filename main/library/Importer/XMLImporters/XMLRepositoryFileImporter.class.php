<?php
/**
 * @since 10/18/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLRepositoryFileImporter.class.php,v 1.1 2005/10/18 19:57:24 cws-midd Exp $
 */ 

require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLRepositoryImporter.class.php");

/**
 * XMLRepositoryFileImporter imports a repository via delegation to subclasses
 * 
 * @since 10/18/05
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLRepositoryFileImporter.class.php,v 1.1 2005/10/18 19:57:24 cws-midd Exp $
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
	function XMLRepositoryFileImporter () {
		parent::XMLImporter();
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
	 * Organizes the import
	 * 
	 * @param object DOMIT_Node
	 * @param string
	 * @param object mixed
	 * @param string
	 * @return object HarmoniId
	 * @access public
	 * @since 10/5/05
	 */
	function import (&$node, $type = null, &$parent) {
		$path = $node->getText();
		if (!ereg("^(([:alpha:]+://)|([:alpha:]+:\\)|/)", $path))
			$path = $this->_node->ownerDocument->xmlPath.$path;
		
		$imp =& XMLRepositoryImporter::withFile($path);
		$imp->parseAndImport();
	}
