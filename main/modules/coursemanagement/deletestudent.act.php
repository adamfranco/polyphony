<?php

/**
 * @package polyphony.modules.coursemanagement
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: deletestudent.act.php,v 1.3 2006/08/19 21:08:41 sporktim Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(HARMONI."GUIManager/Components/Blank.class.php");


class deletestudentAction 
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
 		if ($authZManager->isUserAuthorized(
 					$idManager->getId("edu.middlebury.authorization.view"),
 					$idManager->getId("edu.middlebury.coursemanagement")))
 		{
			return TRUE;
 		} else {
 			
 			return FALSE;
		}
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
	  	$cmm =& Services::getService("CourseManagement");
		$idManager =& Services::getService("Id");
		
		$courseIdString = RequestContext::value("sectionId");
		$courseId = $idManager->getId($courseIdString);
		$courseSection = $cmm->getCourseSection($courseId);
		$courseName = $courseSection->getDisplayName();
		
		return dgettext("polyphony", "Delete students from ".$courseName);
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$actionRows =& $this->getActionRows();
		$pageRows =& new Container(new YLayout(), OTHER, 1);
		$harmoni =& Harmoni::instance();
		
		$cmm =& Services::getService("CourseManagement");
		$idManager =& Services::getService("Id");
		$am =& Services::GetService("AgentManager");
		
		$courseIdString = RequestContext::value("courseId");
		$courseId = $idManager->getId($courseIdString);
		$courseSection = $cmm->getCourseSection($courseId);
				
		ob_start();
				
		print "\n<form action='$self' method='post'>";
		print "\n<select name='student'>";
		print"<option value=''>Name of Student</option>";
		
		$roster =& $courseSection->getRoster();
		while ($roster->hasNext()) {
			$er =& $iter->next();
			$agent =& $am->getAgent($er->getStudent());
			
			$agentName = $agent->getDisplayName();
			$id =& $agent->getId();
			print "<option value='$id'>'$agentName'</option>";
		}
		
		print "</select>";
		print "<input type'submit' value='"._("Delete")."'>";
		
		$actionRows->add(new Block(ob_get_contents(), STANDARD_BLOCK), "100%", null, LEFT, CENTER);
		ob_end_clean();
		
		$student = RequestContext::value('student');
		
		if ($student != '') {
		  	ob_start();
			$studentId = $idManager->getId($student);
			
			$agent =& $am->getAgent($studentId);
			$agentName = $agent->getDisplayName();
			
			print "<p>'$agentName' deleted</p>";
			$courseSection->deleteStudent($studentId);
			
			$groupLayout =& new Block(ob_get_contents(), STANDARD_BLOCK);
			ob_end_clean();
			
			$pageRows->add($groupLayout, "100%", null, LEFT, CENTER);	
			$actionRows->add($pageRows, "100%", null, LEFT, CENTER);
		} else {
			print "<p>Please select a student.</p>";
		}
	}
}