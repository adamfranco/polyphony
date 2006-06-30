<?php
/**
 * @package concerto.modules.asset
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: createcanonicalcourse.act.php,v 1.1 2006/06/30 15:37:54 jwlee100 Exp $
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
 * @version $Id: createcanonicalcourse.act.php,v 1.1 2006/06/30 15:37:54 jwlee100 Exp $
 */
class createcanonicalcourseAction
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
			$idManager->getId("edu.middlebury.concerto.coursemanagement")
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
		$idManager =& Services::getService("Id");
		$repositoryManager =& Services::getService("Repository");
		$repository =& $repositoryManager->getRepository(
				$idManager->getId(
					"edu.middlebury.concerto.exhibition_repository"));
		$asset =& $repository->getAsset(
				$idManager->getId(RequestContext::value('exhibition_id')));
		return _("Add a SlideShow to the")." <em>".$asset->getDisplayName()."</em> "._("Exhibition");
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
		$harmoni->request->passthrough("exhibition_id");
		
		$actionRows =& $this->getActionRows();
		
		$idManager =& Services::getService("Id");
		$exhibitionAssetId =& $idManager->getId(RequestContext::value('exhibition_id'));
		
		$cacheName = 'add_slideshow_wizard_'.$exhibitionAssetId->getIdString();
		
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
		$courseManagemer =& Services::getService("CourseManagement");
		$canonicalCourseIterator =& $courseManager->getCanonicalCourses();
		
		// Instantiate the wizard, then add our steps.
		$wizard =& SimpleStepWizard::withDefaultLayout();
		
		// :: Name and Description ::
		$step =& $wizard->addStep("namedescstep", new WizardStep());
		$step->setDisplayName(_("Please enter the information about a canonical course:"));
		
		// Create the properties.
		$titleProp =& $step->addComponent("title", new WTextField());
		$titleProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
		$titleProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		
		// Create the properties.		
		$numberProp =& $step->addComponent("number", new WTextField());
		$numberProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
		$numberProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		
		$descriptionProp =& $step->addComponent("description", WTextArea::withRowsAndColumns(10,30));
		
		$typeProp = $canonicalCourse->addComponent("type", new WTextField());
		$numberProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
		$numberProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		
		$statusTypeProp = $canonicalCourse->addComponent("statusType", new WTextField());
		$numberProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
		$numberProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		
		$statusTypeProp = $canonicalCourse->addComponent("credits", new WTextField());
		$numberProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
		$numberProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));
				
		// Create the step text
		ob_start();
		print "\n<font size=+2><h2>"._("Canonical Course")."</h2></font>";
		print "\n<h2>"._("Title")."</h2>";
		print "\n"._("The title of this <em>canonical course</em>: ");
		print "\n<br />[[title]]";
		print "\n<h2>"._("Number")."</h2>";
		print "\n"._("The number of this <em>canonical course</em>: ");
		print "\n<br />[[number]]";
		print "\n<h2>"._("Description")."</h2>";
		print "\n"._("The number of this <em>canonical course</em>: ");
		print "\n<br />[[description]]";
		print "\n<h2>"._("Type")."</h2>";
		print "\n"._("The type of this <em>canonical course</em>: ");
		print "\n<br />[[Type]]";
		print "\n<h2>"._("Status type")."</h2>";
		print "\n"._("The status type of this <em>canonical course</em>: ");
		print "\n<br />[[statusType]]";
		print "\n<div style='width: 400px'> &nbsp; </div>";
		$step->setContent(ob_get_contents());
		ob_end_clean();
		
		return $wizard;
	}
	
	function browse_canonicalcourse() {
	  	$harmoni =& Harmoni::instance();
		$courseManagementManager =& Services::getService("CourseManagement");
		$canonicalCourseIterator =& $courseManagementManager->getCanonicalCourses();
		
		// Instantiate the wizard, then add our steps.
		$wizard =& SimpleStepWizard::withDefaultLayout();
		
		print "\n<table>";
		while ($canonicalCourseIterator->hasNext()) {
		  	$canonicalCourse =& $canonicalCourseIterator->next();
		  	$id =& $canonicalCourse->getId();
		  	print "\n\t<tr>";
		  	attributesPrinter($canonicalCourse, $id);
			print "</tr>";			
		}
		print "</table>";		
			
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
		$courseManagementID =& $idManager->getId("edu.middlebury.concerto.coursemanagement");

		
		// First, verify that we chose a parent that we can add children to.
		$authZ =& Services::getService("AuthZ");
		if ($authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.add_children"), 
				$courseManagementID))
		{
			
			$values = $wizard->getAllValues();	
			$type = new Type("title", "number", $values['type']);
			$statusType = new Type("title", "number", $values['statusType']);
			$canonicalCourseA = $courseManagement->createCanonicalCourse($values['title'], $values['number'], 	
																		$values['description'], $type, 
																		$statusType, $values['credits']);
			printpre($values);
			browse_canonicalcourse();
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
		$url =& $harmoni->request->mkURL("coursemanagementmanager");
		return $url->write();
	}
	
	function attributesPrinter(&$canonicalCourse, $id) {
	  	$title = $canonicalCourse->getTitle($id);
	  	$number = $canonicalCourse->getNumber($id);
	  	$description = $canonicalCourse->getNumber($id);
	  	$type = $canonicalCourse->getType();
	  	$statusType = $canonicalCourse->getCourseStatusType();
	  	
		print "\n\t<td>";
		print "Title: ";
		print $title;
		print "\n\t<td>";
		print "\n\t<td>";
		print "Number: ";
		print $number;
		print "\n\t<td>";
		print "Description: ";
		print $description;
		print "\n\t<td>";
		print "Type: ";
		print $type;
		print "\n\t<td>";
		print "Status Type: ";
		print $statusType;
	}
}

?>