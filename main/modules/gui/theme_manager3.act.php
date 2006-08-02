<?php
/**
 * @package polyphony.modules.gui
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: theme_manager3.act.php,v 1.1 2006/08/02 23:47:47 sporktim Exp $
 */ 


include_once(HARMONI."GUIManager/Themes/DobomodeTheme.class.php");

/**
 * 
 * 
 * @package polyphony.modules.gui
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: theme_manager3.act.php,v 1.1 2006/08/02 23:47:47 sporktim Exp $
 */
class theme_manager3Action 
	extends MainWindowAction
{
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		$authZManager =& Services::getService("AuthZ");
		$idManager =& Services::getService("IdManager");
		if ($authZManager->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.create_agent"),
				$idManager->getId("edu.middlebury.authorization.root")))
		{
			return TRUE;
		} else
			return FALSE;
	}
	
	/**
	 * Check Authorizations For Templating
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToTemplate () {
		$authZManager =& Services::getService("AuthZ");
		$idManager =& Services::getService("IdManager");
		if ($authZManager->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.create_agent"),
				$idManager->getId("edu.middlebury.authorization.root")))
		{
			return TRUE;
		} else
			return FALSE;
	}	
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to access the Theme Interface.");
	}	
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return _("Theme Management");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$harmoni =& Harmoni::instance();
		$guimanager =& Services::getService('GUIManager');
		$currentTheme =& $guimanager->getTheme();

		$actionRows =& $this->getActionRows();
		$cacheName = 'theme_interface_wizard43_';// @todo create unique cache name;
		
		$this->runWizard ( $cacheName, $actionRows );
	}	
	
	/**
	 * Create a new Wizard for this action. Caching of this Wizard is handled by
	 * {@link getWizard()} and does not need to be implemented here.
	 * 
	 * @return object Wizard
	 * @access public
	 * @since 4/28/05
	 */
	function &createWizard () {
		
	
		
		
		$harmoni =& Harmoni::instance();
		$guiManager =& Services::getService('GUIManager');		

		$currentTheme =& $guiManager->getTheme();
		
		// Instantiate the wizard, then add our steps.
		//$wizard =& new LogicStepWizard();
		// $wizard =& SimpleStepWizard::withDefaultLayout();
		$wizard =& LogicStepWizard::withDefaultLayout();
		
	/*********************************************************
	 * LOAD STEP
	 *********************************************************/

	 
	 
	 
	 $loadStep =& $wizard->addStep('load_step', new WizardStep());
		$loadStep->setDisplayName(_("This is the load step:"));

		ob_start();
	
	
		// select list for listing themes user can access
		$loadChoice =& $loadStep->addComponent('load_choice', 
												new WSelectList());
		$loadChoice->addOption('', '(choose a theme to copy or edit)');
		$loadChoice->setValue('');

		// get All themes that user can copy and populate list with them
		$theme_choices = $guiManager->getThemeListForUser();
		
		
		
		foreach ($theme_choices as $idString => $dName) {
			$loadChoice->addOption($idString, $dName);
		}

		
		
		// add buttons... copy to new, update, create from scratch.
		$copyButton =& $loadStep->addComponent('copy',
								WLogicButton::withLabel(_('Copy to New...')));
								
								
		
		$loadButton =& $loadStep->addComponent('load', 
								WLogicButton::withLabel(_('Load for Edit...')));
		$newButton =& $loadStep->addComponent('new',
								WLogicButton::withLabel(_('Create Empty...')));
								
		
			
								
		
		// create logic for buttons
		$newThemeRule =& WLogicRule::withSteps(array('dd_step', 'global_step'));
		
		$copyButton->setLogic($newThemeRule);
		$newButton->setLogic($newThemeRule);
		$loadButton->setLogic(WLogicRule::withSteps(array('home_step')));
		
		
		
		
		// add markup for step
		
		
	
		print " Number of theme choices = ".count($theme_choices);
		// @todo iframe preview of selected id (js onChange)
		print "<table>\n\t<tr>\n\t\t<td rowspan='3'>[[load_choice]]</td>";
		print "\n\t\t<td>[[copy]]</td>\n\t</tr>";
		print "\n\t<tr>\n\t\t<td>[[load]]</td>\n\t</tr>";
		print "\n\t<tr>\n\t\t<td>[[new]]</td>\n\t</tr>";

		
		
		$loadStep->setContent(ob_get_contents());
		ob_end_clean();
		
		return $wizard;
		
	}

	/**
	 * Save our results. Tearing down and unsetting the Wizard is handled by
	 * in {@link runWizard()} and does not need to be implemented here.
	 * 
	 * @param string $cacheName
	 * @return boolean TRUE if save was successful and tear-down/cleanup of the
	 *		Wizard should ensue.
	 * @access public
	 * @since 4/28/05
	 */
	function saveWizard ( $cacheName ) {
		$guiManager =& Services::getService("GUI");
		$wizard =& $this->getWizard($cacheName);

		printpre($wizard->allValues);
		exit;
	}
	
	/**
	 * This is the all important function that gives the Wizard control after 
	 * all of its components have been updated since the last pagelaod
	 * 
	 * @return void
	 * @access public
	 * @since 6/1/06
	 */
	function handleUpdate () {
		//<##>
	}
	
	/**
	 * Return the URL that this action should return to when completed.
	 * 
	 * @return string
	 * @access public
	 * @since 4/28/05
	 */
	function getReturnUrl () {
		$harmoni =& Harmoni::instance();
		$url =& $harmoni->request->mkURLWithPassthrough("user", "main");
		return $url->write();
	}


	
}