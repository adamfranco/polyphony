<?php
/**
 * @package polyphony.repository.search
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PartAndValuesModule.class.php,v 1.7 2007/09/19 14:04:49 adamfranco Exp $
 */

/**
 * Search Modules generate forms for and collect/format the subitions of said forms
 * for various Digital Repository search types.
 *
 * @package polyphony.repository.search
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PartAndValuesModule.class.php,v 1.7 2007/09/19 14:04:49 adamfranco Exp $
 */

class PartAndValuesModule {
	
	/**
	 * Constructor
	 * 
	 * @param string $fieldName
	 * @return object
	 * @access public
	 * @since 04/25/06
	 */
	function __construct ( $partStructFieldName, $valueFieldName ) {
		$this->_partStructFieldName = $partStructFieldName;
		$this->_valueFieldName = $valueFieldName;
		$this->_initilaized = false;
	}
	
	/**
	 * Initialize this object
	 * 
	 * @return void
	 * @access public
	 * @since 5/15/06
	 */
	function init () {
		if (!$this->_initilaized) {
			$harmoni = Harmoni::instance();
			$harmoni->request->startNamespace('PartAndValuesModule');
			
			$this->_contextPartStructFieldName = RequestContext::name($this->_partStructFieldName);
			$this->_contextValueFieldName = RequestContext::name($this->_valueFieldName);
			
			if (RequestContext::value($this->_partStructFieldName)) {
				$this->_partStruct = RequestContext::value($this->_partStructFieldName);
			} else {
				$this->_partStruct = null;
			}
			
			if (RequestContext::value($this->_valueFieldName))
				$this->_value = RequestContext::value($this->_valueFieldName);
			else
				$this->_value = null;
	
			$harmoni->request->endNamespace();
			
			$this->_initilaized = true;
		}
	}
	
	/**
	 * Create a form for searching.
	 * 
	 * @param string $action The destination on form submit.
	 * @return string
	 * @access public
	 * @since 04/25/06
	 */
	function createSearchForm ($repository, $action ) {
		ob_start();
		
		print "<form action='$action' method='post'>\n<div>\n";
		
		$this->createSearchFields($repository);
		
		print "\t<input type='submit' value='".dgettext('polyphony', 'Search')."'/>\n";
		print "</div>\n</form>";
		
		return ob_get_clean();
	}
	
