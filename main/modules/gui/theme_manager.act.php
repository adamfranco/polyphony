<?php
/**
 * @package polyphony.modules.gui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: theme_manager.act.php,v 1.1 2006/06/02 16:00:29 cws-midd Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
//require_once(POLYPHONY."/main/library/Wizard/LogicStepWizard.class.php");
require_once(POLYPHONY."/main/library/Wizard/SimpleStepWizard.class.php");
require_once(POLYPHONY."/main/library/Wizard/Components/WizardStep.class.php");
require_once(POLYPHONY."/main/library/Wizard/Components/WColorWheel.class.php");

/**
 * 
 * 
 * @package polyphony.modules.gui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: theme_manager.act.php,v 1.1 2006/06/02 16:00:29 cws-midd Exp $
 */
class theme_managerAction 
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
		$cacheName = 'theme_interface_wizard_';// @todo create unique cache name
		
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
		$wizard =& SimpleStepWizard::withDefaultLayout();
		// $wizard =& LogicStepWizard::withDefaultLayout();
		
	/*********************************************************
	 * LOAD STEP
	 *********************************************************/
		$loadStep =& $wizard->addStep('load_step', new WizardStep());
		
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
		$loadStep->addComponent('copy',
								WEventButton::withLabel(_('Copy to New...')));
		$loadStep->addComponent('load', 
								WEventButton::withLabel(_('Load for Edit...')));
		$loadStep->addComponent('new',
								WEventButton::withLabel(_('Create Empty...')));
		
		// add markup for step
		ob_start();
		// @todo iframe preview of selected id (js onChange)
		print "<table>\n\t<tr>\n\t\t<td rowspan='3'>[[load_choice]]</td>";
		print "\n\t\t<td>[[copy]]</td>\n\t</tr>";
		print "\n\t<tr>\n\t\t<td>[[load]]</td>\n\t</tr>";
		print "\n\t<tr>\n\t\t<td>[[new]]</td>\n\t</tr>";
		$loadStep->setContent(ob_get_clean());
		
	/*********************************************************
	 * DISPLAYNAME DESCRIPTION STEP
	 *********************************************************/
		$ddStep =& $wizard->addStep('dd_step', new WizardStep());
		
		// display name and description fields for theme
		$dName =& $ddStep->addComponent('display_name', new WTextField());
		$desc =& $ddStep->addComponent('description', new WTextArea());
		
		// add markup for step
		ob_start();
		print "<table>\n\t<tr>\n\t\t<td>"._('Display Name:')."</td>";
		print "\n\t\t<td>[[display_name]]</td>\n\t</tr>";
		print "\n\t<tr>\n\t\t<td>"._('Description')."</td>";
		print "\n\t\t<td>[[description]]</td>\n\t</tr>";
		$ddStep->setContent(ob_get_clean());
		
	/*********************************************************
	 * GLOBAL STYLES STEP
	 *********************************************************/
		$globalStep =& $wizard->addStep('global_step', new WizardStep());
		
		// two style property objects
		$currentBG =& $guiManager->getGlobalBGColor();
		$currentFont =& $guiManager->getGlobalFont();
		
		// string for the class
		$currentCollectionClass = $guiManager->getCollectionClass();
		
		// @todo handle current BGColor/Font logic (populate wiz)
		// @todo re-work the global stuff (new vs. old)
		
