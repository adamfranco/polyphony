<?php
/**
* @package polyphony.modules.gui
*
* @copyright Copyright &copy; 2006, Middlebury College
* @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
*
* @version $Id: theme_editor.act.php,v 1.1 2006/08/19 21:08:41 sporktim Exp $
*/


//Note:  If you are interested in old CVS versions of this file, try theme_editor.act.php



/**
*
*
* @package polyphony.modules.gui
*
* @copyright Copyright &copy; 2006, Middlebury College
* @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
*
* @version $Id: theme_editor.act.php,v 1.1 2006/08/19 21:08:41 sporktim Exp $
*/

define("theme_editorAction_wizardId", 'theme_editor_wizard3_');

class theme_editorAction
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
		$cacheName = theme_editorAction_wizardId;// @todo create unique cache name;

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

		$stepChangedListener =& new WStepChangedListener("theme_editorAction::handleStepChange");
		$updateListener =& new WUpdateListener("theme_editorAction::handleUpdate");
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
		$newThemeRule =& WLogicRule::withSteps(array('dd_step', 'home_step','save_step'));
		$copyButton->setLogic(WLogicRule::withSteps(array('home_step','save_step')));
		$newButton->setLogic($newThemeRule);
		$loadButton->setLogic(WLogicRule::withSteps(array('home_step','save_step')));




		// add markup for step




		print "<table>\n\t<tr>\n\t\t<td rowspan='3'>[[load_choice]]</td>";
		print "\n\t\t<td>[[copy]]</td>\n\t</tr>";
		print "\n\t<tr>\n\t\t<td>[[load]]</td>\n\t</tr>";
		print "\n\t<tr>\n\t\t<td>[[new]]</td>\n\t</tr>";


		//@todo--perhaps a way to sample what each theme looks like would be helpful.  An iframe could be useful here, but some modification is needed before it would work--the theme is not created until the step changes.

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
		$globalStep->setDynamicFunction('theme_editorAction::globalStepCallBack');


		/*********************************************************
		* HOME STEP AND EDITING STEPS
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
		$button =& WLogicButton::withLabel('Semi-obselete way to fix things');
		$homeStep->addComponent('global',$button);
		$button->setLogic(WLogicRule::withSteps(array('global_step','home_step')));


		// Start Over
		$button =& new WCallbackButton();
		$homeStep->addComponent('load',$button);
		$button->setEventAndLabel('theme_editorAction::resetToBeginning($this->getWizard());','Back to Beginning');

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
		print "\n\t\t<td>"._('Edit the Display Name and Description of this theme:')."</td><td>[[d_d]]</td><td>"._('This is an old way to edit properties.  It has the advantage that it can edit some global properties:')."</td><td>[[global]]</td>\n\t";

	


	
		
		////////////////////////////////////////////////////////////////////////////////////
		//Buttons and Steps
		//
		//remember that the editing steps are created here!
		///////////////////////////////////////////////////////////////////////////////////
		
		print "\n\t</tr><tr>";
		
		//options
		$name = "block1";
		$label = "Background";		
		$explanation = "Edit the main background:";		
		$info = "Your entire site will apper on top of this block.";
		$arr = array(array('type'=>BLOCK,'index'=>1));
		//execute
		$this->addAnEditingStep($wizard, $name,$arr, $info, false, true, false);
		$button =& WLogicButton::withLabel($label);
		$homeStep->addComponent($name.'_button',$button);
		$button->setLogic(WLogicRule::withSteps(array($name.'_step','home_step')));
		print "\n\t\t<td>"._($explanation)."</td><td>[[".$name."_button]]</td>";
		
		//options
		$name = "block2";
		$label = "Content";		
		$explanation = "Edit the appearance of your content.  It should be easy to read.";		
		$info = "In this step you can modify the look of your main content";
		$arr = array(array('type'=>BLOCK,'index'=>2));
		//execute
		$this->addAnEditingStep($wizard, $name,$arr, $info, true, true, true);
		$button =& WLogicButton::withLabel($label);
		$homeStep->addComponent($name.'_button',$button);
		$button->setLogic(WLogicRule::withSteps(array($name.'_step','home_step')));
		print "\n\t\t<td>"._($explanation)."</td><td>[[".$name."_button]]</td>";
		
		print "\n\t</tr><tr>";
		
		//options
		$name = "heading1";
		$label = "Heading";		
		$explanation = "Edit the main Heading:";		
		$info = "This is where the name of each page should appear.  It should be noticable.";
		$arr = array(array('type'=>HEADING,'index'=>1));
		//execute
		$this->addAnEditingStep($wizard, $name,$arr, $info, true, true, false);
		$button =& WLogicButton::withLabel($label);
		$homeStep->addComponent($name.'_button',$button);
		$button->setLogic(WLogicRule::withSteps(array($name.'_step','home_step')));
		print "\n\t\t<td>"._($explanation)."</td><td>[[".$name."_button]]</td>";
		
		//options
		$name = "heading2";
		$label = "Subheading";		
		$explanation = "Edit the headings in your content";		
		$info = "To separate pieces of your content, you use headings like this.  They should stand out from the main text.";
		$arr = array(array('type'=>HEADING,'index'=>2));
		//execute
		$this->addAnEditingStep($wizard, $name,$arr, $info, true, true, false);
		$button =& WLogicButton::withLabel($label);
		$homeStep->addComponent($name.'_button',$button);
		$button->setLogic(WLogicRule::withSteps(array($name.'_step','home_step')));
		print "\n\t\t<td>"._($explanation)."</td><td>[[".$name."_button]]</td>";
		
		print "\n\t</tr><tr>";
		
		//options
		$name = "header";
		$label = "Header";		
		$explanation = "Edit the header:";		
		$info = "This appears at the very top and likely won't change much.";
		$arr = array(array('type'=>HEADER,'index'=>1));
		//execute
		$this->addAnEditingStep($wizard, $name,$arr, $info, true, true, false);
		$button =& WLogicButton::withLabel($label);
		$homeStep->addComponent($name.'_button',$button);
		$button->setLogic(WLogicRule::withSteps(array($name.'_step','home_step')));
		print "\n\t\t<td>"._($explanation)."</td><td>[[".$name."_button]]</td>";
		
		//options
		$name = "footer";
		$label = "Footer";		
		$explanation = "Edit the footer";		
		$info = "This appears at the very bottom.  It could look like the header for symmetry or could be completely different";
		$arr = array(array('type'=>FOOTER,'index'=>1));
		//execute
		$this->addAnEditingStep($wizard, $name,$arr, $info, true, true, false);
		$button =& WLogicButton::withLabel($label);
		$homeStep->addComponent($name.'_button',$button);
		$button->setLogic(WLogicRule::withSteps(array($name.'_step','home_step')));
		print "\n\t\t<td>"._($explanation)."</td><td>[[".$name."_button]]</td>";


		print "\n\t</tr><tr>";

		print "\n\t\t<td>"._('Go Back to the beginning and choose a theme to modify:')."</td><td>[[load]]</td><td>&nbsp;</td>\n\t</tr>";
		print "\n</table>";
		print "\n\t</td></tr><tr><td align=right valign=bottom>";
		print "\n\t\tSave and finish: [[go_save]]\n\t</td>\n</tr></table>";




		print $this->addIFrame($homeStep);

		$homeStep->setContent(ob_get_clean());


		/*********************************************************
		* SAVE STEP
		*********************************************************/

		//@todo read the text that I tell it to print out.  I suspect that this step should be deleted.

		$saveStep =& $wizard->addStep('save_step', new WizardStep());

		// how do you want to save your theme
		$saveStyle =& $saveStep->addComponent('save_style', new WSelectList());
		$saveStyle->addOption('new', _('Save as a new Theme'));
		$saveStyle->addOption('update', _('Save as updated Theme'));
		if (theme_editorAction::isAuthorizedToTemplate()) {
			$saveStyle->addOption('public', _('Save as new public Theme'));
			$saveStyle->addOption('delete', _('Remove Theme from system'));
		}

		$saveStep->addComponent('save', new WSaveButton());
		$saveStep->addComponent('quit', new WCancelButton("Quit"));

		ob_start();
		print _("This is not really edited from Shubert's old code, so if you're the one implementing save, that's too bad--not done.  This is really not very liekly to help much.  As a hint, the first step (load_step) and this last step should not both be here.  Choose one or the other.  The other hint I can give you is that the saving and loading should be very easy, in theory.  Just use the GUIManager to pull it off.  I think you need to set the current theme to be the one you want to save, then can the save function.  Be sure that you restore the theme though, or the page will display the new theme--cool, but likely problematic.  Also, don't forget that the wizard has a save button.  I'd say remove this step completely, decide how you are loading in the load step, then use that save.  Actually probably pretty easy.
		
		<p> Here's the old text:</p>
		
		
		Here you must choose how to save your Theme.  You can save your work as a new Theme (if you were working on an existant Theme this will leave the original Theme unharmed).  You can also save your work as an updated Theme, which will replace the original Theme you may have loaded (if you have permission to do so).");
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
		//@todo this does not actually save the theme.  Maybe it should.  See the save step for hints.

		$guiManager =& Services::getService("GUI");
		$wizard =& $this->getWizard($cacheName);


		if($wizard->validate()){
			print "success!";
		}else{
			print "fail!";
			return;
		}
		$theme =& theme_editorAction::getTheme();

		if(!is_null($theme)){
			print $theme->getCSS();
		}
	}


	/**
	* This adds an iframe to the page that previews the frame, plus a button to refresh the view
	*
	* @return void
	* @access public
	* @since 8/8/06
	*/
	function addIFrame(&$theStep) {

		//@todo It wouldn't be all that hard to add a series of three radio buttons, each allowing a new theme.  It would be really cool and quite useful.

		//@todo I'm not sure if its a good idea or easy, but here's an idea--every time the user changes something, the thing relods, probably through javascript.  I suspect it would make work frustratingly slow and be difficult though.

		$s = "";
		$harmoni =& Harmoni::instance();

		$url = $harmoni->request->quickURL("gui","test_page", array("theme"=>theme_editorAction_wizardId."__theme"));

		$refreshButton =& $theStep->addComponent('refresh_preview',
		WEventButton::withLabel(_('Refresh...')));


		$s .= "<hr />";
		$s .= "\n\t<table width='100%'><tr>";
		$s .= "\n\t\t<td align='left'><h2>Preview: </h2></td>";
		$s .= "\n\t\t<td align='right'>Refresh preview: [[refresh_preview]]</td>";
		$s .= "</tr></table>";
		$s .= "\n\t\t<div style='background-color: #FFF'>";
		$s .= "\n\t\t\t<iframe src='".$url."'width='100%' height='700' frameborder=1 scrolling='auto'>";
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
		//do you need control of the Wizard each step?  This could be very useful.  You could probably use this to replace the dynamic step if need be.

	}



	function globalStepCallBack(&$step){
		$s = "";

		$wizard =& $step->getWizard();
		$stepContainer =& $wizard->getStepContainer();
		$theme =& theme_editorAction::getTheme();

		if(!is_null($theme)){
			$styleCollections =& $theme->getStyleCollections();

			$children =& $step->getChildren();
			foreach(array_keys($children) as $key){
				$step->removeChild($key);
			}

			//next step
			$step->setComponent('next', new WNextStepButton($stepContainer));


			$s.= "Global Styles:<br/>";
			$s.= "Here you can choose a few simple global attributes for your theme.<br/>";
			$s.= "\n<table width=100%>\n\t<tr>\n\t\t<td>";
			$s.= "<table border=0>\n\t";

			$i = 0;
			foreach(array_keys($styleCollections) as $key){
				$i++;
				$name = 'collection_'.$i;
				$comp =& new WStyleCollection("theme_editorAction::getTheme",$key);
				$step->setComponent($name,$comp);
				$s.= "\n\t<tr>";
				$s.= "\n\t\t<td>[[".$name."]]</td>";
				$s.= "\n\t</tr>";
			}
			$s.= "\n\t</table>";
			$s.= "\n\t</td><td align=right valign=bottom>";
			$s.= "\n\t\t[[next]]\n\t</td>\n</tr></table>";
			$s.= theme_editorAction::addIFrame($step);
		}

		return $s;
	}


	/**
	* This gives the wizard control after each change in step and tells which the previous and current steps are.
	*
	* @return void
	* @access public
	* @since 6/1/06
	*/
	function handleStepChange ($source, $context) {
		//@todo This function has a serious problem--it can't tell which button was pressed.  It really ought to be part of the context, but it isn't.  A new event listeneter for each button is silly, but a generic one might be possible.  Another idea is just using a WCallback button.  Another is storing the fieldname and passing that in.

		
		//@todo This is unfortunate in that hitting back to this step will delete all your changes.  If you fix the above problem, that should go away.
		
		$wizard =& $source->getWizard();
		$values = $wizard->getAllValues();
		if($context['from']=="load_step"){
			if($context['to']=="dd_step"){
				$_SESSION[theme_editorAction_wizardId.'__theme'] =& new SimpleLinesTheme();
			}else{
				$startingTheme=$values["load_step"]["load_choice"];
				$codeToExecute = '$_SESSION["'.theme_editorAction_wizardId.'__theme"] =& new '.$startingTheme.'();';
				eval($codeToExecute);
			}
		}
	}


	function addAnEditingStep($wizard, $name, $arr, $info, $font, $bg, $text ){

		$stepContainer =& $wizard->getStepContainer();

		
		
		$step =& $wizard->addStep($name.'_step', new WizardStep());
		$step->addComponent('next', new WNextStepButton($stepContainer));
		$step->addComponent('main', new WMultiCollection("theme_editorAction::getTheme",$name,$arr,$font,$bg, $text));
		
		$s = "";
		$s.= $info;
		$s.= "<p>[[main]]</p>";
		$s.= "<p align='right'>[[next]]</p>";
		$s.= theme_editorAction::addIFrame($step);
		
		$step->setContent($s);
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
		//@todo this, of course, should be in the user folder eventually.
		return $harmoni->request->quickURL("admin", "main");
	}


	function &getTheme(){
		return $_SESSION[theme_editorAction_wizardId."__theme"];
	}








}