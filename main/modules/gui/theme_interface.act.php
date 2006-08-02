<?php
/**
 * @package polyphony.modules.gui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: theme_interface.act.php,v 1.8 2006/08/02 23:47:47 sporktim Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(POLYPHONY."/main/library/Wizard/Components/WizardStep.class.php");
require_once(POLYPHONY."/main/library/Wizard/Components/WizardDynamicStep.class.php");
require_once(POLYPHONY."/main/library/Wizard/Components/WColorWheel.class.php");

/**
 * 
 * 
 * @package polyphony.modules.gui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: theme_interface.act.php,v 1.8 2006/08/02 23:47:47 sporktim Exp $
 */
class theme_interfaceAction 
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
		print "buildContent";
		
		$harmoni =& Harmoni::instance();
		$guimanager =& Services::getService('GUIManager');
		$currentTheme =& $guimanager->getTheme();

		$actionRows =& $this->getActionRows();
		$cacheName = 'theme_interface_wizard_';//.$currentTheme->getId();
		
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

		
		print "createWizard";

		$guimanager =& Services::getService('GUIManager');		

		$currentTheme =& $guimanager->getTheme();

		
		// Instantiate the wizard, then add our steps.
		$wizard =& SimpleStepWizard::withDefaultLayout();
		
		/*********************************************************
		 * Step 1 Choose Management Mode
		 *********************************************************/

			$step =& $wizard->addStep("choosemode", new WizardStep());
			$step->setDisplayName(_("Choose Theme Source"));
			theme_interfaceAction::populateChoose($step);

		
		/*********************************************************
		 * Step 2 Beginning Steps
		 *********************************************************/

			$step2 =& $wizard->addStep('begin', new WizardDynamicStep());
			$step2->setDisplayName(_("Begin Theme Modification"));
			$step2->setShouldModifyStepFunction("theme_interfaceAction::smsBegin");
			$step2->setModifyStepFunction("theme_interfaceAction::msBegin");
		
		/*********************************************************
		 * Step 3 Basic Theme Customization
		 *********************************************************/

			$step3 =& $wizard->addStep('basic', new WizardDynamicStep());
			$step3->setDisplayName(_("Basic Theme Customization"));
			$step3->setShouldModifyStepFunction("theme_interfaceAction::smsBasic");
			$step3->setModifyStepFunction("theme_interfaceAction::msBasic");

		/*********************************************************
		 * Step 4 Full Theme Customization
		 *********************************************************/

			$step4 =& $wizard->addStep('advanced', new WizardDynamicStep());
			$step4->setDisplayName(_("Advanced Theme Customization"));
			$step4->setShouldModifyStepFunction("theme_interfaceAction::smsAdvanced");
			$step4->setModifyStepFunction("theme_interfaceAction::msAdvanced");

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

		$new = ($wizard->allValues['choosemode']['selectaction'] == 'new')?true:false;
		$basic = ($wizard->allValues['begin']['customization'] == 'basic')?true:false;

		/*********************************************************
		 * Get the theme object
		 *********************************************************/
		 if ($new)
			$theme =& $guiManager->createTheme($wizard->allValues['begin']['displayname'],
								$wizard->allValues['begin']['description']);
		else
			$theme =& $guiManager->getTheme();
		
		/*********************************************************
		 * Get the style collection sources
		 *********************************************************/
		if ($basic) {
			$styles_selector = $wizard->allValues['basic']['styles-selector'];
			$styles_id = $wizard->allValues['basic']['styles-id'];
		} else {
			$styles_selector = $wizard->allValues['advanced']['styles-selector'];
			$styles_id = $wizard->allValues['advanced']['styles-id'];
		}

		/*********************************************************
		 * Handle Style Collections keyed by Id
		 *********************************************************/
		if (!$new && (count($styles_id) > 0)) {
			$styles =& $theme->getStyleCollections();
			foreach ($styles as $style) {
				$sid =& $style->getId();
				// make style collection objects same as wizard reps.
				if (isset($styles_id[$sid->getIdString()]))
					theme_interfaceAction::matchValues($style, $styles_id[$sid->getIdString()]);
				else
					$theme->removeStyleCollection($style);
			}
		}
		
		/*********************************************************
		 * Handle Style Collections keyed by class selector
		 *********************************************************/
		foreach ($styles_selector as $style) {
			$newStyle =& theme_interfaceAction::addStyleToTheme($style, $theme);
		}
		
		$guiManager->setTheme($theme);
		$guiManager->saveTheme();
		
		return true;		
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
	
	/**
	 * This function allows a wizard step to determine whether or not it should
	 * modify itself to reflect any new data from other steps.
	 * (shouldmodifystep).
	 * 
	 * @param object WizardStep
	 * @return void
	 * @access public
	 * @since 4/19/06
	 */
	function smsBegin (&$step) {
		// get Previous steps
		$wizard =& $step->getWizard();
		/* Modify this step When:
			* this step is active AND has not been created
			* the inputs affecting this step have been changed
		*/

		if (isset($wizard->allValues['choosemode']['selectaction']) && (!isset($step->_info) || $step->_info != $wizard->allValues['choosemode'])) {
			$step->_info = $wizard->allValues['choosemode'];
			return true;
		} else return false;
	}
	
	/**
	 * This function allows a wizard step to modify itself to reflect any new
	 * data from other steps. (modifystep).
	 * 
	 * @param object WizardStep
	 * @return void
	 * @access public
	 * @since 4/19/06
	 */
	function msBegin (&$step) {
		$wizard =& $step->getWizard();
		
		$mode = $wizard->allValues['choosemode']['selectaction'];
		
		/*********************************************************
		 * Create the Chunks for this step
		 *********************************************************/

			// create the displayName and Description inputs
			if (!$step->hasComponent('displayname')) {
				theme_interfaceAction::createDnameDesc($step);
			}
			// create the template choices from the Database
			if (!$step->hasComponent('templates')) {
				theme_interfaceAction::createTempCust($step);
			}
				
		/*********************************************************
		 * Dynamic Modification of Components
		 *********************************************************/

			$dname =& $step->getChild('displayname');
			$desc =& $step->getChild('description');
			$cust =& $step->getChild('customization');
			$temps =& $step->getChild('templates');
			if ($mode == 'new') {
				// creating a new theme, display/desc should be blank
				$dname->setStartingDisplayText("Display Name");
				$desc->setStartingDisplayText("Description");
				if (!$temps->isEnabled())
					$temps->setEnabled(true);
			} else if ($mode == 'update') {
				$guimanager =& Services::getService('GUIManager');
				$currentTheme =& $guimanager->getTheme();		
				// modifying a theme dname and desc should be populated
				$dname->setValue($currentTheme->getDisplayName());
				$desc->setValue($currentTheme->getDescription());
				if ($temps->isEnabled())
					$temps->setEnabled(false);
			}
			
		ob_start();
		print $step->getMarkupForComponent('dNameAndDesc');
		print "<hr/>";
		print "<table>";
		print "<tr><td>".$step->getMarkupForComponent('templates')."</td>";
		print "<td>".$step->getMarkupForComponent('customization')."</td></tr></table>";
		$step->setContent(ob_get_clean());
	}
	
	/**
	 * This function allows a wizard step to determine whether or not it should
	 * modify itself to reflect any new data from other steps.
	 * (shouldmodifystep).
	 * 
	 * @param object WizardStep
	 * @return void
	 * @access public
	 * @since 4/19/06 
	 */
	function smsBasic (&$step) {
		// get Previous steps
		$wizard =& $step->getWizard();
		// if the begin step has input and it's different from before
		if (isset($wizard->allValues['begin']['customization']) &&
			(!isset($step->_info) ||
			 $step->_info['choosemode'] != $wizard->allValues['choosemode'] ||
			 $step->_info['begin'] != $wizard->allValues['begin'])) {
			$step->_info = $wizard->allValues;
			return true;
		} else return false;

		// @todo possibly assign step a class variable for what we should do?
	}
	
	/**
	 * This function allows a wizard step to modify itself to reflect any new
	 * data from other steps. (modifystep).
	 * 
	 * @param object WizardStep
	 * @return void
	 * @access public
	 * @since 4/19/06
	 */
	function msBasic (&$step) {
		$wizard =& $step->getWizard();
		
		// the buffer for the step's markup
		ob_start();
		
		// create a few booleans for all decision making here!
		$new = ($wizard->allValues['choosemode']['selectaction'] == 'new')?true:false;
		$basic = ($wizard->allValues['begin']['customization'] == 'basic')?true:false;	

		/*********************************************************
		 * GLOBAL COMPONENTS create modify and display
		 *********************************************************/
		// always use this global component
		if (!$step->hasComponent('global-bgcolor'))
			theme_interfaceAction::createBasicGlobalStyles($step, $new);
		
		// make sure the global components are correctly populated
		theme_interfaceAction::changeGlobals($step);
		// add them to the step's markup
		print $step->getMarkupForComponent('global-style');
		
		/*********************************************************
		 * BASIC CUSTOMIZATION COMPONENTS create modify and display
		 *********************************************************/
		// customization is done in this step, not advanced step; either templates or updates
		if ($basic) {
			
			// create color wheel component
			if (!$step->hasComponent('colorwheel'))
				theme_interfaceAction::createColorWheel($step);
			
			print $step->getMarkupForComponent('colorwheel');
			
			print "Styles for Editing:<br/>";
			// create basic customization parts for either updating or creating
			if ($new) {
				if (!$step->hasComponent('styles-selector')) {
					$ss =& $step->addComponent('styles-selector', new WizardStep());
					theme_interfaceAction::createBasicCustomizationTemplate($ss);
				}
				print "[[styles-selector]]";
			} else {
				if (!$step->hasComponent('styles-id')) {
					$si =& $step->addComponent('styles-id', new WizardStep());
					theme_interfaceAction::createBasicCustomizationUpdate($si);
				}
				print "[[styles-id]]";
			}
		}

		$step->setContent(ob_get_clean());
	}
	
	/**
	 * This function allows a wizard step to determine whether or not it should
	 * modify itself to reflect any new data from other steps.
	 * (shouldmodifystep).
	 * 
	 * @param object WizardStep
	 * @return void
	 * @access public
	 * @since 4/19/06
	 */
	function smsAdvanced (&$step) {
		// can the user create templates?
		// is the advanced step required?
		// has any data changed?
		$wizard =& $step->getWizard();

		if (isset($wizard->allValues['begin']['customization']) &&
		((theme_interfaceAction::isAuthorizedToTemplate() && !$step->hasComponent('save-template')) ||
		($step->_info != $wizard->allValues)))
			return true;
		else return false;
	}
	
	/**
	 * This function allows a wizard step to modify itself to reflect any new
	 * data from other steps. (modifystep).
	 * 
	 * @param object WizardStep
	 * @return void
	 * @access public
	 * @since 4/19/06
	 */
	function msAdvanced (&$step) {
		$wizard =& $step->getWizard();
		$guimanager =& Services::getService("GUI");
		$theme =& $guimanager->getTheme();
		
		$step->_info = $wizard->allValues;

		$update = ($wizard->allValues['choosemode']['selectaction'] == 'update')?true:false;
		$advanced = ($wizard->allValues['begin']['customization'] == 'advanced')?true:false;


		theme_interfaceAction::handleAddRemoveButtons($step);
			
		if ($advanced) {
			// buffer for step content
			ob_start();
			$wizard->allStyles = array();
			
			
			// colorwheel for color options... always printed?
			if (!$step->hasComponent('colorwheel'))
				theme_interfaceAction::createColorWheel($step);
	
			print $step->getMarkupForComponent('colorwheel');

			// @todo global styles here?
			
			if ($update) {
				if (!$step->hasComponent('styles-id')) {
					$si =& $step->addComponent('styles-id', new WizardStep());
					theme_interfaceAction::createAdvancedCustomizationUpdate($si);
				}
				print "[[styles-id]]";
			}
			
			/*********************************************************
			 * handle the setting up of the adding of style collections
			 * (which should handle SP adding
			 *********************************************************/
			// advanced step always allows for new styles
			if (!$step->hasComponent('collection-choices')) {
				theme_interfaceAction::createAdvancedInterface($step);
			} else {
				theme_interfaceAction::updateAdvancedInterface($step);
			}
		
			print $step->getMarkupForComponent('advanced-interface');

		}

		// this needs to be a special case
		if (!$step->hasComponent('save-template')) {
			$step->addComponent('save-template', WCheckBox::withLabel('Save As Template?'));
			ob_start();
			print "[[save-template]]";
			$step->setMarkupForComponent(ob_get_clean(), 'save-template');
		}

		if (theme_interfaceAction::isAuthorizedToTemplate())
			print $step->getMarkupForComponent('save-template');

		$step->setContent(ob_get_clean());
	}

