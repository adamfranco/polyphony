<?php
/**
 * @package concerto.modules.asset
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: createcourseoffering.act.php,v 1.4 2006/07/06 19:53:12 jwlee100 Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(HARMONI."/utilities/StatusStars.class.php");

/**
 * 
 * 
 * @package concerto.modules.asset
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: createcourseoffering.act.php,v 1.4 2006/07/06 19:53:12 jwlee100 Exp $
 */
class createcourseofferingAction
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
		// Check that the user can create an asset here.
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		
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
		return _("You are not authorized to create a SlideShow in this <em>Exhibition</em>.");
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return _("Create a course offering.");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$harmoni =& Harmoni::instance();
		$actionRows =& $this->getActionRows();
		$cacheName = "createCourseOfferingWizard";
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
		$courseManager =& Services::getService("CourseManagement");
		$canonicalCourseIterator =& $courseManager->getCanonicalCourses();
		
		// Instantiate the wizard, then add our steps.
		$wizard =& SimpleStepWizard::withDefaultLayout();
		
		// :: Name and Description ::
		$step =& $wizard->addStep("namedescstep", new WizardStep());
		$step->setDisplayName(_("Please enter the information about a course offering:"));
		
		// Create the properties.
		$titleProp =& $step->addComponent("title", new WTextField());
		$titleProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
		$titleProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		
		// Create the properties.		
		$numberProp =& $step->addComponent("number", new WTextField());
		$numberProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
		$numberProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		
		$descriptionProp =& $step->addComponent("description", WTextArea::withRowsAndColumns(10,30));
		
		$termProp =& $step->addComponent("term", new WTextField());
		$termProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
		$termProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		
		$typeProp =& $step->addComponent("type", new WTextField());
		$typeProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
		$typeProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		
		$statusTypeProp =& $step->addComponent("statusType", new WTextField());
		$statusTypeProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
		$statusTypeProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		
		$creditsProp =& $step->addComponent("credits", new WTextField());
		$creditsProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
		$creditsProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		
		$courseGradeProp =& $step->addComponent("courseGrade", new WTextField());
		$courseGradeProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
		$courseGradeProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));
				
		// Create the step text
		ob_start();
		print "\n<font size=+2><h2>"._("Course Offering")."</h2></font>";
		print "\n<h2>"._("Title")."</h2>";
		print "\n"._("The title of this <em>course offering</em>: ");
		print "\n<br />[[title]]";
		print "\n<h2>"._("Number")."</h2>";
		print "\n"._("The number of this <em>course offering</em>: ");
		print "\n<br />[[number]]";
		print "\n<h2>"._("Description")."</h2>";
		print "\n"._("The description of this <em>course offering</em>: ");
		print "\n<br />[[description]]";
		print "\n<h2>"._("Term")."</h2>";
		print "\n"._("The term of this <em>course offering</em>: ");
		print "\n<br />[[term]]";
		print "\n<h2>"._("Type")."</h2>";
		print "\n"._("The type of this <em>course offering</em>: ");
		print "\n<br />[[type]]";
		print "\n<h2>"._("Status type")."</h2>";
		print "\n"._("The status type of this <em>course offering</em>: ");
		print "\n<br />[[statusType]]";
		print "\n<h2>"._("Credits")."</h2>";
		print "\n"._("The status type of this <em>course offering</em>: ");
		print "\n<br />[[credits]]";
		print "\n<h2>"._("Course Grading Type")."</h2>";
		print "\n"._("The course grading type of this <em>course offering</em>: ");
		print "\n<br />[[courseGrade]]";
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
		$wizard =& $this->getWizard($cacheName);
		
		// Make sure we have a valid Repository
		$courseManager =& Services::getService("CourseManagement");
		$idManager =& Services::getService("Id");
		$courseManagementId =& $idManager->getId("edu.middlebury.coursemanagement");

		
		// First, verify that we chose a parent that we can add children to.
		$authZ =& Services::getService("AuthZ");
		if ($authZ->isUserAuthorized(
						$idManager->getId("edu.middlebury.authorization.add_children"), 
						$courseManagementId))
		{
			
			$values = $wizard->getAllValues();
			printpre($values);
			
			$termType =& new Type("CourseManagement", "edu.middlebury", $values['namedescstep']['term']);
          	$term =& $courseManagementManager->createTerm($termType, $schedule);
          	$termId =& $term->getId();
			$courseType =& new Type("CourseManagement", "edu.middlebury", $values['namedescstep']['type']);
			$statusType =& new Type("CourseManagement", "edu.middlebury", $values['namedescstep']['statusType']);
			$courseGradeType = new Type("CourseManagement", "edu.middlebury", $values['namedescstep']['courseGrade']);
			$canonicalCourse =& $courseManager->createCanonicalCourse($values['namedescstep']['title'], 
																	   $values['namedescstep']['number'], 	
																	   $values['namedescstep']['description'], 
																	   $courseType, $statusType, 
																	   $values['namedescstep']['credits']);
			$courseOffering =& $canonicalCourse->createCourseOffering($values['namedescstep']['title'], 
																	    $values['namedescstep']['number'], 	
																	    $values['namedescstep']['description'], 
																	    $termId, $courseType, $statusType, 
																	    $courseGradeType);
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
		$harmoni =& Harmoni::instance();
		$url =& $harmoni->request->mkURL("admin", "main");
		return $url->write();
	}
}

?>