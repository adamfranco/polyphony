<?php
/**
 * @package concerto.modules.asset
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: searchcanonicalcourse.act.php,v 1.4 2006/07/18 20:24:59 jwlee100 Exp $
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
 * @version $Id: searchcanonicalcourse.act.php,v 1.4 2006/07/18 20:24:59 jwlee100 Exp $
 */
class searchcanonicalcourseAction
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
		return _("Search a canonical course.");
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
		
		
		$harmoni =& Harmoni::instance();
		$courseManager =& Services::getService("CourseManagement");
		$canonicalCourseIterator =& $courseManager->getCanonicalCourses();
		
		// Instantiate the wizard, then add our steps.
		$wizard =& SimpleStepWizard::withDefaultLayout();
		
		ob_start();
		// :: Name and Description ::
		$step =& $wizard->addStep("namedescstep", new WizardStep());
		$step->setDisplayName(_("Please enter the information to search for a canonical course:"));
		
		// Create the properties.
		$titleProp =& $step->addComponent("title", new WTextField());
		$numberProp =& $step->addComponent("number", new WTextField());
		
		// Create the type chooser.
		$select =& new WSelectList();
		$typename = "can";	
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
		/*
		$typeProp =& $step->addComponent("type", new WTextField());
		$typeProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
		$typeProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		*/
		$select =& new WSelectList();
		$typename = "can_stat";	
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
		$typeProp =& $step->addComponent("statusType", $select);
		/*
		$statusTypeProp =& $step->addComponent("statusType", new WTextField());
		$statusTypeProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
		$statusTypeProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		*/
				
		// Create the step text
		ob_start();
		print "\n<font size=+2><h2>"._("Search for Canonical Course")."</h2></font>";
		print "\n<h2>"._("Title")."</h2>";
		print "\n"._("The title of this <em>canonical course</em>: ");
		print "\n<br />[[title]]";
		print "\n<h2>"._("Number")."</h2>";
		print "\n"._("The number of this <em>canonical course</em>: ");
		print "\n<br />[[number]]";
		print "\n<h2>"._("Type")."</h2>";
		print "\n"._("The type of this <em>canonical course</em>: ");
		print "\n<br />[[type]]";
		print "\n<h2>"._("Status type")."</h2>";
		print "\n"._("The status type of this <em>canonical course</em>: ");
		print "\n<br />[[statusType]]";
		print "\n<div style='width: 400px'> &nbsp; </div>";
		$step->setContent(ob_get_contents());
		$actionRows->add(new Block(ob_get_contents(), STANDARD_BLOCK), "100%", null, LEFT, CENTER);
		ob_end_clean();
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
	function search () {
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
			print "\n\t<td>";
			print "<b>Credits</b>";
			print "\n\t</tr>";
			$canonicalCourseIterator = $courseManager->getCanonicalCourses();
			while ($canonicalCourseIterator->hasNext()) {
				$canonicalCourse = $canonicalCourseIterator->next();
				$title = $canonicalCourse->getTitle();
	  			$number = $canonicalCourse->getNumber();
	  			$courseType = $canonicalCourse->getCourseType();
	  			$courseKeyword = $courseType->getKeyword();
	  			$courseStatusType = $canonicalCourse->getStatus();
	  			$courseStatusKeyword = $courseStatusType->getKeyword();
				if ($values['namedescstep']['title'] == $title ||
					$values['namedescstep']['number'] == $number ||
					$values['namedescstep']['type'] == $courseKeyword ||
					$values['namedescstep']['statusType'] == $courseStatusKeyword) {
					$description = $canonicalCourse->getDescription();
					$credits = $canonicalCourse->getCredits();
					
					print "<tr>";
					print "<td>";
					print $title;
					print "<td>";
					print $number;
					print "<td>";
					print $description;
					print "<td>";
					print $courseKeyword;
					print "<td>";
					print $courseStatusKeyword;
					print "<td>";
					print $credits;
					print "</tr>";
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