<?php
/**
 *
 * @package polyphony.library.repository.search
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: RepositorySearchModuleManager.class.php,v 1.4 2005/08/11 18:27:21 ndhungel Exp $
 */

/**
 * Require our modules
 * 
 */
require_once(dirname(__FILE__)."/modules/SimpleFieldModule.class.php");
//require_once(dirname(__FILE__)."/modules/HarmoniFileModule.class.php");

/**
 * The RepositorySearchModuleManager is responcible for sending requests for search forms
 * to the appropriate RepositorySearchModule based on their types.
 * 
 * @package polyphony.library.repository.search
 * @version $Id: RepositorySearchModuleManager.class.php,v 1.4 2005/08/11 18:27:21 ndhungel Exp $
 * @since $Date: 2005/08/11 18:27:21 $
 * @copyright 2004 Middlebury College
 */

class RepositorySearchModuleManager {

	/**
	 * Constructor, set up the relations of the Types to Modules
	 * 
	 * @return object
	 * @access public
	 * @since 10/19/04
	 */
	function RepositorySearchModuleManager () {
		$this->_modules = array();
		$this->_modules["Repository::edu.middlebury.harmoni::AssetType"] =& new SimpleFieldModule("AssetType");
		$this->_modules["Repository::edu.middlebury.harmoni::RootAssets"] =& new SimpleFieldModule("RootAssets");
		$this->_modules["Repository::edu.middlebury.harmoni::DisplayName"] =& new SimpleFieldModule("DisplayName");
		$this->_modules["Repository::edu.middlebury.harmoni::Description"] =& new SimpleFieldModule("Description");
		$this->_modules["Repository::edu.middlebury.harmoni::Content"] =& new SimpleFieldModule("Content");
		$this->_modules["Repository::edu.middlebury.harmoni::AllCustomStructures"] =& new SimpleFieldModule("AllCustomStructures");
	}
		
	/**
	 * Assign the configuration of this Manager. Valid configuration options are as
	 * follows:
	 *	database_index			integer
	 *	database_name			string
	 * 
	 * @param object Properties $configuration (original type: java.util.Properties)
	 * 
	 * @throws object OsidException An exception with one of the following
	 *		   messages defined in org.osid.OsidException:	{@link
	 *		   org.osid.OsidException#OPERATION_FAILED OPERATION_FAILED},
	 *		   {@link org.osid.OsidException#PERMISSION_DENIED
	 *		   PERMISSION_DENIED}, {@link
	 *		   org.osid.OsidException#CONFIGURATION_ERROR
	 *		   CONFIGURATION_ERROR}, {@link
	 *		   org.osid.OsidException#UNIMPLEMENTED UNIMPLEMENTED}, {@link
	 *		   org.osid.OsidException#NULL_ARGUMENT NULL_ARGUMENT}
	 * 
	 * @access public
	 */
	function assignConfiguration ( &$configuration ) { 
		$this->_configuration =& $configuration;
	}

	/**
	 * Return context of this OsidManager.
	 *	
	 * @return object OsidContext
	 * 
	 * @throws object OsidException 
	 * 
	 * @access public
	 */
	function &getOsidContext () { 
		return $this->_osidContext;
	} 

	/**
	 * Assign the context of this OsidManager.
	 * 
	 * @param object OsidContext $context
	 * 
	 * @throws object OsidException An exception with one of the following
	 *		   messages defined in org.osid.OsidException:	{@link
	 *		   org.osid.OsidException#NULL_ARGUMENT NULL_ARGUMENT}
	 * 
	 * @access public
	 */
	function assignOsidContext ( &$context ) { 
		$this->_osidContext =& $context;
	} 

	/**
	 * Create a form for searching.
	 * 
	 * @param object $searchType
	 * @param string $action The destination on form submit.
	 * @return string
	 * @access public
	 * @since 10/19/04
	 */
	function createSearchForm ( & $searchType, $action ) {
		ArgumentValidator::validate($searchType, new ExtendsValidatorRule("HarmoniType"));
		ArgumentValidator::validate($action, new StringValidatorRule);
		
		$typeKey = $searchType->getDomain()
					."::".$searchType->getAuthority()
					."::".$searchType->getKeyword();
		
		if (!is_object($this->_modules[$typeKey]))
			throwError(new Error("Unsupported Search Type, '$typeKey'", "RepositorySearchModuleManager", true));
		
		return $this->_modules[$typeKey]->createSearchForm($action);
	}
	
	/**
	 * Get the formatted search terms based on the submissions of the form
	 * 
	 * @param object $searchType
	 * @return mixed
	 * @access public
	 * @since 10/28/04
	 */
	function getSearchCriteria ( & $searchType ) {
		ArgumentValidator::validate($searchType, new ExtendsValidatorRule("HarmoniType"));
				
		$typeKey = $searchType->getDomain()
					."::".$searchType->getAuthority()
					."::".$searchType->getKeyword();
		
		if (!is_object($this->_modules[$typeKey]))
			throwError(new Error("Unsupported Search Type, '$typeKey'", "RepositorySearchModuleManager", true));
		
		return $this->_modules[$typeKey]->getSearchCriteria();
	}
	
	/**
	 * Start the service
	 * 
	 * @return void
	 * @access public
	 * @since 6/28/04
	 */
	function start () {
		
	}
	
	/**
	 * Stop the service
	 * 
	 * @return void
	 * @access public
	 * @since 6/28/04
	 */
	function stop () {
		
	}
}

?>