	/**
	 * Create the fields (without form tags) for searching
	 * 
	 * @param object Repository $repository
	 * @return string
	 * @access public
	 * @since 4/26/06
	 */
	function createSearchFields ($repository) {
		$this->init();
		ob_start();
		
		print "\n\t\t<select name='".$this->_contextPartStructFieldName."'";
		print " onchange='this.form.submit()'>";
		
		$idManager = Services::getService("Id");
		if ($this->_partStruct) {
			$idStrings = explode("_____", $this->_partStruct);
			$selectedRecordStructId =$idManager->getId($idStrings[0]);
			$selectedPartStructId =$idManager->getId($idStrings[1]);
		}
		
		$setManager = Services::getService("Sets");
		$recordStructSet =$setManager->getPersistentSet($repository->getId());
		$recordStructSet->reset();
		$recordStructHeadingPrinted = FALSE;
		while ($recordStructSet->hasNext()) {
			// Close the record structure group if needed
			if ($recordStructHeadingPrinted)
				print "\n\t\t\t</optgroup>";
			
			$recordStructHeadingPrinted = FALSE;
			$recordStruct =$repository->getRecordStructure($recordStructSet->next());
			$recordStructId =$recordStruct->getId();
			$partStructSet =$setManager->getPersistentSet($recordStruct->getId());
			while ($partStructSet->hasNext()) {
				$partStruct =$recordStruct->getPartStructure($partStructSet->next());
				
				// If the $partStruct has Authoritative values, add them to our menu
				$authoritativeValues =$partStruct->getAuthoritativeValues();
				if ($authoritativeValues->hasNext()) {
					// Print a heading for the record structure if it isn't printed
					// yet.
					if (!$recordStructHeadingPrinted) {
						print "\n\t\t\t<optgroup label='";
						print $recordStruct->getDisplayName();
						print "'>";
						$recordStructHeadingPrinted = TRUE;
					}
					
					$partStructId =$partStruct->getId();
					print "\n\t\t\t\t<option ";
					print " value='";
						print $recordStructId->getIdString();
						print "_____";
						print $partStructId->getIdString();
					print "'";
					
					// Make sure that we keep either the selected part Structure
					// or the first one around for populating the Authority List
					if (isset($selectedPartStructId)) {
						if ($selectedPartStructId->isEqual($partStructId)) {
							$selectedPartStruct =$partStruct;
							print " selected='selected'";
						}
					} else if (!isset($selectedPartStruct)) {
						$selectedPartStruct =$partStruct;
						print " selected='selected'";
					}
					
					print ">";
					print $partStruct->getDisplayName();
					print "</option>";					
				}
			}
		}
		
		if (!isset($selectedPartStruct)) {
			print "\n\t\t\t<option value=''>"._("No authoritative values are defined.")."</option>";
		}
		print "\n\t\t</select>";
		
		if (isset($selectedPartStruct)) {
			print "\n\t\t<select name='".$this->_contextValueFieldName."'>";
			print "\n\t\t\t<option value=''>"._("Please select a value...")."</option>";
			
			$authoritativeValues =$selectedPartStruct->getAuthoritativeValues();
			while ($authoritativeValues->hasNext()) {
				$value =$authoritativeValues->next();
				print "\n\t\t\t<option";
				print " value='".urlencode($value->asString())."'";
				if ($this->_value == urlencode($value->asString()))
					print " selected='selected'";
				print ">".$value->asString()."</option>";
			}
			
			print "\n\t\t\t<option value='__NonMatching__'";
			if ($this->_value == '__NonMatching__')
				print " selected='selected'";
			print ">"._("* Non-Matching *")."</option>";
			
			print "\n\t\t</select>";
		}
		
		return ob_get_clean();
	}
	
	/**
	 * Get the formatted search terms based on the submissions of the form
	 * 
	 * @param object Repository $repository
	 * @return mixed
	 * @access public
	 * @since 04/25/06
	 */
	function getSearchCriteria ( $repository ) {
		$this->init();
		
		if ($this->_partStruct 
			&& $this->_value) 
		{
			$idManager = Services::getService("Id");
			
			$idStrings = explode("_____", $this->_partStruct);
			$selectedRecordStructId =$idManager->getId($idStrings[0]);
			$selectedPartStructId =$idManager->getId($idStrings[1]);
			
			$recordStruct =$repository->getRecordStructure($selectedRecordStructId);
			$partStruct =$recordStruct->getPartStructure($selectedPartStructId);
			
			$value =$partStruct->createValueObjectFromString(
				urldecode($this->_value));
			
			return array (
				'RecordStructureId' => $selectedRecordStructId,
				'PartStructureId' => $selectedPartStructId,
				'AuthoritativeValue' => $value);
		} else {
			return false;
		}
	}
	
	/**
	 * Get an array of the current values to be added to a url. The keys of the
	 * arrays are the field-names in the appropriate context.
	 * 
	 * @return array
	 * @access public
	 * @since 04/25/06
	 */
	function getCurrentValues () {
		$this->init();
		
		if ($this->_partStruct) {
			$values = array(
						$this->_contextPartStructFieldName => $this->_partStruct,
						$this->_contextValueFieldName => $this->_value);
		} else
			$values = array();
		
		return $values;
	}
	
	/**
	 * Update the current values with data (maybe stored in the session for instance. 
	 * The keys of the arrays are the field-names in the appropriate context.
	 * This could have been originally fetched via getCurrentValues
	 * 
	 * @param array $values
	 * @return void
	 * @access public
	 * @since 04/25/06
	 */
	function setCurrentValues ($values) {
		$this->init();
		
		if (isset($values[$this->_contextPartStructFieldName])) {
			$this->_partStruct = $values[$this->_contextPartStructFieldName];
			$this->_value = $values[$this->_contextValueFieldName];
		}
	}
}

?>