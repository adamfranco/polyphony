<?php

require_once(dirname(__FILE__)."/modules/SimpleFieldModule.class.php");
//require_once(dirname(__FILE__)."/modules/HarmoniFileModule.class.php");

/**
 * The DRSearchModuleManager is responcible for sending requests for search forms
 * to the appropriate DRSearchModule based on their types.
 * 
 * @package polyphony.dr.search
 * @version $Id: DRSearchModuleManager.class.php,v 1.2 2004/11/02 22:24:26 adamfranco Exp $
 * @date $Date: 2004/11/02 22:24:26 $
 * @copyright 2004 Middlebury College
 */

class DRSearchModuleManager {

	/**
	 * Constructor, set up the relations of the Types to Modules
	 * 
	 * @return object
	 * @access public
	 * @date 10/19/04
	 */
	function DRSearchModuleManager () {
		$this->_modules = array();
		$this->_modules["DR::Harmoni::AssetType"] =& new SimpleFieldModule("AssetType");
		$this->_modules["DR::Harmoni::RootAssets"] =& new SimpleFieldModule("RootAssets");
		$this->_modules["DR::Harmoni::DisplayName"] =& new SimpleFieldModule("DisplayName");
		$this->_modules["DR::Harmoni::Description"] =& new SimpleFieldModule("Description");
		$this->_modules["DR::Harmoni::Content"] =& new SimpleFieldModule("Content");
		$this->_modules["DR::Harmoni::AllCustomStructures"] =& new SimpleFieldModule("AllCustomStructures");
	}
		
	/**
	 * Create a form for searching.
	 * 
	 * @param object $searchType
	 * @param string $action The destination on form submit.
	 * @return string
	 * @access public
	 * @date 10/19/04
	 */
	function createSearchForm ( & $searchType, $action ) {
		ArgumentValidator::validate($searchType, new ExtendsValidatorRule("HarmoniType"));
		ArgumentValidator::validate($action, new StringValidatorRule);
		
		$typeKey = $searchType->getDomain()
					."::".$searchType->getAuthority()
					."::".$searchType->getKeyword();
		
		if (!is_object($this->_modules[$typeKey]))
			throwError(new Error("Unsupported Search Type, '$typeKey'", "DRSearchModuleManager", true));
		
		return $this->_modules[$typeKey]->createSearchForm($action);
	}
	
	/**
	 * Get the formatted search terms based on the submissions of the form
	 * 
	 * @param object $searchType
	 * @return mixed
	 * @access public
	 * @date 10/28/04
	 */
	function getSearchCriteria ( & $searchType ) {
		ArgumentValidator::validate($searchType, new ExtendsValidatorRule("HarmoniType"));
				
		$typeKey = $searchType->getDomain()
					."::".$searchType->getAuthority()
					."::".$searchType->getKeyword();
		
		if (!is_object($this->_modules[$typeKey]))
			throwError(new Error("Unsupported Search Type, '$typeKey'", "DRSearchModuleManager", true));
		
		return $this->_modules[$typeKey]->getSearchCriteria();
	}
	
	/**
	 * Start the service
	 * 
	 * @return void
	 * @access public
	 * @date 6/28/04
	 */
	function start () {
		
	}
	
	/**
	 * Stop the service
	 * 
	 * @return void
	 * @access public
	 * @date 6/28/04
	 */
	function stop () {
		
	}
}

?>