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
* @version $Id: edit_section_roster.act.php,v 1.1 2006/08/25 19:16:13 jwlee100 Exp $
*/

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(HARMONI."GUIManager/Components/Blank.class.php");

class edit_section_rosterAction
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
		
		$sectionIdString = $harmoni->request->get("courseId");
		$sectionId =& $idManager->getId($sectionIdString);
		$cm =& Services::getService("CourseManagement");
		$section =& $cm->getCourseSection($sectionId);

		
		// Process any changes
		if (RequestContext::value("agentIdToAdd") && RequestContext::value("status"))
			$this->addStudent($section, RequestContext::value("agentIdToAdd"), 
										RequestContext::value("status"));
		
		if (RequestContext::value("agentIdToRemove"))
			$this->removeStudent($section, RequestContext::value("agentIdToRemove"));
		
		
		// Print out our search and memebers
		$actionRows =& $this->getActionRows();
		$sectionName = $section->getDisplayName();
		
		$actionRows->add(new Heading(_("Edit roster for $sectionName."), 2), "100%", null, LEFT, CENTER);
		
		$actionRows->add($this->getAddForm($section), "100%", null, LEFT, CENTER);
		$actionRows->add($this->getMembers($section), "100%", null, LEFT, CENTER);
		
		$harmoni->request->forget("courseId");
		$harmoni->request->endNamespace();

		textdomain($defaultTextDomain);
	}

	/***************************FUNCTIONS***************************************/

	/*********************************************************
	* the wild card search form to add students
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
		$link1 = $harmoni->request->quickURL("coursemanagement", "edit_offering_details", 
											array("courseId"=>$courseIdString));
		
		print "<p><a href='$link1'>Return to course offering details.</a></p>";
		
		print _("<p>Please enter a student's name to search and add.</p>")."";
		
		// Search header
		$self = $harmoni->request->quickURL("coursemanagement", "edit_section_roster", 
			array("search_criteria", "search_type"));
		
		$lastCriteria = $harmoni->request->get("search_criteria");
		$search_criteria_name = RequestContext::name("search_criteria");
		
		if (is_null($search_criteria_name)) 
			$search_criteria_name = "";
		
		print "<form action='$self' method='post'>
			<div>
			<input type='text' name='$search_criteria_name' value='$lastCriteria' />";	
	
			print "\n\t<input type='submit' value='"._("Search")."' />";
			print "\n\t<a href='".$harmoni->request->quickURL()."'>";
			print "<input type='button' value='"._("Clear")."' /></a>";
		print "\n</div>\n</form>\n";
		
		// Agent search results		
		if ($search_criteria = $harmoni->request->get('search_criteria')) {
			$searchType =& new HarmoniType("Agent & Group Search", "edu.middlebury.harmoni", "TokenSearch");
			$string = "*".$search_criteria."*";
			$agents =& $am->getAgentsBySearch($string, $searchType);
			$displayName = $section->getDisplayName();
			print "\n<hr/><p><h3>Search results</h3></p>";
			print "Click on a student's name to add for the course, <strong>$displayName</strong>.";
			
			while ($agents->hasNext()) {
				$agent =& $agents->next();
				$id =& $agent->getId();
				$harmoni->history->markReturnURL("polyphony/coursemanagement/edit_section_details");
		
				$last_status_name = $harmoni->request->get("status");
				$status = RequestContext::name("status");
				
				if (is_null($status)) 
					$status = "";
				
											
				$idString = $id->getIdString();
				$self = $harmoni->request->quickURL("coursemanagement", "edit_section_roster", 
													array("agentIdToAdd"=>$idString, "status"=>$status, 
													"search_criteria"=>RequestContext::value("search_criteria")));
				
				print "<p>";
				print "<form action='$self' method='post'>";
				print "\n<u>".$agent->getDisplayName()."</u>";
				
				/* Search the record to see if student is already enrolled. */
				$studentPresent = 0;							// To check later whether student is enrolled or not
				$roster =& $section->getRoster();
				while ($roster->hasNextEnrollmentRecord()) {
					$record = $roster->nextEnrollmentRecord();
					$studentId =& $record->getStudent();

					if ($id->isEqual($studentId))
						$studentPresent = 1;					// Set to student already being enrolled
				}
				
				if ($studentPresent == 0) {
				  	print "<select name='".$status."' value='".$last_status_name."'>";
					print "<option value='Student' selected='selected'>Student</option>";
					print "<option value='Auditing'>Auditing</option>";
					print "</select>";
				  	print "\n\t<input type='submit' value='"._("Add")."' />";
				}
				print "</form></p>";		
			}
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
	
	function addStudent(&$section, $agentIdString, $status) {
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
		
		/* Search the record to see if student is already enrolled. */
		$studentPresent = 0;							// To check later whether student is enrolled or not
		$roster =& $section->getRoster();
		while ($roster->hasNextEnrollmentRecord()) {
			$record = $roster->nextEnrollmentRecord();
			$studentId =& $record->getStudent();

			if ($agentId->isEqual($studentId))
				$studentPresent = 1;					// Set to student already being enrolled
		}
				
		if ($studentPresent == 0) {
		  	$enrollmentStatusType =& new Type("EnrollmentStatusType", "edu.middlebury", $status);
			$section->addStudent($agentId, $enrollmentStatusType);
		}
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