<?php
/**
 * @package polyphony.coursemanagement
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: createnewtype.act.php,v 1.5 2007/09/19 14:04:54 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(HARMONI."/utilities/StatusStars.class.php");

class createnewtypeAction
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
		// Check that the user can create a type here.
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		
		return $authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.add_children"),
			$idManager->getId("edu.middlebury.coursemanagement")
		);
	}
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to create a CourseManagement Type.");
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return _("Create a Type.");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$harmoni = Harmoni::instance();
		$actionRows =$this->getActionRows();
		$cacheName = "createnewtypeWizard";
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
	
	function createWizard () {
		$harmoni = Harmoni::instance();
		//$courseManager = Services::getService("CourseManagement");
		//$canonicalCourseIterator =$courseManager->getCanonicalCourses();
		
		// Instantiate the wizard, then add our steps.
		$wizard = SimpleStepWizard::withDefaultLayout();
		
		// :: Name and Description ::
		$step =$wizard->addStep("namedescstep", new WizardStep());
		$step->setDisplayName(_("Please choose the name and type for this type:"));
		
		
		
		// Create the type chooser.
		$select = new WSelectList();
		//$select->addOption('',"Canonical Course Type");
		$select->addOption('can',"Canonical Course Type");
		$select->addOption('can_stat',"Canonical Course Status Type");
		$select->addOption('offer',"Course Offering Type");
		$select->addOption('offer_stat',"Course Offering Status Type");
		$select->addOption('section',"Course Section Type");
		$select->addOption('section_stat',"Course Section Status Type");
		$select->addOption('enroll_stat',"Enrollment Status Type");
		$select->addOption('grade',"Course Grading Type");
		$select->addOption('term',"Term Type");
		//$select->setValue('');
		
		$typeProp =$step->addComponent("typetype", $select);
		
		//$typeProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
		//$typeProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		
		
		
		// Create the title
		$titleProp =$step->addComponent("keyword", new WTextField());
		$titleProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
		$titleProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		
		// Create the description
		$descriptionProp =$step->addComponent("description", WTextArea::withRowsAndColumns(10,30));
		
				
		// Create the step text
		ob_start();
		print "\n<font size=+2><h2>"._("Type creator")."</h2></font>";
		
		print "\n<h2>"._("Type")."</h2>";
		print "\n"._("Please choose a type of <em>type</em> to create: ");
		print "\n<br />[[typetype]]";
		
		print "\n<h2>"._("Keyword")."</h2>";
		print "\n"._("The keyword of the <em>type</em>: ");
		print "\n<br />[[keyword]]";
		
		print "\n<h2>"._("Description")."</h2>";
		print "\n"._("The optional description of the <em>type</em>: ");
		print "\n<br />[[description]]";
		
		print "\n<div style='width: 400px'> &nbsp; </div>";
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
		$wizard =$this->getWizard($cacheName);
		
		// Make sure we have a valid Repository
		$courseManager = Services::getService("CourseManagement");
		$idManager = Services::getService("Id");
		$courseManagementId =$idManager->getId("edu.middlebury.coursemanagement");

		
		// First, verify that we chose a parent that we can add children to.
		$authZ = Services::getService("AuthZ");
		if ($authZ->isUserAuthorized(
						$idManager->getId("edu.middlebury.authorization.add_children"), 
						$courseManagementId))
		{
			
			$values = $wizard->getAllValues();
			printpre($values);
			
			$type = new Type("CourseManagement", "edu.middlebury",$values['namedescstep']['keyword'], 	
																	   $values['namedescstep']['description']);
			$courseManager->_typeToIndex($values['namedescstep']['typetype'], $type);
			RequestContext::sendTo($this->getReturnUrl());
			exit();
			return TRUE;
		} 
		// If we don't have authorization to add to the picked parent, send us back to
		// that step.
		else {
			return FALSE;
		}
	}
	
	/**
	 * Return the URL that this action should return to when completed.
	 * 
	 * @return string
	 * @access public
	 * @since 4/28/05
	 */
	function getReturnUrl () {
		$harmoni = Harmoni::instance();
		$url =$harmoni->request->mkURL("admin", "main");
		return $url->write();
	}
}

?>