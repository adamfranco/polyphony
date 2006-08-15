<?php
/**
 * @package concerto.modules.asset
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: addstudent.act.php,v 1.3 2006/08/14 18:59:26 jwlee100 Exp $
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
 * @version $Id: addstudent.act.php,v 1.3 2006/08/14 18:59:26 jwlee100 Exp $
 */
class addstudentAction
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
		return _("Create a course section.");
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
		$cacheName = "createCourseSectionWizard";
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
		$idManager =& Services::getService("Id");
		$am =& Services::GetService("AgentManager");
		$canonicalCourseIterator =& $courseManager->getCanonicalCourses();
		
		// Instantiate the wizard, then add our steps.
		$wizard =& SimpleStepWizard::withDefaultLayout();
		
		// :: Name and Description ::
		$step =& $wizard->addStep("namedescstep", new WizardStep());
		$step->setDisplayName(_("Please enter the information about a course offering:"));
		
		// Text box for location
		$lastNameProp =& $step->addComponent("lastname", new WTextField());
		$lastNameProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
		$lastNameProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		
		$firstNameProp =& $step->addComponent("firstname", new WTextField());
		$firstNameProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
		$firstNameProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		
		$enrollmentStatusProp =& $step->addComponent("status", new WTextField());
		$enrollmentStatusProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
		$enrollmentStatusProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));		
		
		// Create the step text
		ob_start();
		print "\n<font size=+2><h2>"._("Course Section")."</h2></font>";
		print "\n<h2>"._("Last name")."</h2>";
		print "\n"._("Please enter the last name of the student</em>: ");
		print "\n<br />[[lastname]]";
		print "\n<h2>"._("First name")."</h2>";
		print "\n"._("\Please enter the first name of the student</em>: ");
		print "\n<br />[[firstname]]";
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
		
		// First, verify that we chose a parent that we can add children to.
		$authZ =& Services::getService("AuthZ");
		if ($authZ->isUserAuthorized(
						$idManager->getId("edu.middlebury.authorization.add_children"), 
						$courseManagementId))
		{
			
			$values = $wizard->getAllValues();
			printpre($values);
			
			$lastName = $values['namedescstep']['lastname'];
			$firstName = $values['namedescstep']['firstname'];
			$student = $lastName.$firstName;
			
			$status = $values['namedescstep']['status'];
          	
          	$courseIdString = RequestContext::value("courseId");
			$courseId = $idManager->getId($courseIdString);
			$courseSection = $cmm->getCourseSection($courseId);
		
			$agentType =& new Type("CourseManagement", "edu.middlebury", $student);
			$propertiesType =& new Type("CourseManagement", "edu.middlebury", "properties");
			$properties =& new HarmoniProperties($propertiesType);
			$agent =& $am->createAgent("Gladius", $agentType, $properties);
			$enrollStatType =& new Type("CourseManagement", "edu.middlebury", $status, "");
	 
			$courseSection->addStudent($agent, $enrollStatType);												   
			
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
		$harmoni =& Harmoni::instance();
		$url =& $harmoni->request->mkURL("admin", "main");
		return $url->write();
	}
}

?>