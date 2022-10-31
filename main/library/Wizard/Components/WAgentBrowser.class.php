<?php
/**
 * @since Jul 19, 2005
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WAgentBrowser.class.php,v 1.8 2008/02/07 20:09:17 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/ResultPrinter/EmbeddedArrayResultPrinter.class.php");

/**
 * This component allows you to search the agents in the system and select a number of them for some action.
 * 
 * @since Jul 19, 2005
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WAgentBrowser.class.php,v 1.8 2008/02/07 20:09:17 adamfranco Exp $
 */
class WAgentBrowser
	extends WizardComponent 
{
	var $_agentsSelected = array();
	var $_searchResults = array();
	var $_resultPrinter = null;
	
	var $_searchField = null;
	var $_searchButton = null;
	var $_searchTypeSelector = null;
	var $_oneType = false;
	
	var $_options = array();
	
	var $_actionSelected = "nop";
	
	function __construct ()
	{
		$this->_searchField = new WTextField();
		$this->_searchField->setSize(20);
		$this->_searchButton = WEventButton::withLabel(dgettext("polyphony", "Go"));
		
		$agentManager = Services::getService("Agent");
		$searchTypes =$agentManager->getAgentSearchTypes();
		$this->_searchTypeSelector = new WSelectList();
		$count = 0;
		while($searchTypes->hasNext()) {
			$type =$searchTypes->next();
			$this->_searchTypeSelector->addOption(urlencode($type->asString()), $type->asString());
			$this->_searchTypeSelector->setValue(urlencode($type->asString()));
			$count++;
		}
		if ($count < 2) $this->_oneType = true;
		
		$this->_searchField->setParent($this);
		$this->_searchButton->setParent($this);
		$this->_searchTypeSelector->setParent($this);
	}
	
	/**
	 * Resets the search results and the selected agents.
	 *
	 * @return void
	 **/
	function reset()
	{
		$this->_agentsSelected = array();
		$this->_searchResults = array();
		
		$this->_resultPrinter = null;
	}
	
	/**
	 * Adds an action to the list of actions presented to the end user.
	 *
	 * @return void
	 **/
	function addActionOption($action_name, $label)
	{
		$this->_options[$action_name] = $label;
	}
	
	/**
	 * Tells the wizard component to update itself - this may include getting
	 * form post data or validation - whatever this particular component wants to
	 * do every pageload. 
	 * @param string $fieldName The field name to use when outputting form data or
	 * similar parameters/information.
	 * @access public
	 * @return boolean - TRUE if everything is OK
	 */
	function update ($fieldName) {
		
		$this->_searchField->update($fieldName."_query");
		$this->_searchButton->update($fieldName."_go");
		
		// check if we have any new checked agents to add to our list. 
		$values = RequestContext::value($fieldName."_checked");
		if (is_array($values)) {
			// remove the dummy '' value.
			$key = array_search('', $values);
			if ($key !== false) unset($values[$key]);
			$this->_agentsSelected = array_unique($values);
		}
		
		// check if the user selected an action
		$action = RequestContext::value($fieldName."_action");
		if ($action) $this->_actionSelected = $action;
		
		// perform the search, if necessary.
		if ($this->_searchButton->getAllValues()) {
			$query = $this->_searchField->getAllValues();
			$type = HarmoniType::fromString(urldecode($this->_searchTypeSelector->getAllValues()));
			
			$agentManager = Services::getService("Agent");
			
			$list =$agentManager->getAgentsBySearch($query, $type);
			
			$this->_searchResults = array(); // clear the array
			$this->_resultPrinter = null;
			
			while ($list->hasNext()) {
				$this->_searchResults[] =$list->next();
			}
			
			$this->_resultPrinter = new AgentBrowserResultPrinter($this->_searchResults, 1, 10, array("WAgentBrowser", "printAgent"), $fieldName);
			$this->_resultPrinter->overridePageNumber(1);
			$this->_resultPrinter->setOptions($this->_options);
		}
		
		$this->_resultPrinter->_selected = $this->_agentsSelected;
	}

	/**
	 * Returns the values of wizard-components. Should return an array if children are involved,
	 * otherwise a whatever type of object is expected.
	 * 
	 * Returns an array, 0=>action selected, 1=>array(of agents selected)
	 * @access public
	 * @return mixed
	 */
	function getAllValues () {
		$action = $this->_actionSelected;
		$this->_actionSelected = "nop";
		return array($action, $this->_agentsSelected);
	}

	/**
	 * Returns a block of XHTML-valid code that contains markup for this specific
	 * component. 
	 * @param string $fieldName The field name to use when outputting form data or
	 * similar parameters/information.
	 * @access public
	 * @return string
	 */
	function getMarkup ($fieldName) {
		// here we will do a couple things:
		// 1) display a search field in which people can enter information and search for users
		// 2) take the search results (maybe hundreds of them) and display them in some fashion
		// 3) display a list of the currently selected agents (cached)
		$srchFieldName = $fieldName . "_query";
		
		$td = textdomain("polyphony");
		$m = _("Search agents: ");
		$m .= $this->_searchField->getMarkup($srchFieldName);
		if (!$this->_oneType) $m .= $this->_searchTypeSelector->getMarkup($fieldName."_type");
		$m .= $this->_searchButton->getMarkup($fieldName."_go");
		$name = RequestContext::name($fieldName."_checked[]");
		$m .= "<input type='hidden' name='$name' value=''/>";
		$m .= "<br/>\n";
		
		if (count($this->_searchResults) || count($this->_agentsSelected)) {
			$m .= $this->_resultPrinter->getMarkup();
			$this->_resultPrinter->overridePageNumber(-1); // reset;
		}

		textdomain($td);
		
		return $m;
	}
	
	/**
	 * Callback function for the HTMLArrayResultPrinter.
	 *
	 * @return string
	 * @access public
	 * @static
	 **/
	static function printAgent($agent)
	{
		$m = '';
		$m .= $agent->getDisplayName();
		return $m;
	}
}

