<?php
/**
 * @package concerto.modules.asset
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: searchcourseoffering.act.php,v 1.4 2006/07/11 19:31:53 jwlee100 Exp $
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
 * @version $Id: searchcourseoffering.act.php,v 1.4 2006/07/11 19:31:53 jwlee100 Exp $
 */
class searchcourseofferingAction
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
		return _("You are not authorized to create a <em>Course Offering</em>.");
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
		
		
		/*$select =& new WSelectList();
	
		$dbHandler =& Services::getService("DBHandler");
		$query=& new SelectQuery;
		$query->addTable('cm_can');
		$query->addColumn('id');
		$query->addColumn('title');
		$res=& $dbHandler->query($query);
		while($res->hasMoreRows()){
			$row = $res->getCurrentRow();
			$res->advanceRow();
			$select->addOption($row['id'],$row['title']);
		}
		$canonicalCourseProp =& $step->addComponent("can", $select);
		*/
		/*
		// Create the properties.
		$titleProp =& $step->addComponent("title", new WTextField());
		$titleProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
		$titleProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		
		// Create the properties.		
		$numberProp =& $step->addComponent("number", new WTextField());
		$numberProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
		$numberProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		*/
		
		$titleProp =& $step->addComponent("title", new WTextField());
		
		$numberProp =& $step->addComponent("number", new WTextField());		
	
		$select =& new WSelectList();
	
		$dbHandler =& Services::getService("DBHandler");
		$query=& new SelectQuery;
		$query->addTable('cm_term');
		$query->addColumn('id');
		$query->addColumn('name');
		$res=& $dbHandler->query($query);
		while($res->hasMoreRows()){
			$row = $res->getCurrentRow();
			$res->advanceRow();
			$select->addOption($row['id'],$row['name']);
		}
		$termProp =& $step->addComponent("term", $select);
		/*$termProp =& $step->addComponent("term", new WTextField());
		$termProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
		$termProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));*/
		
		
		$select =& new WSelectList();
		$typename = "offer";	
		$dbHandler =& Services::getService("DBHandler");
		$query=& new SelectQuery;
		$query->addTable('cm_'.$typename."_type");
		$query->addColumn('id');
		$query->addColumn('keyword');
		$res=& $dbHandler->query($query);
		while($res->hasMoreRows()){
			$row = $res->getCurrentRow();
			$res->advanceRow();
			$select->addOption($row['id'],$row['keyword']);
		}
		$typeProp =& $step->addComponent("type", $select);
		/*$typeProp =& $step->addComponent("type", new WTextField());
		$typeProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
		$typeProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));*/
		
		
		
		$select =& new WSelectList();
		$typename = "offer_stat";	
		$dbHandler =& Services::getService("DBHandler");
		$query=& new SelectQuery;
		$query->addTable('cm_'.$typename."_type");
		$query->addColumn('id');
		$query->addColumn('keyword');
		$res=& $dbHandler->query($query);
		while($res->hasMoreRows()){
			$row = $res->getCurrentRow();
			$res->advanceRow();
			$select->addOption($row['id'],$row['keyword']);
		}
		$statusTypeProp =& $step->addComponent("statusType", $select);
		/*
		$statusTypeProp =& $step->addComponent("statusType", new WTextField());
		$statusTypeProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
		$statusTypeProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		*/
	
		
		
		$select =& new WSelectList();
		$typename = "grade";	
		$dbHandler =& Services::getService("DBHandler");
		$query=& new SelectQuery;
		$query->addTable('cm_'.$typename."_type");
		$query->addColumn('id');
		$query->addColumn('keyword');
		$res=& $dbHandler->query($query);
		while($res->hasMoreRows()){
			$row = $res->getCurrentRow();
			$res->advanceRow();
			$select->addOption($row['id'],$row['keyword']);
		}
		$courseGradeProp =& $step->addComponent("courseGrade", $select);
		/*
		$courseGradeProp =& $step->addComponent("courseGrade", new WTextField());
		$courseGradeProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
		$courseGradeProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		*/
		// Create the step text
		ob_start();
		print "\n<h2>"._("Title")."</h2>";
		print "\n"._("The title of the <em>course offering</em>: ");
		print "\n<br />[[title]]";
		print "\n<h2>"._("Number")."</h2>";
		print "\n"._("The number of the <em>course offering</em>: ");
		print "\n<br />[[number]]";
		print "\n<h2>"._("Term")."</h2>";
		print "\n"._("The term of this <em>course offering</em>: ");
		print "\n<br />[[term]]";
		print "\n<h2>"._("Type")."</h2>";
		print "\n"._("The type of this <em>course offering</em>: ");
		print "\n<br />[[type]]";
		print "\n<h2>"._("Status type")."</h2>";
		print "\n"._("The status type of this <em>course offering</em>: ");
		print "\n<br />[[statusType]]";
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
						
			$courseType =& $courseManager->_indexToType($values['namedescstep']['type'],'can');
			$statusType =& $courseManager->_indexToType($values['namedescstep']['statusType'],'can_stat');
			
			print "\n<table border=1>";
			print "\n\t<tr>";
			print "\n\t<td>";
			print "<b>Title</b>";
			print "\n\t<td>";
			print "<b>Number</b>";
			print "\n\t<td>";
			print "<b>Description</b>";
			print "\n\t<td>";
			print "<b>Course Type</b>";
			print "\n\t<td>";
			print "<b>Course Status</b>";
			print "\n\t</tr>";
			$canonicalCourseIterator = $courseManager->getCanonicalCourses();
			while ($canonicalCourseIterator->hasNext()) {
				$canonicalCourse = $canonicalCourseIterator->next();
				$courseOfferingIterator = $canonicalCourse->getCourseOfferings();
				while ($courseOfferingIterator->hasNext()) {
					$courseOffering = $courseOfferingIterator->next();
					$title = $courseOffering->getTitle();
	  				$number = $courseOffering->getNumber();
	  				$term = $courseOffering->getTerm();
	  				$offeringType = $courseOffering->getOfferingType();
	  				$offeringKeyword = $offeringType->getKeyword();
	  				$offeringStatusType = $courseOffering->getStatus();
	  				$offeringStatusKeyword = $offeringStatusType->getKeyword();
	  				$courseGradeType = $courseOffering->getCourseGradeType();
	  				$courseGradeKeyword = $courseGradeType->getKeyword();

					if ($values['namedescstep']['title'] == $title ||
						$values['namedescstep']['number'] == $number ||
						$values['namedescstep']['term'] == $term ||
						$values['namedescstep']['type'] == $offeringKeyword ||
						$values['namedescstep']['statusType'] == $offeringStatusKeyword ||
						$values['namedescstep']['courseGrade']) {
						$description = $canonicalCourse->getDescription();
					
						print "<tr>";
						print "<td>";
						print $title;
						print "<td>";
						print $number;
						print "<td>";
						print $description;
						print "<td>";
						print $offeringKeyword;
						print "<td>";
						print $offeringStatusKeyword;
						print "</tr>";
					}
				}
			}
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