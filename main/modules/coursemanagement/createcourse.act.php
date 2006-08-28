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
* @version $Id: createcourse.act.php,v 1.5 2006/08/28 18:03:26 jwlee100 Exp $
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
			RequestContext::value("courseTerm"))
			$this->addCourse(RequestContext::value("courseTitle"), RequestContext::value("courseNumber"),
							 RequestContext::value("courseDescription") && RequestContext::value("courseType"), 				
							 RequestContext::value("courseStatus"), RequestContext::value("courseTerm"));
		
		if (RequestContext::value("courseIdToRemove"))
			$this->removeCourse($section, RequestContext::value("courseIdToRemove"));
		
		
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
		
		if (is_null($course_title)) 
			$course_title = "";
		
		print "<form action='$self' method='post'>
			<div>
			<input type='text' name='$course_title' value='$last_title' />	
			<input type='text' name='$course_number' value='$last_number' />
			<input type='text' name='$course_description' value='$last_description' />
			<input type='text' name='$course_type' value='$last_type' />
			<input type='text' name='$course_status' value='$last_status' />
			<input type='text' name='$course_term' value='$last_term' />";		
	
			print "\n\t<input type='submit' value='"._("Search")."' />";
			print "\n\t<a href='".$harmoni->request->quickURL()."'>";
			print "<input type='button' value='"._("Clear")."' /></a>";
		print "\n</div>\n</form>\n";
		}
		$output =& new Block(ob_get_clean(), STANDARD_BLOCK);
		return $output;
	}
	
	/***********************************************************************************
	* Members to remove																   *		
	************************************************************************************/
	function &getMembers(&$section) {
	  	$harmoni =& Harmoni::instance();
	  
	  	$cmm =& Services::getService("CourseManagement");
		$idManager =& Services::getService("Id");
		$am =& Services::GetService("AgentManager");
	  	
	  	ob_start();
		print "\n<h4>Members</h4>";
		
		$roster =& $section->getRoster();
		if (!$roster->hasNextEnrollmentRecord()) {
			print "<p>No students are enrolled in this class.</p>";
		} else {
			while ($roster->hasNextEnrollmentRecord()) {
				$er =& $roster->nextEnrollmentRecord();
				$agent =& $am->getAgent($er->getStudent());
			
				$agentName = $agent->getDisplayName();
				$id =& $agent->getId();
				$idString = $id->getIdString();
				
				$self = $harmoni->request->quickURL("coursemanagement", "edit_section_roster", 
													array("agentIdToRemove"=>$idString,
														  "search_criteria"=>RequestContext::value("search_criteria")));
			
				print "<form action='$self' method='post'>";
				print "\n<a href='".$harmoni->request->quickURL("agents", "edit_agent_details", 
																array("agentId"=>$id->getIdString()))."'>";
				print "\n".$agent->getDisplayName()."</a>";
				
				$statusType =& $er->getStatus();
				$status = $statusType->getKeyword();
				
				print "&nbsp;&nbsp;&nbsp;$status&nbsp;&nbsp;&nbsp;";

				print "\n\t<input type='submit' value='"._("Remove")."' />";
				print "\n</form>\n";
			}
		}
		
		$output =& new Block(ob_get_clean(), STANDARD_BLOCK);
		return $output;
	}
	
	function addCourse(&$section, $agentIdString, $status) {
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
		
		$sectionType =& $courseManager->_indexToType($values['namedescstep']['type'],'section');
		$statusType =& $courseManager->_indexToType($values['namedescstep']['statusType'],'section_stat');
		$courseGradeType =& $courseManager->_indexToType($values['namedescstep']['courseGrade'],'grade');
		$location =& $values['namedescstep']['location'];
			
		/*$canonicalCourse =& $courseManager->createCanonicalCourse($values['namedescstep']['title'], 
																   $values['namedescstep']['number'], 	
																   $values['namedescstep']['description'], 
																   $courseType, $statusType, 
																   $values['namedescstep']['credits']);*/
																	   
		$id =& $idManager->getId($values['namedescstep']['courseid']);   
		$courseOffering =& $courseManager->getCanonicalCourse($id);														   
																   
		$courseOffering =& $canonicalCourse->createCourseOffering($canonicalCourse->getTitle(), 
																  $canonicalCourse->getNumber(),
																  $canonicalCourse->getDescription(),	
																  $sectionType, $statusType, 
																  $location);
	}
	
	function removeStudent(&$section, $agentIdString) {
		$harmoni =& Harmoni::instance();
		$harmoni->request->startNamespace("polyphony-agents");
		$harmoni->request->passthrough("agentId");
		
		$idManager =& Services::getService("Id");
		$am =& Services::GetService("AgentManager");
		
		$agentId =& $idManager->getId($agentIdString);
		$section->removeStudent($agentId);
	}
}