/**
 * A result printer for the {@link WAgentBrowser}.
 *
 * @package polyphony.wizard.components
 * @copyright Copyright &copy; 2005, Middlebury College
 * @author Gabriel Schine
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 * @version $Id: WAgentBrowser.class.php,v 1.8 2008/02/07 20:09:17 adamfranco Exp $
 */
class AgentBrowserResultPrinter
	extends EmbeddedArrayResultPrinter
{
	var $_colors;
	var $_currColor = 0;
	var $_fieldName;
	
	var $_selected = array();
	
	var $_options = array();
	
	function __construct(	$array, $numColumns, 
										$numResultsPerPage, $callbackFunction, $fieldName) {
		parent::__construct($array, $numColumns, $numResultsPerPage, $callbackFunction);
		$this->_colors = array("#aaa", "#ccc");
		$this->setTDStyle("background-color: ".$this->_colors[0]."; padding: 2px;");
		$this->_fieldName = $fieldName;
	}
	
	function setOptions($options) {
		$this->_options = $options;
	}
	
	function createTRElement() {
		$tr = parent::createTRElement();
		$this->_currColor = 1-$this->_currColor;
		$this->setTDStyle("background-color: ".$this->_colors[$this->_currColor]."; padding: 2px;");
		return $tr;
	}
	
	function createItemElement($content, $checked = false) {
		$name = RequestContext::name($this->_fieldName . "_checked");
		$currItemId =$this->_currentItem->getId();
		$id = urlencode($currItemId->getIdString());
		$chk = $checked?" checked":"";
		$content = "<input type='checkbox' name='".$name."[]' value='$id'$chk />\n" . $content;
		return parent::createItemElement($content);
	}
	
	function createHeaderRow() {
		$resultsText = dgettext("polyphony", "Search results");
		return $this->createTRElement().$this->createTDElement(
			"<input type='hidden' name='".RequestContext::name("starting_number")."' id='starting_number' value='".RequestContext::value("starting_number")."' /><b>$resultsText:</b>", $this->_numColumns) .
			"</tr>";
	}
	
	function createFooterRow() {
		$m = '';
		if (count($this->_selected)) {
			$agents = Services::getService("Agent");
			$ids = Services::getService("Id");
			// print out the existing agents.
			$m .= $this->createTRElement().$this->createTDElement("<b>".dgettext("polyphony", "Already selected").":</b>")."</tr>\n";
			foreach($this->_selected as $id) {
				$agent = $agents->getAgent($ids->getId($id));
				$this->_currentItem =$agent;
				$itemArray = array ($agent);
				$params = array_merge($itemArray, $this->_callbackParams);
				$itemMarkup = call_user_func_array(
					$this->_callbackFunction, $params);
				$m .= $this->createTRElement().$this->createItemElement($itemMarkup, true)."</tr>\n";
			}
		}
		$fieldID = $this->_fieldName."_action";
		$name = RequestContext::name($fieldID);
		$tdContent = "
					With selected: <select name='$name' id='$fieldID' onchange='if (this.value != \"nop\") submitWizard(this.form)'>\n";
					$tdContent .= "<option value='nop'>".dgettext("polyphony", "choose action...")."</option>\n";
					foreach ($this->_options as $name=>$label) {
						$tdContent .= "<option value='$name'>$label</option>\n";
					}
					$tdContent .= "</select>";
		
		$m .= $this->createTRElement() . $this->createTDElement($tdContent)."</tr>";
			
		return $m;
	}
	
	function createPageLinks ($numItems, $startingNumber)
	{
		$harmoni = Harmoni::instance();
		ob_start();
		$numPages = ceil($numItems/$this->_pageSize);
		$currentPage = floor($startingNumber/$this->_pageSize)+1; // add one for 1-based counting
		for ($i=1; $i<=$numPages; $i++) {
			if ($i > 0 && ($i+1) % 10 == 0)
				print "<br />";
			print " ";
			if ($i != $currentPage) {
				$url =$harmoni->request->mkURLWithPassthrough();
				$url->setValue("starting_number", (($i-1)*$this->_pageSize+1));
				print "<a href='#' onclick='getWizardElement(\"starting_number\").value = \"".(($i-1)*$this->_pageSize+1)."\"; submitWizard(getWizardElement(\"starting_number\").form);'>";
			}
			print $i;
			if ($i != $currentPage)
				print "</a>";
		}

		// Add the links to the page
		$pageLinks = ob_get_contents();
		ob_end_clean();

		return $pageLinks;
	}
	###
} // END class AgentBrowserResultPrinter


?>