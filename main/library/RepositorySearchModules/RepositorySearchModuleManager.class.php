<?php
/**
 *
 * @package polyphony.repository.search
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: RepositorySearchModuleManager.class.php,v 1.11 2007/09/19 14:04:48 adamfranco Exp $
 */

/**
 * Require our modules
 * 
 */
require_once(dirname(__FILE__)."/modules/SimpleFieldModule.class.php");
require_once(dirname(__FILE__)."/modules/PartAndValuesModule.class.php");

/**
 * The RepositorySearchModuleManager is responcible for sending requests for search forms
 * to the appropriate RepositorySearchModule based on their types.
 * 
 * @package polyphony.repository.search
 * @version $Id: RepositorySearchModuleManager.class.php,v 1.11 2007/09/19 14:04:48 adamfranco Exp $
 * @since $Date: 2007/09/19 14:04:48 $
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
		$this->_modules["Repository::edu.middlebury.harmoni::Keyword"] = new SimpleFieldModule("Keyword");
		$this->_modules["Repository::edu.middlebury.harmoni::DisplayName"] = new SimpleFieldModule("DisplayName");
		$this->_modules["Repository::edu.middlebury.harmoni::Authoritative Values"] = new PartAndValuesModule("PartId", "AuthValue");
		$this->_modules["Repository::edu.middlebury.harmoni::AssetType"] = new SimpleFieldModule("AssetType");
		$this->_modules["Repository::edu.middlebury.harmoni::RootAssets"] = new SimpleFieldModule("RootAssets");
		$this->_modules["Repository::edu.middlebury.harmoni::Description"] = new SimpleFieldModule("Description");
		$this->_modules["Repository::edu.middlebury.harmoni::Content"] = new SimpleFieldModule("Content");
		$this->_modules["Repository::edu.middlebury.harmoni::AllCustomStructures"] = new SimpleFieldModule("AllCustomStructures");
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
	function assignConfiguration ( $configuration ) { 
		$this->_configuration =$configuration;
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
	function getOsidContext () { 
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
	function assignOsidContext ( $context ) { 
		$this->_osidContext =$context;
	} 

	/**
	 * Create a form for searching.
	 * 
	 * @param object Repository $repository
	 * @param object $searchType
	 * @param string $action The destination on form submit.
	 * @return string
	 * @access public
	 * @since 10/19/04
	 */
	function createSearchForm ( $repository, $searchType, $action) {
		ArgumentValidator::validate($repository, new ExtendsValidatorRule("Repository"));
		ArgumentValidator::validate($searchType, new ExtendsValidatorRule("Type"));
		ArgumentValidator::validate($action, new StringValidatorRule);
		
		$typeKey = $searchType->getDomain()
					."::".$searchType->getAuthority()
					."::".$searchType->getKeyword();
		
		if (!is_object($this->_modules[$typeKey]))
			throwError(new Error("Unsupported Search Type, '$typeKey'", "RepositorySearchModuleManager", true));
		
		return $this->_modules[$typeKey]->createSearchForm($repository, $action);
		
	}
	
	/**
	 * Create a form for searching.
	 * 
	 * @param object Repository $repository
	 * @param object $searchType
	 * @param string $action The destination on form submit.
	 * @return string
	 * @access public
	 * @since 10/19/04
	 */
	function createSearchFields ( $repository, $searchType) {
		ArgumentValidator::validate($repository, new ExtendsValidatorRule("Repository"));
		ArgumentValidator::validate($searchType, new ExtendsValidatorRule("Type"));
		
		$typeKey = $searchType->getDomain()
					."::".$searchType->getAuthority()
					."::".$searchType->getKeyword();
		
		if (!is_object($this->_modules[$typeKey]))
			throwError(new Error("Unsupported Search Type, '$typeKey'", "RepositorySearchModuleManager", true));
		
		return $this->_modules[$typeKey]->createSearchFields($repository);
	}
	
	/**
	 * Get the formatted search terms based on the submissions of the form
	 * 
	 * @param object $searchType
	 * @return mixed
	 * @access public
	 * @since 10/28/04
	 */
	function getSearchCriteria ( $repository, $searchType ) {
		ArgumentValidator::validate($searchType, new ExtendsValidatorRule("Type"));
				
		$typeKey = $searchType->getDomain()
					."::".$searchType->getAuthority()
					."::".$searchType->getKeyword();
		
		if (!is_object($this->_modules[$typeKey]))
			throwError(new Error("Unsupported Search Type, '$typeKey'", "RepositorySearchModuleManager", true));
		
		return $this->_modules[$typeKey]->getSearchCriteria($repository);
	}
	
	/**
	 * Get an array of the current values to be added to a url. The keys of the
	 * arrays are the field-names in the appropriate context.
	 * 
	 * @param object $searchType
	 * @return array
	 * @access public
	 * @since 10/28/04
	 */
	function getCurrentValues ( $searchType ) {
		ArgumentValidator::validate($searchType, new ExtendsValidatorRule("Type"));
				
		$typeKey = $searchType->getDomain()
					."::".$searchType->getAuthority()
					."::".$searchType->getKeyword();
		
		if (!is_object($this->_modules[$typeKey]))
			throwError(new Error("Unsupported Search Type, '$typeKey'", "RepositorySearchModuleManager", true));
		
		return $this->_modules[$typeKey]->getCurrentValues();
	}
	
	/**
	 * Update the current values with data (maybe stored in the session for instance. 
	 * The keys of the arrays are the field-names in the appropriate context.
	 * This could have been originally fetched via getCurrentValues
	 * 
	 * @param object $searchType
	 * @param array $values
	 * @return array
	 * @access public
	 * @since 10/28/04
	 */
	function setCurrentValues ( $searchType, $values ) {
		ArgumentValidator::validate($searchType, new ExtendsValidatorRule("Type"));
				
		$typeKey = $searchType->getDomain()
					."::".$searchType->getAuthority()
					."::".$searchType->getKeyword();
		
		if (!is_object($this->_modules[$typeKey]))
			throwError(new Error("Unsupported Search Type, '$typeKey'", "RepositorySearchModuleManager", true));
		
		return $this->_modules[$typeKey]->setCurrentValues($values);
	}
}

?>