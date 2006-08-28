<?php

/**
* This action is the central page for viewing and modifying course section information.
*
* @package polyphony.modules.coursemanagement
*
*
* @since 7/28/06
*
* @copyright Copyright &copy; 2006, Middlebury College
* @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
*
* @version $Id: createcourse.act.php,v 1.7 2006/08/28 20:28:47 jwlee100 Exp $
*/

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(HARMONI."GUIManager/Components/Blank.class.php");

class createcourseAction
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
		// Check for authorization
		$authZManager =& Services::getService("AuthZ");
		$idManager =& Services::getService("IdManager");
		$harmoni =& Harmoni::instance();

		$harmoni->request->startNamespace("polyphony-agents");
		$sectionIdString = $harmoni->request->get("courseId");

		$harmoni->request->endNamespace();

		if ($authZManager->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"),
			$idManager->getId($sectionIdString)))
		{
			return TRUE;
		} else
		return FALSE;
	}

	/**
	* Return the heading text for this action, or an empty string.
	*
	* @return string
	* @access public
	* @since 4/26/05
	*/
	function getHeadingText () {
		$harmoni =& Harmoni::instance();
		$harmoni->request->startNamespace("polyphony-agents");
		$harmoni->request->passthrough("courseId");
		$sectionIdString = $harmoni->request->get("courseId");
		$idManager =& Services::getService("Id");
		$sectionId =& $idManager->getId($sectionIdString);
		$cm =& Services::getService("CourseManagement");
		$section =& $cm->getCourseSection($sectionId);
		return dgettext("polyphony", $section->getDisplayName());
	}

	/**
	* Build the content for this action
	*
	* @return void
	* @access public
	* @since 4/26/05
	*/
	function buildContent () {
		$defaultTextDomain = textdomain("polyphony");
		
		$idManager =& Services::getService("Id");
		$harmoni =& Harmoni::instance();

		$harmoni->request->startNamespace("polyphony-agents");
		$harmoni->request->passthrough("courseId");
		
		$cm =& Services::getService("CourseManagement");
				
		// Process any changes
		if (RequestContext::value("courseTitle") && RequestContext::value("courseNumber") &&
			RequestContext::value("courseType") && RequestContext::value("courseStatus") &&
			RequestContext::value("courseTerm") && RequestContext::value("courseCredits") &&
			RequestContext::value("courseLocation"))
			$this->addCourse(RequestContext::value("courseTitle"), RequestContext::value("courseNumber"),
							 RequestContext::value("courseDescription") && RequestContext::value("courseType"), 				
							 RequestContext::value("courseStatus"), RequestContext::value("courseTerm"), 
							 RequestContext::value("courseCredits"), RequestContext::value("courseLocation"));
		
		if (RequestContext::value("courseIdToRemove") && RequestContext::value("canonicalCourseId"))
			$this->removeCourse(RequestContext::value("courseIdToRemove"), RequestContext::value("canonicalCourseId"));
		
		
		// Print out our search and memebers
		$actionRows =& $this->getActionRows();
		$sectionName = $section->getDisplayName();
		
		$actionRows->add(new Heading(_("Add or remove courses."), 2), "100%", null, LEFT, CENTER);
		
		$actionRows->add($this->getAddForm($section), "100%", null, LEFT, CENTER);
		$actionRows->add($this->getCourses($section), "100%", null, LEFT, CENTER);
		
		$harmoni->request->endNamespace();

		textdomain($defaultTextDomain);
	}

	/***************************FUNCTIONS***************************************/

	/*********************************************************
	* The form to add information for adding courses
    *********************************************************/
		

	function &getAddForm(&$section) {
		$harmoni =& Harmoni::instance();
		
		$cmm =& Services::getService("CourseManagement");
		$idManager =& Services::getService("Id");
		$am =& Services::GetService("AgentManager");

		$offering =& $section->getCourseOffering();
		$offeringId = $offering->getId();
		$courseIdString = $offeringId->getIdString();
		
		ob_start();
		$link1 = $harmoni->request->quickURL("coursemanagement", "course_search");
		
		print "<p><a href='$link1'>Click here to search for existing courses and add sections.</a></p>";
		
		print _("<p>Please enter the following information about .</p>")."";
		
		// Search header
		$self = $harmoni->request->quickURL("coursemanagement", "createcourse", 
			array("courseTitle", "courseNumber", "courseDescription", "courseType", "courseStatus", "courseTerm"));
		
		/*
		if (RequestContext::value("courseTitle") && RequestContext::value("courseNumber") &&
			RequestContext::value("courseType") && RequestContext::value("courseStatus") &&
			RequestContext::value("courseTerm"))
			$this->addCourse(RequestContext::value("courseTitle"), RequestContext::value("courseNumber"),
							 RequestContext::value("courseDescription") && RequestContext::value("courseType"), 				
							 RequestContext::value("courseStatus"), RequestContext::value("courseTerm"));
		*/
		
		$last_title = $harmoni->request->get("courseTitle");
		$course_title = RequestContext::name("courseTitle");
		$last_number = $harmoni->request->get("courseNumber");
		$course_number = RequestContext::name("courseNumber");
		$last_description = $harmoni->request->get("courseDescription");
		$course_description = RequestContext::name("courseDescription");
		$last_type = $harmoni->request->get("courseType");
		$course_type = RequestContext::name("courseType");
		$last_status = $harmoni->request->get("courseStatus");
		$course_status = RequestContext::name("courseStatus");
		$last_term = $harmoni->request->get("courseTerm");
		$course_term = RequestContext::name("courseTerm");
		$last_credits = $harmoni->request->get("courseCredits");
		$course_credits = RequestContext::name("courseCredits");
		$last_credits = $harmoni->request->get("courseLocation");
		$course_credits = RequestContext::name("courseLocation");
		
		if (is_null($course_title)) 
			$course_title = "";
		
		print "<form action='$self' method='post'>
			<div>
			<br />Course Title: <input type='text' name='$course_title' value='$last_title' />	
			<br />Course Number: <input type='text' name='$course_number' value='$last_number' />
			<br />Course Description: <input type='text' name='$course_description' value='$last_description' />
			<br />Course Type: <input type='text' name='$course_type' value='$last_type' />
			<br />Course Status: <input type='text' name='$course_status' value='$last_status' />
			<br />Course Term: <input type='text' name='$course_term' value='$last_term' />
			<br />Course Credits: <input type='text' name='$course_credits' value='$last_credits' />";		
	
			print "\n\t<input type='submit' value='"._("Search")."' />";
			print "\n\t<a href='".$harmoni->request->quickURL()."'>";
			print "<input type='button' value='"._("Clear")."' /></a>";
		print "\n</div>\n</form>\n";
		}
		$output =& new Block(ob_get_clean(), STANDARD_BLOCK);
		return $output;
	}
	
	/***********************************************************************************
	* List of all courses																   *		
	************************************************************************************/
	function &getCourses(&$section) {
	  	$harmoni =& Harmoni::instance();
	  
	  	$cmm =& Services::getService("CourseManagement");
		$idManager =& Services::getService("Id");
		$am =& Services::GetService("AgentManager");
	  	
	  	ob_start();
		print "\n<h4>Members</h4>";
				
		print "<form action='$self' method='post'>";
		$canonicalCourseIterator =& $cmm->getCanonicalCourses();
		if (!$canonicalCourseIterator->hasNextCanonicalCourse()) {
			print "<p>No course offerings are present.</p>";
		} else {
			while ($canonicalCourseIterator->hasNextCanonicalCourse()) {
			  	$canonicalCourse =& $canonicalCourseIterator->$nextCanonicalCourse();
				$courseOfferingIterator =& $canonicalCourse->getCourseOfferings();
				while ($courseOfferingIterator->hasNextCourseOffering()) {
				  	$courseOffering =& $courseOfferingIterator->nextCourseOffering();
					$id =& $courseOffering->getId();
					$idString = $id->getIdString();
					$canonicalCourseId = $canonicalCourse->getId();
					$canonicalCourseIdString = $canonicalCourseId->getIdString();
				
					print $self = $harmoni->request->quickURL("coursemanagement", "createcourse", 
			   												  array("courseIdToRemove"=>$idString,
																 	"canonicalCourseId=>$canonicalCourseIdString");
			
					print "<form action='$self' method='post'>";
					print "\n<a href='".$harmoni->request->quickURL("agents", "edit_offering_details", 
																	array("courseId"=>$idString))."'>";
					print "\n".$courseOffering->getDisplayName()."</a>";
				
					print "\n\t<input type='submit' value='"._("Remove")."' />";
					print "\n</form>\n";
				}
			}
		}
		
		$output =& new Block(ob_get_clean(), STANDARD_BLOCK);
		return $output;
	}
	
	function addCourse($courseTitle, $courseNumber, $courseDescription, $type, $status,
					   $courseTerm, $credits, $location) {
					     
		$actionRows =& $this->getActionRows();
		$pageRows =& new Container(new YLayout(), OTHER, 1);
		$harmoni =& Harmoni::instance();
		
		$cmm =& Services::getService("CourseManagement");
		$idManager =& Services::getService("Id");
		$am =& Services::GetService("AgentManager");
		
		$agentId =& $idManager->getId($agentIdString);
		$agent =& $am->getAgent($agentId);
		
		$everyoneId =& $idManager->getId("edu.middlebury.agents.everyone");
		$usersId =& $idManager->getId("edu.middlebury.agents.users");
		
		$canonicalCourseIterator =& $cmm->getCanonicalCourses();
		$courseFound = 
		
		$courseType =& new Type("CourseManagement", "edu.middlebury", $type);
		$courseStatus =& new Type("CourseManagement", "edu.middlebury", $status);
		
		$canonicalCourse =& $cmm->createCanonicalCourse($courseTitle, $courseNumber, $courseDescription, 
														$courseType, $statusType, $courseCredits);
																	      											   
		
		$sectionType =& new Type("CourseManagement", "edu.middlebury", $type);
		$sectionStatus =& new Type("CourseManagement", "edu.middlebury", $status);
															   
		$courseOffering =& $canonicalCourse->createCourseOffering($canonicalCourse->getTitle(), 
																  $canonicalCourse->getNumber(),
																  $canonicalCourse->getDescription(),	
																  $sectionType, $sectionStatus, 
																  $location);
	}
	
	function removeCourse($courseOfferingIdString, $canonicalCourseIdString) {
		$harmoni =& Harmoni::instance();
		$harmoni->request->startNamespace("polyphony-agents");
		$harmoni->request->passthrough("agentId");
		
		$idManager =& Services::getService("Id");
		$cmm =& Services::getService("CourseManagement");
		
		$courseOfferingId =& $idManager->getId($courseOfferingIdString);
		$canonicalCourseId =& $idManager->getId($canonicalcourseIdString);
		
		// Delete course offering
		$canonicalCourse =& $cmm->getCanonicalCourse($canonicalCourseId);
		$canonicalCourse->removeCourseOffering($courseOfferingId);
		
		$courseOfferingIterator =& $canonicalCourse->getCourseOfferings();
		
		if (!$courseOfferingIterator->hasNextCourseOffering)
			$cmm->removeCanonicalCourse($canonicalCourseId);
	}
}