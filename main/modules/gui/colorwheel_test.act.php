<?php
/**
 * @package polyphony.modules.gui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: colorwheel_test.act.php,v 1.3 2006/01/18 15:42:55 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");


/**
 * 
 * 
 * @package polyphony.modules.gui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: colorwheel_test.act.php,v 1.3 2006/01/18 15:42:55 adamfranco Exp $
 */
class colorwheel_testAction 
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
					$idManager->getId("edu.middlebury.authorization.create_agents"),
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
		return _("ColorWheel Test");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		/*
		$harmoni =& Harmoni::instance();
		//$currenttheme =& $harmoni->getTheme();
		$guimanager =& Services::getService('GUIManager');
		$currenttheme =& $guimanager->getTheme();
		$array =& $currenttheme->getAllRegisteredSPs();
		*/
		$actionRows =& $this->getActionRows();
		$cacheName = 'colorwheel_test_wizard';
		
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
		
		
		// Instantiate the wizard, then add our steps.
		$wizard =& SimpleStepWizard::withDefaultLayout();
		
		$step =& $wizard->addStep("themesettings", new WizardStep());
		$step->setDisplayName(_("Test"));
		$actionlist =& $step->addComponent("colorwheel", new WColorWheel());
		$actionlist =& $step->addComponent("textfield", new WTextField());
		ob_start();
		print "[[colorwheel]]";
		$step->setContent(ob_get_contents());
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
		$wizard =& $this->getWizard($cacheName);
		$properties =& $wizard->getAllValues();
		printpre($properties);
		exit();	
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
		$url =& $harmoni->request->mkURLWithPassthrough("admin", "main");
		return $url->write();
	}
	
}

?>