<?php
/**
 * @package polyphony.library.repository.search
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PartAndValuesModule.class.php,v 1.4 2006/05/12 18:29:40 adamfranco Exp $
 */

/**
 * Search Modules generate forms for and collect/format the subitions of said forms
 * for various Digital Repository search types.
 *
 * @package polyphony.library.repository.search
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PartAndValuesModule.class.php,v 1.4 2006/05/12 18:29:40 adamfranco Exp $
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
	function PartAndValuesModule ( $partStructFieldName, $valueFieldName ) {
		$this->_partStructFieldName = $partStructFieldName;
		$this->_valueFieldName = $valueFieldName;
	}
	
	/**
	 * Create a form for searching.
	 * 
	 * @param string $action The destination on form submit.
	 * @return string
	 * @access public
	 * @since 04/25/06
	 */
	function createSearchForm (&$repository, $action ) {
		ob_start();
		
		print "<form action='$action' method='post'>\n<div>\n";
		
		$this->createSearchFields($repository);
		
		print "\t<input type='submit' />\n";
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
	function createSearchFields (&$repository) {
		ob_start();
		
		print "\n\t\t<select name='";
		print RequestContext::name($this->_partStructFieldName);
		print "' onchange='this.form.submit()'>";
		
		$idManager =& Services::getService("Id");
		if (RequestContext::value($this->_partStructFieldName)) {
			$idStrings = explode("_____", RequestContext::value($this->_partStructFieldName));
			$selectedRecordStructId =& $idManager->getId($idStrings[0]);
			$selectedPartStructId =& $idManager->getId($idStrings[1]);
		}
		
		$setManager =& Services::getService("Sets");
		$recordStructSet =& $setManager->getPersistentSet($repository->getId());
		$recordStructSet->reset();
		$recordStructHeadingPrinted = FALSE;
		while ($recordStructSet->hasNext()) {
			// Close the record structure group if needed
			if ($recordStructHeadingPrinted)
				print "\n\t\t\t</optgroup>";
			
			$recordStructHeadingPrinted = FALSE;
			$recordStruct =& $repository->getRecordStructure($recordStructSet->next());
			$recordStructId =& $recordStruct->getId();
			$partStructSet =& $setManager->getPersistentSet($recordStruct->getId());
			while ($partStructSet->hasNext()) {
				$partStruct =& $recordStruct->getPartStructure($partStructSet->next());
				
				// If the $partStruct has Authoritative values, add them to our menu
				$authoritativeValues =& $partStruct->getAuthoritativeValues();
				if ($authoritativeValues->hasNext()) {
					// Print a heading for the record structure if it isn't printed
					// yet.
					if (!$recordStructHeadingPrinted) {
						print "\n\t\t\t<optgroup label='";
						print $recordStruct->getDisplayName();
						print "'>";
						$recordStructHeadingPrinted = TRUE;
					}
					
					$partStructId =& $partStruct->getId();
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
							$selectedPartStruct =& $partStruct;
							print " selected='selected'";
						}
					} else if (!isset($selectedPartStruct)) {
						$selectedPartStruct =& $partStruct;
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
			print "\n\t\t<select name='";
			print RequestContext::name($this->_valueFieldName);
			print "'>";
			print "\n\t\t\t<option value=''>"._("Please select a value...")."</option>";
			
			$authoritativeValues =& $selectedPartStruct->getAuthoritativeValues();
			while ($authoritativeValues->hasNext()) {
				$value =& $authoritativeValues->next();
				print "\n\t\t\t<option";
				print " value='".urlencode($value->asString())."'";
				if (RequestContext::value($this->_valueFieldName) == urlencode($value->asString()))
					print " selected='selected'";
				print ">".$value->asString()."</option>";
			}
			
			print "\n\t\t\t<option value='__NonMatching__'";
			if (RequestContext::value($this->_valueFieldName) == '__NonMatching__')
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
	function getSearchCriteria ( &$repository ) {
		if (RequestContext::value($this->_partStructFieldName) 
			&& RequestContext::value($this->_valueFieldName)) 
		{
			$idManager =& Services::getService("Id");
			
			$idStrings = explode("_____", RequestContext::value($this->_partStructFieldName));
			$selectedRecordStructId =& $idManager->getId($idStrings[0]);
			$selectedPartStructId =& $idManager->getId($idStrings[1]);
			
			$recordStruct =& $repository->getRecordStructure($selectedRecordStructId);
			$partStruct =& $recordStruct->getPartStructure($selectedPartStructId);
			
			$value =& $partStruct->createValueObjectFromString(
				urldecode(RequestContext::value($this->_valueFieldName)));
			
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
		if (RequestContext::value($this->_partStructFieldName)
			&& RequestContext::value($this->_valueFieldName)) 
		{
			return array(
						RequestContext::name($this->_partStructFieldName) => 
						RequestContext::value($this->_partStructFieldName),
						RequestContext::name($this->_valueFieldName) => 
						RequestContext::value($this->_valueFieldName));
		} else
			return array();
	}
}

?>