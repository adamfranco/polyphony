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
* @version $Id: createcourse.act.php,v 1.14 2006/08/30 17:53:51 jwlee100 Exp $
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
		// Check that the user can create an asset here.
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		
		return $authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.add_children"),
			$idManager->getId("edu.middlebury.coursemanagement")
		);
	}

	/**
	* Return the heading text for this action, or an empty string.
	*
	* @return string
	* @access public
	* @since 4/26/05
	*/
	function getHeadingText () {
		return _("Add or remove a course offering.");
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
				
		// Process any changes and add or remove courses as necessary
		if (RequestContext::value("courseTitle") && RequestContext::value("courseNumber") &&
			RequestContext::value("courseDescription") && RequestContext::value("courseType") && 
			RequestContext::value("courseStatus") && RequestContext::value("courseTerm") && 
			RequestContext::value("courseCredits") && RequestContext::value("courseGradeType"))
			$this->addCourse(RequestContext::value("courseTitle"), RequestContext::value("courseNumber"),
							 RequestContext::value("courseDescription"), RequestContext::value("courseType"), 				
							 RequestContext::value("courseStatus"), RequestContext::value("courseTerm"), 
							 RequestContext::value("courseCredits"), RequestContext::value("courseGradeType"));
		
		if (RequestContext::value("courseIdToRemove"))
			$this->removeCourse(RequestContext::value("courseIdToRemove"));
		
		
		// Print out the add form and course list
		$actionRows =& $this->getActionRows();
		
		$actionRows->add(new Heading(_("Add or remove courses."), 2), "100%", null, LEFT, CENTER);
		
		$actionRows->add($this->getAddForm(), "100%", null, LEFT, CENTER);
		$actionRows->add($this->getCourses(), "100%", null, LEFT, CENTER);
		
		$harmoni->request->endNamespace();

		textdomain($defaultTextDomain);
	}

	/***************************FUNCTIONS***************************************/

	/*********************************************************
	* The form to add information for adding courses
    *********************************************************/
		
	/**
	* Creates the form to add information about the course offering.
	*
	* @return void
	* @access public
	* @since 8/29/05
	*/
	function &getAddForm() {
		$harmoni =& Harmoni::instance();
		
		$cmm =& Services::getService("CourseManagement");
		$idManager =& Services::getService("Id");
		$am =& Services::GetService("AgentManager");
		
		ob_start();
			
		print _("<h4>Please enter the following information to add a course.</h4>")."";
		
		// Search header
		$self = $harmoni->request->quickURL("coursemanagement", "createcourse", 
			array("courseTitle", "courseNumber", "courseDescription", "courseType", "courseStatus", "courseTerm"));
		
		$last_title = $harmoni->request->get("courseTitle");
		$course_title = RequestContext::name("courseTitle");
		$last_number = $harmoni->request->get("courseNumber");
		$course_number = RequestContext::name("courseNumber");
		$last_description = $harmoni->request->get("courseDescription");
		$course_description = RequestContext::name("courseDescription");
		$last_term = $harmoni->request->get("courseTerm");
		$course_term = RequestContext::name("courseTerm");
		$last_type = $harmoni->request->get("courseType");
		$course_type = RequestContext::name("courseType");
		$last_status = $harmoni->request->get("courseStatus");
		$course_status = RequestContext::name("courseStatus");
		$last_term = $harmoni->request->get("courseTerm");
		$course_term = RequestContext::name("courseTerm");
		$last_credits = $harmoni->request->get("courseCredits");
		$course_credits = RequestContext::name("courseCredits");
		$last_grade_type = $harmoni->request->get("courseGradeType");
		$course_grade_type = RequestContext::name("courseGradeType");
		
		if (is_null($course_title)) 
			$course_title = "";
		
		print "<form action='$self' method='post'>
			<div>
			<p>Course Title: <br/><input type='text' name='$course_title' value='$last_title' /></p>
			<p>Course Number: <br/><input type='text' name='$course_number' value='$last_number' /></p>
			<p>Course Description: <br/><textarea name='$course_description' value='$last_description'></textarea></p>";
		
		// Print select function for terms
		print "<p>Course Term: <br/><select name='$course_term' value='$last_term'>";
			$terms =& $cmm->getTerms();
			while ($terms->hasNextTerm()) {
				$term =& $terms->nextTerm();
				$termId =& $term->getId();
				$termIdString =& $termId->getIdString();
				print "<option value='$termIdString'>".$term->getDisplayName()."</option>";
			}
		print "</select></p>";
			
		print "<p>Course Type: <br/><input type='text' name='$course_type' value='$last_type' /></p>
			<p>Course Status: <br/><input type='text' name='$course_status' value='$last_status' /></p>
			<p>Course Credits: <br/><input type='text' name='$course_credits' value='$last_credits' /></p>		
			<p>Course Grade Type: <br/><input type='text' name='$course_grade_type' value='$last_grade_type' /></p>";	
	
			print "\n\t<input type='submit' value='"._("Add")."' />";
			print "\n\t<a href='".$harmoni->request->quickURL()."'>";
			print "<input type='button' value='"._("Clear")."' /></a>";
		print "\n</div>\n</form>\n";
		
		$link = $harmoni->request->quickURL("coursemanagement", "course_search");
		print _("<h4><a href='$link'>Click here to search for courses.</a></h4>")."";
		
		$output =& new Block(ob_get_clean(), STANDARD_BLOCK);
		return $output;
	}
	
	/**
	* Prints the list of existing course offerings with the functionality to remove.
	*
	* @return void
	* @access public
	* @since 8/29/05
	*/
	function &getCourses() {
	  	$harmoni =& Harmoni::instance();
	  
	  	$cmm =& Services::getService("CourseManagement");
		$idManager =& Services::getService("Id");
		$am =& Services::GetService("AgentManager");
	  	
	  	ob_start();
		print "\n<h4>Existing course offerings.  Please click on a course offering to edit its details (e.g. add a section).</h4>";
		
		$canonicalCourseIterator =& $cmm->getCanonicalCourses();
		if (!$canonicalCourseIterator->hasNextCanonicalCourse()) {
			print "<p>No course offerings are present.</p>";
		} else {
			while ($canonicalCourseIterator->hasNextCanonicalCourse()) {
			  	$canonicalCourse =& $canonicalCourseIterator->nextCanonicalCourse();
				$courseOfferingIterator =& $canonicalCourse->getCourseOfferings();
				while ($courseOfferingIterator->hasNextCourseOffering()) {
				  	$courseOffering =& $courseOfferingIterator->nextCourseOffering();
					$id =& $courseOffering->getId();
					$idString = $id->getIdString();
					$canonicalCourseId = $canonicalCourse->getId();
					$canonicalCourseIdString = $canonicalCourseId->getIdString();
					$courseName = $courseOffering->getDisplayName();
					
					// Get term
					$courseTerm =& $courseOffering->getTerm();
					$courseTermName = $courseTerm->getDisplayName();
				
					$self = $harmoni->request->quickURL("coursemanagement", "createcourse", 
			   											array("courseIdToRemove"=>$idString));
			
					print "<form action='$self' method='post'>";
					print "\n<a href='".$harmoni->request->quickURL("coursemanagement", "edit_offering_details", 
																	array("courseId"=>$idString))."'>";
					print "\n".$courseName."</a>&nbsp;&nbsp;&nbsp;".$courseTermName."&nbsp;&nbsp;&nbsp;";
				
					print "\n\t<input type='submit' value='"._("Remove")."' />";
					print "\n</form>\n";
				}
			}
		}
		
		$output =& new Block(ob_get_clean(), STANDARD_BLOCK);
		return $output;
	}
	
	/**
	* Process any changes in the form to add a course offering.
	*
	* @param string offering
	* @param string type
	* @param string status
	* &param string location
	* @return void
	* @access public
	* @since 8/29/05
	*/
	function addCourse($courseTitle, $courseNumber, $courseDescription, $type, $status,
					   $courseTerm, $credits, $courseGradeType) {
					     
		$actionRows =& $this->getActionRows();
		$pageRows =& new Container(new YLayout(), OTHER, 1);
		$harmoni =& Harmoni::instance();
		
		$cmm =& Services::getService("CourseManagement");
		$idManager =& Services::getService("Id");
		
		$everyoneId =& $idManager->getId("edu.middlebury.agents.everyone");
		$usersId =& $idManager->getId("edu.middlebury.agents.users");
		
		$canonicalCourseIterator =& $cmm->getCanonicalCourses();
		
		$courseType =& new Type("CourseManagement", "edu.middlebury", $type);
		$courseStatus =& new Type("CourseManagement", "edu.middlebury", $status);
		
		/* Check for existing canonical course with the same title and number. */
		$coursePresent = 0;
		$canonicalCourseIterator =& $cmm->getCanonicalCourses();
		while ($canonicalCourseIterator->hasNextCanonicalCourse()) {
			$canonicalCourse =& $canonicalCourseIterator->nextCanonicalCourse();
			if ($canonicalCourse->getTitle() == $courseTitle && $canonicalCourse->getNumber() == $courseNumber) {
				$coursePresent = 1;
				break;
			}
		}
		
		/*If existing canonical course with the same title and number does not exist, create a new canonical course.*/
		if ($coursePresent == 0) {
			$canonicalCourse =& $cmm->createCanonicalCourse($courseTitle, $courseNumber, $courseDescription, 
															$courseType, $courseStatus, $credits);
		}
											      											   
		$offeringType =& new Type("CourseManagement", "edu.middlebury", $type);
		$offeringStatus =& new Type("CourseManagement", "edu.middlebury", $status);
		$offeringGradeType =& new Type("CourseManagement", "edu.middlebury", $courseGradeType);
		
		$termId =& $idManager->getId($courseTerm);
															   
		$courseOffering =& $canonicalCourse->createCourseOffering($courseTitle, $courseNumber,
																  $courseDescription, $termId, $offeringType, 
																  $offeringStatus, $offeringGradeType);
	}
	
	/**
	* Process any changes in the remove form to delete a course offering.
	*
	* @param string offering
	* @param string type
	* @param string status
	* &param string location
	* @return void
	* @access public
	* @since 8/29/05
	*/
	function removeCourse($courseOfferingIdString) {
		$harmoni =& Harmoni::instance();
		$harmoni->request->startNamespace("polyphony-agents");
		$harmoni->request->passthrough("agentId");
		
		$idManager =& Services::getService("Id");
		$cmm =& Services::getService("CourseManagement");
		
		$courseOfferingId =& $idManager->getId($courseOfferingIdString);
		
		$courseOffering =& $cmm->getCourseOffering($courseOfferingId);
		$canonicalCourse =& $courseOffering->getCanonicalCourse();
		$canonicalCourseId =& $canonicalCourse->getId();
		
		// Delete course offering
		$canonicalCourse->deleteCourseOffering($courseOfferingId);
		
		// If no course offerings left, remove the canonical course, too.	
		$courseOfferingIterator =& $canonicalCourse->getCourseOfferings();
	
		if (!$courseOfferingIterator->hasNextCourseOffering())
			$cmm->deleteCanonicalCourse($canonicalCourseId);
	}
}