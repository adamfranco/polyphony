<?php
/**
 * @package concerto.modules.asset
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: searchcoursesection.act.php,v 1.1 2006/07/13 20:02:56 jwlee100 Exp $
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
 * @version $Id: searchcoursesection.act.php,v 1.1 2006/07/13 20:02:56 jwlee100 Exp $
 */
class searchcoursesectionAction
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
		$canonicalCourseIterator =& $courseManager->getCanonicalCourses();
		
		// Instantiate the wizard, then add our steps.
		$wizard =& SimpleStepWizard::withDefaultLayout();
		
		// :: Name and Description ::
		$step =& $wizard->addStep("namedescstep", new WizardStep());
		$step->setDisplayName(_("Please enter the information about a course offering:"));
		
		$titleProp =& $step->addComponent("title", new WTextField());
		
		$numberProp =& $step->addComponent("number", new WTextField());					
				
		$select =& new WSelectList();
		$typename = "section";	
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
		
		
		
		$select =& new WSelectList();
		$typename = "section_stat";	
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
		
		// Text box for location
		$locationProp =& $step->addComponent("location", new WTextField());
		
		
		
		// Create the step text
		ob_start();
		print "\n<font size=+2><h2>"._("Course Section")."</h2></font>";
		print "\n<h2>"._("Title")."</h2>";
		print "\n"._("The title of this <em>course section</em>: ");
		print "\n<br />[[title]]";
		print "\n<h2>"._("Number")."</h2>";
		print "\n"._("The number of this <em>course section</em>: ");
		print "\n<br />[[number]]";
		//print "\n<h2>"._("Description")."</h2>";
		//print "\n"._("The description of this <em>course section</em>: ");
		//print "\n<br />[[description]]";
		print "\n<h2>"._("Section Type")."</h2>";
		print "\n"._("The type of this <em>course section</em>: ");
		print "\n<br />[[type]]";
		print "\n<h2>"._("Status type")."</h2>";
		print "\n"._("The status type of this <em>course sectiong</em>: ");
		print "\n<br />[[statusType]]";
		print "\n<h2>"._("Location")."</h2>";
		print "\n"._("The location of this <em>course section</em>: ");
		print "\n<br />[[location]]";
		print "\n<div style='width: 400px'> &nbsp; </div>";
		$step->setContent(ob_get_contents());
		ob_end_clean();
		
		ob_start();
		$courseManagementManager =& Services::getService("CourseManagement");
		$canonicalCourseIterator =& $courseManagementManager->getCanonicalCourses();
		
		print "\n<table border=1>";
		print "\n\t<tr>";
		print "\n\t<td>";
		print "Title: ";
		print "\n\t<td>";
		print "Number: ";
		print "\n\t<td>";
		print "Description: ";
		print "\n\t<td>";
		print "Section type: ";
		print "\n\t<td>";
		print "Section status: ";
		print "\n\t<td>";
		print "</tr>";
		
		while ($canonicalCourseIterator->hasNext()) {
		  	$canonicalCourse =& $canonicalCourseIterator->next();
		  	$title = $canonicalCourse->getTitle();
	  		$number = $canonicalCourse->getNumber();
	  		$description = $canonicalCourse->getDescription();
	  		$courseType = $canonicalCourse->getCourseType();
	  		$courseKeyword = $courseType->getKeyword();
	  		$courseStatusType = $canonicalCourse->getStatus();
	  		$courseStatusKeyword = $courseStatusType->getKeyword();
	  		$credits = $canonicalCourse->getCredits();
	  		
	  		$courseOfferingIterator =& $canonicalCourse->getCourseOfferings();
			while ($courseOfferingIterator->hasNext()) {
				$courseOffering =& $courseOfferingIterator->next();
				$title = $courseOffering->getTitle();
	  			$number = $courseOffering->getNumber();
	  			$description = $courseOffering->getDescription();
	  			$offeringType = $courseOffering->getOfferingType();
	  			$offeringKeyword = $offeringType->getKeyword();
	  			$offeringStatusType = $courseOffering->getStatus();
	  			$offeringStatusKeyword = $offeringStatusType->getKeyword();

				$courseSectionIterator =& $courseOffering->getCourseSections();
				while ($courseSectionIterator->hasNext()) {
					$courseSection =& $courseSectionIterator->next();
					$title = $courseSection->getTitle();
	  				$number = $courseSection->getNumber();
	  				$description = $courseSection->getDescription();
	  				$sectionType = $courseSection->getSectionType();
	  				$sectionKeyword = $sectionType->getKeyword();
	  				$sectionStatusType = $courseSection->getStatus();
	  				$sectionStatusKeyword = $sectionStatusType->getKeyword();
	  				$sectionLocation = $courseSection->getLocation();
	  			
	  				print "\n\t<tr>";
					print "\n\t<td>";
					print "Title: ";
					print $title;
					print "\n\t<td>";
					print "Number: ";
					print $number;
					print "\n\t<td>";
					print "Description: ";
					print $description;
					print "\n\t<td>";
					print "Course offering type: ";
					print $sectionKeyword;
					print "\n\t<td>";
					print "Course offering status: ";
					print $sectionStatusKeyword;
					print "\n\t<td>";
					print "Course offering grade: ";
					print $sectionLocation;
					print "</tr>";
				}
			}
		}
		print "</table>";
		
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
			print "<b>Section Type</b>";
			print "\n\t<td>";
			print "<b>Section Status</b>";
			print "\n\t<td>";
			print "<b>Location</b>";
			print "\n\t</tr>";
			$canonicalCourseIterator = $courseManager->getCanonicalCourses();
			while ($canonicalCourseIterator->hasNext()) {
				$canonicalCourse = $canonicalCourseIterator->next();
				$courseOfferingIterator = $canonicalCourse->getCourseOfferings();
				while ($courseOfferingIterator->hasNext()) {
					$courseOffering = $courseOfferingIterator->next();
					$courseSectionIterator = $courseOffering->getCourseSections();
					while ($courseSectionIterator->hasNext()) {
					  	$courseSection = $courseSectionIterator->next();
						$title = $courseSection->getTitle();
	  					$number = $courseSection->getNumber();
	  					$sectionType = $courseSection->getSectionType();
	  					$sectionKeyword = $sectionType->getKeyword();
	  					$sectionStatusType = $courseSection->getStatus();
	  					$sectionStatusKeyword = $sectionStatusType->getKeyword();
	  					$location = $courseSection->getLocation();
					

						if ($values['namedescstep']['title'] == $title ||
							$values['namedescstep']['number'] == $number ||
							$values['namedescstep']['type'] == $sectionKeyword ||
							$values['namedescstep']['statusType'] == $sectionStatusKeyword ||
							$values['namedescstep']['location'] == $sectionLocation) {
							$description = $canonicalCourse->getDescription();
					
							print "<tr>";
							print "<td>";
							print $title;
							print "<td>";
							print $number;
							print "<td>";
							print $description;
							print "<td>";
							print $sectionKeyword;
							print "<td>";
							print $sectionStatusKeyword;
							print "<td>";
							print $location;
							print "</tr>";
						}
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