/*		
// background color and font settings
$namedColors = array (_("aqua"), _("black"), _('blue'), _('fuchsia'),
	_('gray'), _('green'), _('lime'), _('maroon'), _('navy'), 
	_('olive'), _('purple'), _('red'), _('silver'), _('teal'),
	_('white'), _('yellow'));

// background color
$bgcolor =& $globalStep->addComponent('global_bgcolor', new WSelectList());
foreach ($namedColors as $color) {
	if ($color == 'black' || $color == 'navy' || $color == 'blue')
		$bgcolor->addOption($color, $color, "background-color:$color; color:white;");
	else
		$bgcolor->addOption($color, $color, "background-color:$color;");
}
$bgcolor->setValue('white');

$sp =& new FontSP();
*/
		$bgcolor =& $globalStep->addComponent('global_bgcolor',
							$currentBG->getWizardRepresentation());
		// handle wizard representations of colors differently (color wheel)

		$globalStep->addComponent('global-font',
							$currentFont->getWizardRepresentation());

		// choose the style of the collections (rounded corners etc)
		$blockStyle =& $globalStep->addComponent('block_style',
												 new WRadioList());
		
		// get the options for block classes and make radio options
		$styleClasses = $guiManager->getSupportedStyleCollections();
		foreach ($styleClasses as $class) {
			$blockStyle->addOption($class, $class);
		}

		// add markup for step
		ob_start();
		// @todo iframe preview of bgcolor and font (js onChange)
		print "Global Styles:<br/>";
		print "Here you can choose a few simple global attributes for your theme.<br/>";
		print "<table border=2>\n\t<tr>";
		print "\n\t\t<td>"._('Choose a Background Color for your Theme:')."</td>";
		print "\n\t\t<td>[[global-bgcolor]]</td>\n\t</tr>";
		print "\n\t<tr>\n\t\t<td>"._('Choose attributes for your global font style:')."</td>";
		print "\n\t\t<td>[[global-font]]</td>";
		print "\n\t</tr>";
		print "\n\t<tr>";
		print "\n\t\t<td>"._('Choose a style for borders of blocks')."</td>";
		print "\n\t\t<td>[[block_style]][</td>";
		print "\n\t</tr>\n</table>";
		$step->setContent(ob_get_clean());
		
	/*********************************************************
	 * HOME STEP
	 *********************************************************/
		$homeStep =& $wizard->addStep('home_step', new WizardStep());
		
		//===== BUTTONS =====//
			// Quick Manipulation
			$homeStep->addComponent('quick',
							WEventButton::withLabel('Quick and Easy Editing'));
			
			// D&D
			$homeStep->addComponent('d_d',
					WEventButton::withLabel('Display Name and Description'));

			// Global
			$homeStep->addComponent('global',
							WEventButton::withLabel('Global Theme Properties'));

			// Menu
			$homeStep->addComponent('menu',
							WEventButton::withLabel('Menu Options'));

			// Blocks
			$homeStep->addComponent('blocks',
							WEventButton::withLabel('Content Options'));

			// Start Over
			$homeStep->addComponent('load',
							WEventButton::withLabel('Back to Beginning'));
			
			// Save
			$homeStep->addComponent('save',
							WEventButton::withLabel('Save This Theme'));
							
		// add markup for step
		ob_start();
		// @todo iframe for previewing theme
		print "\n<table>";
		print "\n\t<tr>";
		print "\n\t\t<td colspan='2'>"._('Choose a few simple options for a quick customized theme:')."</td><td colspan='2'>[[quick]]</td>\n\r</tr>";
		print "\n\t<tr>";
		print "\n\t\t<td>"._('Edit the Display Name and Description of this theme:')."</td><td>[[d_d]]</td><td>"._('Edit the Global colors, font, and border styles:')."</td><td>[[global]]</td>\n\t</tr>";
		print "\n\t<tr>";
		print "\n\t\t<td>"._('Edit the Menu styles with full customization:')."</td><td>[[menu]]</td><td>"._('Edit the Content styles with full customization:')."</td><td>[[blocks]]</td>\n\t</tr>";
		print "\n\t<tr>";
		print "\n\t\t<td>"._('Go Back to the beginning and choose a theme to modify:')."</td><td>[[load]]</td><td>"._('Complete Theme modification by saving your theme:')."</td><td>[[save]]</td>\n\t</tr>";
		print "\n</table>";
		$homeStep->setContent(ob_get_clean());

	/*********************************************************
	 * MENU STEP
	 *********************************************************/
		$menuStep =& $wizard->addStep('menu_step', new WizardStep());
		
		// AJAX single edit Collection
		$menuEditor =& $wizard->addComponent('menu_editor', 
											new WSingleEditCollection());

		$menuStyles =& $guiManager->getMenuStylesForEditor();
		
		// to populate the SEC:
			// add component collections, who only get printed when chosen
			// from the drop down list to their left
		
		$menuStep->addComponent('home',
								WEventButton::withLabel('Done with Menus'));
		
		// add markup for step
		ob_start();
		// @todo iframe for previewing theme
		print _("Edit the look and feel of menus by choosing a style to edit from the drop down list on the left, and then editing the options that avail themeselves on the right")."<br/>";
		print "[[menu_editor]]<br/>";
		print _('Click this button when you are done editing your styles:')."[[home]]";
		$menuStep->setContent(ob_get_clean());
		
	/*********************************************************
	 * BLOCKS STEP
	 *********************************************************/
		$blocksStep =& $wizard->addStep('blocks_step', new WizardStep());
		
		// AJAX single edit collection
		$blockEditor =& $wizard->addComponent('block_editor',
									new WSingleEditCollection());
									
		$blockStyles =& $guiManager->getBlockStylesForEditor();
		
		// same as menu step

		$menuStep->addComponent('home',
								WEventButton::withLabel('Done with Content'));

		// add markup for step
		ob_start();
		// @todo iframe for previewing theme
		print _("Edit the look and feel of content areas by choosing a style to edit from the drop down list on the left, and then editing the options that avail themeselves on the right")."<br/>";
		print "[[block_editor]]<br/>";
		print _('Click this button when you are done editing your styles:')."[[home]]";
		$menuStep->setContent(ob_get_clean());

	/*********************************************************
	 * SAVE STEP
	 *********************************************************/

		$saveStep =& $wizard->addStep('save_step', new WizardStep());
		
		// how do you want to save your theme
		$saveStyle =& $wizard->addComponent('save_style', new WSelectList());
		$saveStyle->addOption('new', _('Save as a new Theme'));
		$saveStyle->addOption('update', _('Save as updated Theme'));
		if (theme_managerAction::isAuthorizedToTemplate()) {
			$saveStyle->addOption('public', _('Save as new public Theme'));
			$saveStyle->addOption('delete', _('Remove Theme from system'));
		}
		
		$saveStep->addComponent('save', new WSaveButton());
		
		ob_start();
		print _("Here you must choose how to save your Theme.  You can save your work as a new Theme (if you were working on an existant Theme this will leave the original Theme unharmed).  You can also save your work as an updated Theme, which will replace the original Theme you may have loaded (if you have permission to do so).");
		print "<br/>";
		print "<table><tr><td>[[save_style]]</td><td>[[save]]</td></tr></table>";
		$saveStep->setContent(ob_get_clean());


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