<?php
/**
* @package polyphony.modules.gui
*
* @copyright Copyright &copy; 2006, Middlebury College
* @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
*
* @version $Id: theme_manager3.act.php,v 1.3 2006/08/15 20:51:44 sporktim Exp $
*/


//include_once(HARMONI."GUIManager/Themes/DobomodeTheme.class.php");


 require_once(POLYPHONY."/main/library/GUIWizardComponents/WStyleComponent.class.php");

/**
*
*
* @package polyphony.modules.gui
*
* @copyright Copyright &copy; 2006, Middlebury College
* @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
*
* @version $Id: theme_manager3.act.php,v 1.3 2006/08/15 20:51:44 sporktim Exp $
*/

define("theme_manager3Action_wizardId", 'theme_interface_wizard9_');

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
	* Return the "unauthorized" string to pring <em>Note:  I think that's supposed to mean print--Tim</em>
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
		$cacheName = theme_manager3Action_wizardId;// @todo create unique cache name;

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

		$stepChangedListener =& new WStepChangedListener("theme_manager3Action::handleStepChange");
		$updateListener =& new WUpdateListener("theme_manager3Action::handleUpdate");
		$wizard->addEventListener($stepChangedListener);
		$wizard->addEventListener($updateListener);

		$stepContainer =& $wizard->getStepContainer();

		/*********************************************************
		* LOAD STEP
		*********************************************************/




		$loadStep =& $wizard->addStep('load_step', new WizardStep());
		$loadStep->setDisplayName(_("This is the load step:"));

		ob_start();


		// select list for listing themes user can access
		$loadChoice =& $loadStep->addComponent('load_choice',
		new WSelectList());
		//$loadChoice->addOption('', '(choose a theme to copy or edit)');
		//$loadChoice->setValue('');

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
		$newThemeRule =& WLogicRule::withSteps(array('dd_step', 'global_step','home_step','save_step'));
		$copyButton->setLogic(WLogicRule::withSteps(array('home_step','save_step')));
		$newButton->setLogic($newThemeRule);
		$loadButton->setLogic(WLogicRule::withSteps(array('home_step','save_step')));




		// add markup for step




		// @todo iframe preview of selected id (js onChange)
		print "<table>\n\t<tr>\n\t\t<td rowspan='3'>[[load_choice]]</td>";
		print "\n\t\t<td>[[copy]]</td>\n\t</tr>";
		print "\n\t<tr>\n\t\t<td>[[load]]</td>\n\t</tr>";
		print "\n\t<tr>\n\t\t<td>[[new]]</td>\n\t</tr>";



		$loadStep->setContent(ob_get_contents());
		ob_end_clean();






		/*********************************************************
		* DISPLAYNAME DESCRIPTION STEP
		*********************************************************/
		$ddStep =& $wizard->addStep('dd_step', new WizardStep());

		// display name and description fields for theme
		$dName =& $ddStep->addComponent('display_name', new WTextField());
		$desc =& $ddStep->addComponent('description', new WTextArea());
		$ddStep->addComponent('next', new WNextStepButton($stepContainer));

		// add markup for step
		ob_start();
		print "\n<table width=100%>\n\t<tr>\n\t\t<td>";
		print "\t<table>\n\t\t<tr>\n\t\t\t<td>"._('Display Name:')."</td>";
		print "\n\t\t\t<td>[[display_name]]</td>\n\t\t</tr>";
		print "\n\t\t<tr>\n\t\t<td>"._('Description')."</td>";
		print "\n\t\t\t<td>[[description]]</td>\n\t\t</tr></table>";
		print "\n\t</td><td align=right valign=bottom>";
		print "\n\t\t[[next]]\n\t</td>\n</tr></table>";


		$ddStep->setContent(ob_get_clean());

		/*********************************************************
		* GLOBAL STYLES STEP
		*********************************************************/
		$globalStep =& $wizard->addStep('global_step', new WDynamicStep());
		$globalStep->setDynamicFunction('theme_manager3Action::globalStepCallBack');


		/*********************************************************
		* HOME STEP
		*********************************************************/
		$homeStep =& $wizard->addStep('home_step', new WizardStep());

		//===== BUTTONS =====//
		// Quick Manipulation
		$button =& WLogicButton::withLabel('Quick and Easy Editing');
		$homeStep->addComponent('quick',$button);
		$button->setLogic(WLogicRule::withSteps(array('quick_step','home_step')));

		// D&D
		$button =& WLogicButton::withLabel('Display Name and Description');
		$homeStep->addComponent('d_d',$button);
		$button->setLogic(WLogicRule::withSteps(array('dd_step','home_step')));

		// Global
		$button =& WLogicButton::withLabel('Global Theme Properties');
		$homeStep->addComponent('global',$button);
		$button->setLogic(WLogicRule::withSteps(array('global_step','home_step')));


		// Menu
		$button =& WLogicButton::withLabel('Menu Options');
		$homeStep->addComponent('menu',$button);
		$button->setLogic(WLogicRule::withSteps(array('menu_step','home_step')));

		// Block 1
		$button =& WLogicButton::withLabel('Block 1');
		$homeStep->addComponent('block1',$button);
		$button->setLogic(WLogicRule::withSteps(array('blocks_step1','home_step')));
		// Block 2
		$button =& WLogicButton::withLabel('Block 2');
		$homeStep->addComponent('block2',$button);
		$button->setLogic(WLogicRule::withSteps(array('blocks_step2','home_step')));
		// Block 3
		$button =& WLogicButton::withLabel('Block 3');
		$homeStep->addComponent('block3',$button);
		$button->setLogic(WLogicRule::withSteps(array('blocks_step3','home_step')));
		// Block 4
		$button =& WLogicButton::withLabel('Block 4');
		$homeStep->addComponent('block4',$button);
		$button->setLogic(WLogicRule::withSteps(array('blocks_step4','home_step')));

		// Start Over
		$button =& new WCallbackButton();
		$homeStep->addComponent('load',$button);
		$button->setEventAndLabel('theme_manager3Action::resetToBeginning($this->getWizard());','Back to Beginning');

		// Save
		$homeStep->addComponent('go_save',
		new WNextStepButton($wizard->_stepContainer, dgettext("polyphony","Finish")));

		// add markup for step
		ob_start();
		// @todo iframe for previewing theme
		print "\n<table width=100%>\n\t<tr>\n\t\t<td>";
		print "\n<table>";
		print "\n\t<tr>";
		print "\n\t\t<td colspan='2'>"._('Choose a few simple options for a quick customized theme:')."</td><td colspan='2'>[[quick]]</td>\n\r</tr>";
		print "\n\t<tr>";
		print "\n\t\t<td>"._('Edit the Display Name and Description of this theme:')."</td><td>[[d_d]]</td><td>"._('Edit the Global colors, font, and border styles:')."</td><td>[[global]]</td>\n\t</tr>";
		
		print "\n\t<tr>";
		print "\n\t\t<td>"._('Edit the Menu styles with full customization:')."</td><td>[[menu]]</td>";
		print "\n\t\t<td>"._('Block 1:')."</td><td>[[block1]]</td>\n\t</tr>";
		print "\n\t<tr>";
		
		print "\n\t<tr>";
		print "\n\t\t<td>"._('Block 2:')."</td><td>[[block2]]</td>";
		print "\n\t\t<td>"._('Block 3:')."</td><td>[[block3]]</td>\n\t</tr>";
		print "\n\t<tr>";
		
		print "\n\t<tr>";
		print "\n\t\t<td>"._('Block 4:')."</td><td>[[block4]]</td><td>"._('Edit the Menu styles with full customization:')."</td><td>[[menu]]</td>\n\t</tr>";
		print "\n\t<tr>";
		
		
		print "\n\t\t<td>"._('Go Back to the beginning and choose a theme to modify:')."</td><td>[[load]]</td><td>&nbsp;</td>\n\t</tr>";
		print "\n</table>";
		print "\n\t</td></tr><tr><td align=right valign=bottom>";
		print "\n\t\tSave and finish: [[go_save]]\n\t</td>\n</tr></table>";

		
		

		print $this->addIFrame($homeStep);

		$homeStep->setContent(ob_get_clean());

		/*********************************************************
		* Menu STEP
		*********************************************************/
		$menuStep =& $wizard->addStep('menu_step', new WizardStep());
		$menuStep->addComponent('next', new WNextStepButton($stepContainer));

		// AJAX single edit Collection
		//$menuEditor =& $wizard->addComponent('menu_editor',
		//									new WSingleEditCollection());

		//	$menuStyles =& $guiManager->getMenuStylesForEditor();

		// to populate the SEC:
		// add component collections, who only get printed when chosen
		// from the drop down list to their left

		//$menuStep->addComponent('home',
		//						WEventButton::withLabel('Done with Menus'));

		// add markup for step
		ob_start();
		// @todo iframe for previewing theme
		print _("Edit the look and feel of menus by choosing a style to edit from the drop down list on the left, and then editing the options that avail themeselves on the right")."<br/>";
		print "&nbsp<br/>";
		//print _('Click this button when you are done editing your styles:')."[[home]]";

		print "\n<table width=100%>\n\t<tr>\n\t\t<td>";

		print "\n\t</td><td align=right valign=bottom>";
		print "\n\t\t[[next]]\n\t</td>\n</tr></table>";

		print $this->addIFrame($menuStep);

		$menuStep->setContent(ob_get_clean());

		/*********************************************************
		* BLOCK1 STEP
		*********************************************************/
		
		
		for($i=1; $i<=4; $i++){
			$blockStep =& $wizard->addStep('blocks_step'.$i, new WDynamicStep());
			$blockStep->setDynamicFunction('theme_manager3Action::blockStep'.$i.'CallBack');
		}
		
		/*
		$blocksStep =& $wizard->addStep('blocks_step', new WizardStep());
		$blocksStep->addComponent('next', new WNextStepButton($stepContainer));
		// AJAX single edit collection
		//$blockEditor =& $wizard->addComponent('block_editor',
		//							new WSingleEditCollection());

		//	$blockStyles =& $guiManager->getBlockStylesForEditor();

		// same as menu step

		//$blocksStep->addComponent('home',
		//						WEventButton::withLabel('Done with Content'));

		// add markup for step
		ob_start();
		// @todo iframe for previewing theme
		print _("Edit the look and feel of content areas by choosing a style to edit from the drop down list on the left, and then editing the options that avail themeselves on the right")."<br/>";
		print "block_editor<br/>";

		print "\n<table width=100%>\n\t<tr>\n\t\t<td>";

		print "Blocks";

		print "\n\t</td><td align=right valign=bottom>";
		print "\n\t\t[[next]]\n\t</td>\n</tr></table>";


		print $this->addIFrame($blocksStep);

		$blocksStep->setContent(ob_get_clean());*/

		/*********************************************************
		* SAVE STEP
		*********************************************************/

		$saveStep =& $wizard->addStep('save_step', new WizardStep());

		// how do you want to save your theme
		$saveStyle =& $saveStep->addComponent('save_style', new WSelectList());
		$saveStyle->addOption('new', _('Save as a new Theme'));
		$saveStyle->addOption('update', _('Save as updated Theme'));
		if (theme_manager3Action::isAuthorizedToTemplate()) {
			$saveStyle->addOption('public', _('Save as new public Theme'));
			$saveStyle->addOption('delete', _('Remove Theme from system'));
		}

		$saveStep->addComponent('save', new WSaveButton());
		$saveStep->addComponent('quit', new WCancelButton("Quit"));

		ob_start();
		print _("Here you must choose how to save your Theme.  You can save your work as a new Theme (if you were working on an existant Theme this will leave the original Theme unharmed).  You can also save your work as an updated Theme, which will replace the original Theme you may have loaded (if you have permission to do so).");
		print "<br/>";
		print "<table width=100%><tr><td>[[save_style]][[save]]</td><td align=right>[[quit]]</td></tr></table>";
		$saveStep->setContent(ob_get_clean());

		//add steps:

		$wizard->setRequiredSteps(array('load_step'));

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

		
		if($wizard->validate()){
			print "success!";	
		}else{
			print "fail!";	
		}
		
		
		//printpre($wizard->getAllValues());

		$theme =& theme_manager3Action::getTheme();

		if(!is_null($theme)){


			print $theme->getCSS();


			//$guiManager->setTheme($theme);

		}else{
			print "poop sticks! I can't find the theme to save!";
		}

		//exit;
	}


	/**
	* This adds an iframe to the page
	*
	* @return void
	* @access public
	* @since 8/8/06
	*/
	function addIFrame(&$theStep) {
		$s = "";
		$harmoni =& Harmoni::instance();

		$url = $harmoni->request->quickURL("gui","test_page", array("theme"=>theme_manager3Action_wizardId."__theme"));

		$refreshButton =& $theStep->addComponent('refresh_preview',
		WEventButton::withLabel(_('Refresh...')));


		$s .= "<hr />";
		$s .= "\n\t<table width='100%'><tr>";
		$s .= "\n\t\t<td align='left'><h2>Preview: </h2></td>";
		$s .= "\n\t\t<td align='right'>Refresh preview: [[refresh_preview]]</td>";
		$s .= "</tr></table>";
		$s .= "\n\t\t<div style='background-color: #FFF'>";
		$s .= "\n\t\t\t<iframe src='".$url."'width='100%' height='500' frameborder=1 scrolling='auto'>";
		$s .= "\n\t\t\t\t<a href='".$url."'>Preview</a>";
		$s .= "\n\t\t\t</iframe>";
		$s .= "\n\t\t</div>";

		return $s;
	}


	/**
	*
	* @return void
	* @access public
	* @since 6/1/06
	*/
	function handleUpdate ($source, $context) {
		$wizard =& $source->getWizard();
		$steps =& $wizard->getStepContainer();
		$stepName = $steps->getCurrentStepName();
		$step =& $steps->getStep($stepName);
		$values = $wizard->getAllValues();	
		$theme =& theme_manager3Action::getTheme();
		if(!is_null($theme)){
		}else{
			if($context['from']!="load_step"){
				print "poop sticks! I can't find the theme!";
			}
		}	
	}

	function blockStep1CallBack(&$step){
		return theme_manager3Action::blockStep($step,1);
	}
	
	function blockStep2CallBack(&$step){
		return theme_manager3Action::blockStep($step,2);
	}
	
	function blockStep3CallBack(&$step){
		return theme_manager3Action::blockStep($step,3);
	}
	
	function blockStep4CallBack(&$step){
		return theme_manager3Action::blockStep($step,4);
	}
	
	function blockStep(&$step,$index){
	
		
		
		
		$s = "";
		
	
		
		$wizard =& $step->getWizard();
		$stepContainer =& $wizard->getStepContainer();		
		$theme =& theme_manager3Action::getTheme();
		
		
		//$blockEditor =& new WBlockEditor("theme_manager3Action::getTheme",$index);
		$fontEditor1 =& new WFontEditor("theme_manager3Action::getTheme","font1",MENU,1);
		$fontEditor2 =& new WFontEditor("theme_manager3Action::getTheme","font2",BLOCK,2);
		$fontEditor3 =& new WFontEditor("theme_manager3Action::getTheme","font3",HEADING,2);
		$fontEditor4 =& new WFontEditor("theme_manager3Action::getTheme","font4",HEADING,1);
		

		$step->setComponent('next', new WNextStepButton($stepContainer));
		//$step->setComponent('block', $blockEditor);
		$step->setComponent('font1', $fontEditor1);
		$step->setComponent('font2', $fontEditor2);
		$step->setComponent('font3', $fontEditor3);
		$step->setComponent('font4', $fontEditor4);
		
		$s.="Feel free to modify the block: ";
		
		
		
		
		$s.= "\n<table width=100%>\n\t<tr>";
		//$s.= "<td>[[block]]</td>";
		$s.= "</tr><td>Font menu 1</td><td>[[font1]]</td><tr>";
		$s.= "</tr><td>Font block 2</td><td>[[font2]]</td><tr>";
		$s.= "</tr><td>Font heading 2</td><td>[[font3]]</td><tr>";
		$s.= "</tr><td>Font heading 1</td><td>[[font4]]</td><tr>";

		$s.= "<td align=right valign=bottom>";
		$s.= "\n\t\t[[next]]\n\t</td>\n</tr></table>";
		
		$s.= theme_manager3Action::addIFrame($step);
		
		return $s;
	}
	
	
	function globalStepCallBack(&$step){
		$s = "";

		$wizard =& $step->getWizard();
		$stepContainer =& $wizard->getStepContainer();		
		$theme =& theme_manager3Action::getTheme();
				
		if(!is_null($theme)){
			$styleCollections =& $theme->getStyleCollections();
			
			$children =& $step->getChildren();
			foreach(array_keys($children) as $key){				
				$step->removeChild($key);						
			}
			

			// two style property objects
			//$currentBG =& $guiManager->getGlobalBGColor();
			//$currentFont =& $guiManager->getGlobalFont();

			// string for the class
			//$currentCollectionClass = $guiManager->getCollectionClass();

			//next step
			$step->setComponent('next', new WNextStepButton($stepContainer));


			// @todo iframe preview of bgcolor and font (js onChange)
			$s.= "Global Styles:<br/>";
			$s.= "Here you can choose a few simple global attributes for your theme.<br/>";
			$s.= "\n<table width=100%>\n\t<tr>\n\t\t<td>";
			$s.= "<table border=0>\n\t";
			//print "<tr>";
			
			$i = 0;
			foreach(array_keys($styleCollections) as $key){	
					$i++;	
					$name = 'collection_'.$i;	
							
					//$comp =& $styleCollections[$key]->getWizardRepresentation("theme_manager3Action::getTheme");
					$comp =& new WStyleCollection("theme_manager3Action::getTheme",$key);
					
					
					
					$step->setComponent($name,$comp);
					
					
					$s.= "\n\t<tr>";
					$s.= "\n\t\t<td>[[".$name."]]</td>";
					$s.= "\n\t</tr>";
				
			}
			
	
			$s.= "\n\t</table>";
			$s.= "\n\t</td><td align=right valign=bottom>";
			$s.= "\n\t\t[[next]]\n\t</td>\n</tr></table>";
			

		
			$s.= theme_manager3Action::addIFrame($step);



		}else{
			$s.= "POOP! I can't find the theme";

		}

		return $s;
	}
	
	function refreshTheme($step){
		$vals = $step->getAllValues();	
		
		
		//print_r($vals);
		
		//print "---------------------------------------<br />";
		//print "---------------------------------------<br />";
		//print "---------------------------------------<br />";
		//print "---------------------------------------<br />";
		
		
		$theme =& theme_manager3Action::getTheme();
		
		if(is_null($theme)){
			return;	
		}
		
		$collections = $theme->getStyleCollections();
		
		foreach($collections as $coll){
			//print "<br />--------------_____________----------<br />";
			//print_r($coll);
		}
		
		
		
		
	}


	/**
	* This is the all important function that gives the Wizard control after
	* all of its components have been updated since the last pagelaod
	*
	* @return void
	* @access public
	* @since 6/1/06
	*/
	function handleStepChange ($source, $context) {

		$wizard =& $source->getWizard();


		
		

		$values = $wizard->getAllValues();
		
	

		print " From: '".$context['from']."' to '".$context['to']."' ";


		if($context['from']=="load_step"){

			if($context['to']=="dd_step"){
				print "Making a default theme";


				$codeToExecute = '$_SESSION["'.$wizard->getIdString().'__theme"] =& new '.$startingTheme.'();';


				$_SESSION[theme_manager3Action_wizardId.'__theme'] =& new SimpleLinesTheme();

			}else{



				$startingTheme=$values["load_step"]["load_choice"];
				print "Making a theme";


				$codeToExecute = '$_SESSION["'.theme_manager3Action_wizardId.'__theme"] =& new '.$startingTheme.'();';

				print $codeToExecute;

				eval($codeToExecute);
			}



		}


		//$wizard =& $source->getWizard();
		//$stepContainer =& $wizard->getStepContainer();
		//$stepName =  $stepContainer->getCurrentStepName();


		//
		//if($context['from']==='home_step' && $context['to']==='load_step'){
		//	$wizard->_stepContainer->clearHistoryAnd;
		//}

		//<##>
	}

	/**
	* This is the all important function that gives the Wizard control after
	* all of its components have been updated since the last pagelaod
	*
	* @return void
	* @access public
	* @since 6/1/06
	*/
	function resetToBeginning($wizard) {
		print "reset";
		$wizard->setRequiredSteps(array('load_step'));
		//print " From: '".$context['from']."' to '".$context['to']."' ";


		//$wizard =& $source->getWizard();
		//if($context['from']==='home_step' && $context['to']==='load_step'){
		//$wizard->_stepContainer->clearHistoryAnd;
		//}

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
		return $harmoni->request->quickURL("user", "main");
		//return $url->write();
	}

	
	/*
	function &getTheme (){
		//return $this->_theme;
		
		
		if( isset($_SESSION[theme_manager3Action_wizardId."__theme"])){
			
			return $_SESSION[theme_manager3Action_wizardId."__theme"];
		}else{
			$null = null;
			return $null;
		}
		
		

	}*/
	

	
	function &getTheme(){
		return $_SESSION[theme_manager3Action_wizardId."__theme"];
	}
	
	
	
	
	



}