<?php

/**
 * @package polyphony.modules.coursemanagement
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: addstudent.act.php,v 1.1 2006/08/02 20:59:56 jwlee100 Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(HARMONI."GUIManager/Components/Blank.class.php");


class printrosterAction 
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
		
		$courseIdString = RequestContext::value("courseId");
		$courseId = $idManager->getId($courseIdString);
		$courseOffering = $cmm->getCourseOffering($courseId);
		$courseName = $courseOffering->getDisplayName();
		
		return dgettext("polyphony", "Course Offering Enrollment Record");
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
				
		print "\n<table border=1>";
		print "\n<tr>Name of Student</tr>";
		
		$roster =& $courseSection->getRoster();
		while ($roster->hasNext()) {
			$er =& $iter->next();
			$agent =& $am->getAgent($er->getStudent());
			
			$agentName = $agent->getDisplayName();
			print "<tr>";
			print $agentName;
			print "</tr>";
		}
		
		$groupLayout =& new Block(ob_get_contents(), STANDARD_BLOCK);
		print "</table>";
		ob_end_clean();
		
		$pageRows->add($groupLayout, "100%", null, LEFT, CENTER);	
		$actionRows->add($pageRows, "100%", null, LEFT, CENTER);	
	}
}