// 				ob_start();
// 				print "<table border=3>";
// 				$globalStyles =& $theme->getGlobalStyles();
// 				foreach ($globalStyles as $globalStyle) {
// 					$gid =& $globalStyle->getId();
// 					$step->addComponent($gid->getIdString(),
// 						$globalStyle->getWizardRepresentation());
// 					print "<tr><td>".$globalStyle->getDisplayName()."</td>";
// 					print "<td>[[".$gid->getIdString()."]]</td></tr>";
// 				}
// 				print "</table>";
// 				$step->setMarkupForComponent(ob_get_clean(), 'global-style');

	/**
	 * populates the first step with the appropriate wizard components and markup
	 * 
	 * @param ref object WizardStep
	 * @return void
	 * @access static
	 * @since 5/15/06
	 */
	function populateChoose (&$step) {
		$guiManager =& Services::getService('GUI');
		$currentTheme =& $guiManager->getTheme();

		// component
		$actionlist =& $step->addComponent("selectaction", new WSelectList());
		$actionlist->addOption("update", _("Update Current Theme"));
		$actionlist->addOption("new", _("Create New Theme"));
		$actionlist->addOption("load", _("Load theme by Id"));
		$actionlist->setValue('new');
//		$actionlist->addOption("search", _("Search for theme"));

		// markup
		ob_start();
		print "<h2>Current Theme: <em>".$currentTheme->getDisplayName().
			"</em></h2>";
		
		print "<p>Please choose whether you want to create a new Theme, update the current Theme, ".
			" or load a Theme.</p>";
		print"\n<br />[[selectaction]]";
		
		$step->setContent(ob_get_clean());
	}
	
	/**
	 * generates the dname and description stuff
	 * 
	 * @param ref object WizardStep $step
	 * @return void
	 * @access public
	 * @since 5/11/06
	 */
	function createDnameDesc (&$step) {
		$step->addComponent('displayname', new WTextField());
		$step->addComponent('description', new WTextArea());
		ob_start();
		print "<table>";
		print "<tr><td>"._('Display Name:')."</td><td>[[displayname]]</td></tr>";
		print "<tr><td>"._('Description:')."</td><td>[[description]]</td></tr>";
		print "</table>";
		// save these two together as a markup component for the step
		$step->setMarkupForComponent(ob_get_clean(),'dNameAndDesc');
	}

	/**
	 * generates the templates and customization components
	 * 
	 * @param ref object WizardStep $step
	 * @return void
	 * @access public
	 * @since 5/11/06
	 */
	function createTempCust (&$step) {
		$idManager =& Services::getService("Id");
		$wizard =& $step->getWizard();
		$guiManager =& Services::getService("GUI");

		$themeTemplates =& $guiManager->getThemeTemplates();
		$templates =& $step->addComponent('templates', new WRadioList());
		$templates->addOption('none', _("Start from scratch (no template)"));
		foreach ($themeTemplates as $template) {
			$templateId =& $template->getId();
			$templates->addOption($templateId->getIdString(), $template->getDescription());
		}
// @todo if value is none, disable basic customization, otherwise make sure it is enabled
		$templates->setOnChange(null);

		$customization =& $step->addComponent('customization',	new WRadioList());
		$customization->addOption('basic', _('Choose a few options (suggested)'));
		$customization->addOption('advanced', _('Fully Customizable Theme (Complex)'));
// @todo default to basic
		$customization->setValue('advanced');
		$customization->setOnChange(null);

		ob_start();
		print _("Choose a Template to work with, ");
		print _("Choosing no template means you must fully customize");
		print "<table>";
		print "<tr><td>"._("Templates:")."</td></tr>";
		print "<tr><td>[[templates]]</td></tr>";
		print "</table>";
		$step->setMarkupForComponent(ob_get_clean(), 'templates');
		ob_start();
		print _("Choose either basic (customize existing style parts), or advanced");
		print _("(complex customization adding/removing style parts).");
		print "<table>";
		print "<tr><td>"._("Degree of Customization:")."</td></tr>";
		print "<tr><td>[[customization]]</td></tr>";
		print "</table>";
		$step->setMarkupForComponent(ob_get_clean(), 'customization');
	}
	
	/**
	 * generates basic global styles for the theme
	 * 
	 * @param ref object WizardStep $step
	 * @param boolean $new are we creating a new theme
	 * @return void
	 * @access public
	 * @since 5/11/06
	 */
	function createBasicGlobalStyles (&$step, $new) {
		$namedColors = array (_("aqua"), _("black"), _('blue'), _('fuchsia'), _('gray'), _('green'),
		_('lime'), _('maroon'), _('navy'), _('olive'), _('purple'), _('red'), _('silver'), _('teal'), _('white'), _('yellow'));

		// background color
		$bgcolor =& $step->addComponent('global-bgcolor', new WSelectList());
		foreach ($namedColors as $color) {
			if ($color == 'black' || $color == 'navy' || $color == 'blue')
				$bgcolor->addOption($color, $color, "background-color:$color; color:white;");
			else
				$bgcolor->addOption($color, $color, "background-color:$color;");
		}
//		if ($new) {
			$bgcolor->setValue('white');
			$sp =& new FontSP();
//		} else {
			// @todo find background color, add as choice
			// @todo find font, set as $sp
//		}
		// font
		$step->addComponent('global-font', $sp->getWizardRepresentation());
		ob_start();
		print "Global Styles:<br/>";
		print "Here you can choose a few simple global attributes for your theme.<br/>";
		print "<table border=2><tr><td>";
		print "Choose a Background Color for your Theme:</td>";
		print "<td>[[global-bgcolor]]</td></tr>";
		print "<tr><td>Choose attributes for your global font style:</td>";
		print "<td>[[global-font]]</td></tr></table>";
		// this markup should never change
		$step->setMarkupForComponent(ob_get_clean(), 'global-style');
	}

	/**
	 * if the global settings need to be adjusted they will be (do once?)
	 * 
	 * @param ref object WizardStep $step
	 * @access public
	 * @since 5/11/06
	 */
	function changeGlobals (&$step) {
		$idManager =& Services::getService("Id");
		$wizard =& $step->getWizard();
		$guiManager =& Services::getService("GUI");

		// using a template
		if (isset($wizard->allValues['begin']['templates'])  && ($wizard->allValues['begin']['templates']!= 'none'))
			$themeObj =& $guiManager->getThemeById(
				$idManager->getId($wizard->allValues['begin']['templates']));
		// updating a theme
		else if ($wizard->allValues['choosemode']['selectaction'] == 'update')
			$themeObj =& $guiManager->getTheme();
		else
			return;
		// have theme obj, if color isn't right fix it
		$globalStyles =& $themeObj->getGlobalStyles();
		$SPs =& $globalStyles['body']->getSPs();
		$SCs =& $SPs['background-color']->getSCs();
		$comp =& $step->getChild('global-bgcolor');
		$val = $SCs['colorsc']->getValue();
		
		// check the value
		if ($comp->getAllValues() != $val) {
			// check for the option
			if (!$comp->isOption($val))
				$comp->addOption($val, $val, 'background-color:$val;');
			$comp->setValue($val);
		}
	}
	
	/**
	 * creates the colorwheel and loadcolors button for the step
	 * 
	 * @param ref object WizardStep
	 * @return void
	 * @access public
	 * @since 5/15/06
	 */
	function createColorWheel (&$step) {
		$step->addComponent('colorwheel', new WColorWheel());
		$lc =& $step->addComponent('loadcolors', WEventButton::withLabel('Load Colors'));

		ob_start();
		print '[[colorwheel]]<br/>';
		print '[[loadcolors]]<br/>';
		$step->setMarkupForComponent(ob_get_clean(), 'colorwheel');	
	}
	
	/**
	 * generates basic editing for template
	 * 
	 * @param ref object WizardStep $step
	 * @return void
	 * @access public
	 * @since 5/11/06
	 */
	function createBasicCustomizationTemplate (&$step) {
		$idManager =& Services::getService("Id");
		$wizard =& $step->getWizard();
		$guiManager =& Services::getService("GUI");
	
		// get template or theme for updating	
		$themeObj =& $guiManager->getThemeById(
			$idManager->getId($wizard->allValues['begin']['templates']));

		ob_start();
		print "Template Styles:<br/>";
		// print each created style
		foreach ($themeObj->getStyleCollections() as $style) {
			$step->addComponent($style->getClassSelector(),
								$style->getWizardRepresentation());
// 			$step->addComponent('remove-'.$style->getClassSelector(),
// 				new WEventButton());
			print "<hr/>";
			print "<table border=1><tr><td>".$style->getDisplayName()."</td>";
			print "<td>[[".$style->getClassSelector()."]]</td>";
			print "<td>".$style->getDescription()."</td>";
			print "</tr></table>";
		}
		// this markup should never change
		$step->setContent(ob_get_clean());
	}

	/**
	 * generates basic editing for updates
	 * 
	 * @param ref object WizardStep $step
	 * @return void
	 * @access public
	 * @since 5/11/06
	 */
	function createBasicCustomizationUpdate (&$step) {
		$idManager =& Services::getService("Id");
		$wizard =& $step->getWizard();
		$guiManager =& Services::getService("GUI");

		// get theme for updating		
		$themeObj =& $guiManager->getTheme();

		ob_start();
		print "Theme Styles:<br/>";
		// print each created style
		foreach ($themeObj->getStyleCollections() as $style) {
			$sid =& $style->getId();
			$step->addComponent($sid->getIdString(),
								$style->getWizardRepresentation());
// 			$step->addComponent('remove-'.$style->getClassSelector(),
// 				new WEventButton());
			print "<hr/>";
			print "<table border=1><tr><td>".$style->getDisplayName()."</td>";
			print "<td>[[".$sid->getIdString()."]]</td>";
			print "<td>".$style->getDescription()."</td>";
			print "</tr></table>";
		}
		// this markup should never change
		$step->setContent(ob_get_clean());
	}

	/**
	 * generates advanced editing for updates
	 * 
	 * @param ref object WizardStep $step
	 * @return void
	 * @access public
	 * @since 5/15/06
	 */
	function createAdvancedCustomizationUpdate (&$step) {
		$idManager =& Services::getService("Id");
		$wizard =& $step->getWizard();
		$guiManager =& Services::getService("GUI");

		// get theme for updating		
		$themeObj =& $guiManager->getTheme();
		
		// buffer for group of updatable styles
		ob_start();
				
		// print out existing, id-keyed styles
		foreach ($theme->getStyleCollections() as $style) {
			$wizard->allStyles[$style->getSelector()] = true;
			$sid =& $style->getId();
			$step->addComponent($sid->getIdString(),
								$style->getWizardRepresentation(true));
			$step->addComponent('remove-'.$sid->getIdString(), WEventButton::withLabel('-'));
			// buffer for individual style
			ob_start();
			print "<hr/>";
			print "<table border=1><tr><td>".$style->getDisplayName()."</td>";
			print "<td>[[".$sid->getIdString()."]]</td>";
			print "<td>".$style->getDescription()."</td>";
			print "<td>[[remove-".$sid->getIdString()."]]</td></tr></table>";
			$step->addMarkupForComponent(ob_get_clean(), $sid->getIdString());
			print $step->getMarkupForComponent($sid->getIdString());
		}
		$step->setContent(ob_get_clean());
	}

	/**
	 * generates an interface for adding new style collections to the theme
	 * 
	 * @param ref object WizardStep
	 * @return void
	 * @access public
	 * @since 5/15/06
	 */
	function createAdvancedInterface (&$step) {
		$guiManager =& Services::getService('GUI');
		$wizard =& $step->getWizard();

		if (!$step->hasComponent('styles-selector')) {
			$ss =& $step->addComponent('styles-selector', new WizardStep());
			theme_interfaceAction::createAdvancedCustomizationAlways($ss);
		}
		
		ob_start();
		print "<table>";
		print "<tr><td colspan=2>[[styles-selector]]</td></tr>";

		if (!$step->hasComponent('add-collection')) {
			$addButton =& $step->addComponent('add-collection', new WEventButton());
			$addButton->setLabel("Add Style For Component");
			$addButton->setParent($step);
		}

		$addStyle =& $step->addComponent('collection-choices', new WSelectList());
		$addStyle->addOption('', '(choose a component to add a new style for it)');
		// drop down menu for adding a style collection
		
		foreach ($guiManager->getSupportedComponents() as $component) {
			if (strtoupper($component) != 'BLANK')
			for ($i = 1; $i < 5; $i++) {
				$addStyle->addOption($component.$i, $component." ".$i);
				
				// disable already existing styles
				if (isset($wizard->allStyles[$component.$i]) && $wizard->allStyles[$component.$i])
					$addStyle->disableOption($component.$i);
			}
		}
		
		print "<tr><td>[[collection-choices]]</td><td>[[add-collection]]</td></tr></table>";

		$step->setMarkupForComponent(ob_get_clean(), 'advanced-interface');
	}
	
	/**
	 * generates basic editing for templates and new styles
	 * 
	 * @param ref object WizardStep $step
	 * @return void
	 * @access public
	 * @since 5/11/06
	 */
	function createAdvancedCustomizationAlways (&$step) {
		$idManager =& Services::getService("Id");
		$wizard =& $step->getWizard();
		$guiManager =& Services::getService("GUI");

		// get template 	
		if (isset($wizard->allValues['begin']['templates']) && ($wizard->allValues['begin']['templates'] != 'none')) {
			ob_start();
			$themeObj =& $guiManager->getThemeById(
				$idManager->getId($wizard->allValues['begin']['templates']));
	
			// print each templated style
			foreach ($themeObj->getStyleCollections() as $style) {
				ob_start();
				$wizard->allStyles[$style->getSelector()] = true;
				$step->addComponent($style->getClassSelector(),
									$style->getWizardRepresentation());
				$step->addComponent('remove-'.$style->getClassSelector(),
									WEventButton::withLabel('-'));
				print "<hr/>";
				print "<table border=1><tr><td>".$style->getDisplayName()."</td>";
				print "<td>[[".$style->getClassSelector()."]]</td>";
				print "<td>".$style->getDescription()."</td>";
				print "<td>[[remove-".$style->getClassSelector()."]]</td>";
				print "</tr></table>";
				$step->setMarkupForComponent(ob_get_clean(), $style->getClassSelector());
			}
		}

		ob_start();
		foreach ($step->getMarkups() as $markup) {
			print $markup;
		}
		
		// this markup should never change
		$step->setContent(ob_get_clean());
	}
	
	/**
	 * creates a new style in the wizard
	 * 
	 * @param ref object WizardStep $step
	 * @param string $type the type of the new style
	 * @return void
	 * @access public
	 * @since 5/18/06
	 */
	function createStyle (&$step, $type) {
		// create necessary inputs from $type
		$index = substr($type, -1);
		$c[] = "*.".strtolower($type);
		$c[] = strtolower($type);
		$c[] = trim($type, $index)." ".$index;
		$c[] = "A ".trim($type, $index)." $index style collection.";

// @todo lookup standard descriptions from somewhere		
		$style =& new StyleCollection($c[0], $c[1], $c[2], $c[3]);

		ob_start();
		
		$step->addComponent($c[1], $style->getWizardRepresentation(true));
		$step->addComponent('remove-'.$style->getClassSelector(),
							WEventButton::withLabel('-'));
		print "<hr/>";
		print "<table border=1><tr><td>".$style->getDisplayName()."</td>";
		print "<td>[[".$style->getClassSelector()."]]</td>";
		print "<td>".$style->getDescription()."</td>";
		print "<td>[[remove-".$style->getClassSelector()."]]</td>";
		print "</tr></table>";
		$step->setMarkupForComponent(ob_get_clean(), $style->getClassSelector());
		
		$step->setContent($step->getContent().$step->getMarkupForComponent($style->getClassSelector()));
	}
	
	/**
	 * finds any requests for new SPS and adds them
	 * 
	 * @param ref object WizardStep
	 * @return void
	 * @access public
	 * @since 5/17/06
	 */
	function handleAddRemoveButtons (&$step) {
		$wizard =& $step->getWizard();
		
		// handles the addition of style collections
		if ($step->hasComponent('add-collection') && $wizard->allValues['advanced']['add-collection']) {
			$type = $wizard->allValues['advanced']['collection-choices'];
			$step->_children['collection-choices']->setValue('');
			theme_interfaceAction::createStyle($step->getChild('styles-selector'), $type);
		}
		if (isset($wizard->allValues['advanced']['styles-id']) &&
			isset($wizard->allValues['advanced']['styles-selector'])) {
		$styles_id = $wizard->allValues['advanced']['styles-id'];
		$styles_sel = $wizard->allValues['advanced']['styles-selector'];

		// handle id keyed styles
		foreach ($styles_id as $idString => $style) {
			if ($style['remove-'.$idString]) {
				// @todo remove all references to this style
				$buh =& $step->getChild('styles-id');
				$buh->removeChild($idString);
				$buh->setMarkupForComponent('', $idString);
			}
			else if ($style['plus'] && ($style['add-SP'] != '')) {
				// @todo add chosen SP to style
				$buh =& $step->getChild('styles-id');
				$a_buh =& $buh->getChild($idString);
				$SP =& new $style['add-SP'];
				$a_buh->addComponent($style['add-SP'], $SP->getWizardRepresentation);
				
				// buffer for SP markup
				ob_start();
				print "<tr><td>".$SP->getDisplayName()."</td>";
				print "<td>[[".$style['add-SP']."]]</td>";
	
				// create remove button for each SP
				$a_buh->addComponent('remove-'.$style['add-SP'],
										WEventButton::withLabel('-'));
				print "<td>[[remove-".$spid->getIdString()."]]</td>";
				print "</tr>";
				$a_buh->setMarkupForComponent(ob_get_clean(), 
												 $style['add-SP']);
				$a_buh->setContent(
					$a_buh->getContent().$a_buh->getMarkupForComponent(
														$style['add-SP']));
			}
			else foreach ($style as $key => $array)
				if (isset($array['remove-'.$key]) && $array['remove-'.$key])
					// @todo remove the SP from the style
					print 'nothing';
		}
		
		// handle selector keyed styles
		foreach ($styles_id as $selector => $style) {
			if ($style['remove-'.$selector]) {
				// @todo remove all references to this style
				$buh =& $step->getChild('styles-selector');
				$buh->removeChild($idString);
				$buh->setMarkupForComponent('', $idString);
			}
			else if ($style['plus'] && ($style['add-SP'] != '')) {
				// @todo add chosen SP to style
				$buh =& $step->getChild('styles-selector');
				$a_buh =& $buh->getChild($idString);
				$SP =& new $style['add-SP'];
				$a_buh->addComponent($style['add-SP'], $SP->getWizardRepresentation);
				
				// buffer for SP markup
				ob_start();
				print "<tr><td>".$SP->getDisplayName()."</td>";
				print "<td>[[".$style['add-SP']."]]</td>";
	
				// create remove button for each SP
				$a_buh->addComponent('remove-'.$style['add-SP'],
										WEventButton::withLabel('-'));
				print "<td>[[remove-".$spid->getIdString()."]]</td>";
				print "</tr>";
				$a_buh->setMarkupForComponent(ob_get_clean(), 
												 $style['add-SP']);
				$a_buh->setContent(
					$a_buh->getContent().$a_buh->getMarkupForComponent(
														$style['add-SP']));

			}
			else foreach ($style as $key => $array)
				if (isset($array['remove-'.$key]) && $array['remove-'.$key])
					// @todo remove the SP from the style
					print 'nothing';
		}
		}
	}
	
	/**
	 * generates an
	 * 
	 * @param ref object WizardStep
	 * @return void
	 * @access public
	 * @since 5/16/06
	 */
	function updateAdvancedInterface (&$step) {
// @todo oh yeah... this needs to get done
	}
	
	
}

?>