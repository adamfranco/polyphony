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
* @version $Id: edit_section_details.act.php,v 1.12 2006/08/23 23:45:38 jwlee100 Exp $
*/

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(HARMONI."GUIManager/Components/Blank.class.php");

class edit_section_detailsAction
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

		$actionRows =& $this->getActionRows();
		$pageRows =& new Container(new YLayout(), OTHER, 1);
		$harmoni =& Harmoni::instance();

		$harmoni->request->startNamespace("polyphony-agents");
		$harmoni->request->passthrough("courseId");
		$sectionIdString = $harmoni->request->get("courseId");
		$furtherAction = $harmoni->request->get("furtherAction");

		$idManager =& Services::getService("Id");
		$sectionId =& $idManager->getId($sectionIdString);
		$cm =& Services::getService("CourseManagement");
		$section =& $cm->getCourseSection($sectionId);


		ob_start();

		print "<div style='margin-left: 15px'>";

		if (!$furtherAction) $furtherAction = "edit_section_detailsAction::viewSectionDetails";

		$actionFunctions = array(
		"edit_section_detailsAction::viewSectionDetails",
		"edit_section_detailsAction::editSectionDetails",
		"edit_section_detailsAction::confirmDeleteSection",
		"edit_section_detailsAction::deleteSection",
		"edit_section_detailsAction::searchStudentToAdd",
		"edit_section_detailsAction::chooseStudentToAdd",
		"edit_section_detailsAction::addStudent",
		"edit_section_detailsAction::chooseStudentToRemove",
		"edit_section_detailsAction::removeStudent",

		);


		if($furtherAction && in_array($furtherAction, $actionFunctions)){
			eval($furtherAction.'($section);');
		}else{
			print "No such action '".$furtherAction."'";	
		}

		print "</div>";

		
		
		// Layout
		$actionRows->add(new Block(ob_get_contents(),2),"100%", null, CENTER, TOP);
		ob_end_clean();
		$harmoni->request->forget("courseId");
		$harmoni->request->endNamespace();

		textdomain($defaultTextDomain);
	}

	/***************************FUNCTIONS***************************************/

	/**
	* shows the details of the offering's properties and gives menu of actions
	*
	* @param object Agent $agent
	* @return void
	* @access public
	* @since 7/19/05
	*/
	function viewSectionDetails(&$section){




		$sectionId =& $section->getId();


		//display offering info

		print "\n<table><tr><td>";

		print "\n<h2>".$section->getDisplayName()."</h2>";

		/*
		print "\n<table bgcolor='#AAAAAA' cellspacing='1' cellpadding='3'>";

		print "\n\t<tr>";
		print "\n\t\t<td>Display Name</td>";
		print "\n\t\t<td>";
		print $section->getDisplayName();
		print "</td>";
		print "\n\t</tr>";
		print "\n\t<tr>";
		print "\n\t\t<td>Number</td>";
		print "\n\t\t<td>";
		print $section->getNumber();
		print "</td>";
		print "\n\t</tr>";

		print "</table>";

		*/
		print "</td><td>";




		//actions menu
		$harmoni =& Harmoni::instance();
		$url =& $harmoni->request->mkURL();

		
		$courseId =& $section->getId();
		$courseIdString = $courseId->getIdString();
		
		print "<ul>";
		
		$link1 = $harmoni->request->quickURL("coursemanagement", "edit_section_details", array("courseId"=>$courseIdString, "furtherAction"=>"edit_section_detailsAction::editSectionDetails"));				
		print "<li><a href='".$link1."'>Edit section</a></li>";
		
		$link2 = $harmoni->request->quickURL("coursemanagement", "edit_section_details", array("courseId"=>$courseIdString, "furtherAction"=>"edit_section_detailsAction::deleteSection"));				
		print "<li><a href='".$link2."'>Delete section</a></li>";
		
		
		
		//print "<li><a href='".$url->write("furtherAction","edit_section_detailsAction::editOffering")."'>Edit section</a></li>";
		//print "<li><a href='".$url->write("furtherAction","edit_section_detailsAction::deleteOffering")."'>Delete section</a></li>";

		

		$harmoni->history->markReturnURL("polyphony/coursemanagement/edit_section_details");
		$link1 = $harmoni->request->quickURL("coursemanagement", "edit_section_details",
		array("courseId"=>$courseIdString, "furtherAction"=>"edit_section_detailsAction::searchStudentToAdd"));
		print 	"<li><a href='$link1'>Add student</a></li>";
		
		
		$link3 = $harmoni->request->quickURL("coursemanagement", "edit_section_details", array("courseId"=>$courseIdString, "furtherAction"=>"edit_section_detailsAction::chooseStudentToRemove"));				
		print "<li><a href='".$link3."'>Remove Student</a></li>";
		
		/*
		
		print "<li><a href='".$url->write("furtherAction","edit_section_detailsAction::removeStudent")."'>Remove Student</a></li>";
		
		$harmoni->history->markReturnURL("polyphony/coursemanagement/deletestudent");
		$link2 = $harmoni->request->quickURL("coursemanagement", "deletestudent",
		array("courseId"=>$courseIdString));
		print	"<li><a href='$link2'>Delete student</a></li>*/
		print "</ul>";
		
		print "\n</td></tr></table>";


		print "\n<h4>Members</h4>";




		$enrollmentRecordIterator =& $section->getRoster();

		$am =& Services::getService("AgentManager");



		print "<table cellpadding=20>";
		$column = 0;
		print "\t<tr>";
		while($enrollmentRecordIterator->hasNextEnrollmentRecord()){

			if($column==4){
				$column=0;
				print "\n\t<tr></tr>";
			}
			$column++;

			$er =& $enrollmentRecordIterator->nextEnrollmentRecord();
			$id =& $er->getStudent();
			$member =& $am->getAgent($id);


			$harmoni =& Harmoni::instance();

			$harmoni->history->markReturnURL("polyphony/agents/edit_agent_details");

			print "\n<td><a href='".$harmoni->request->quickURL("agents","edit_agent_details", array("agentId"=>$id->getIdString()))."'>";
			print "\n".$member->getDisplayName()."</a>";
			print "\n - <a href=\"Javascript:alert('"._("Id:").'\n\t'.addslashes($id->getIdString())."')\">Id</a></td>";



		}

		print "</tr></table>";


	}




	/***
	* offers a confirmation screen for deleting an entire offering
	*/

	function confirmDeleteOffering(&$offering){
		$harmoni =& Harmoni::instance();
		$url =& $harmoni->request->mkURL();
		print "Do you really want to delete ".$offering->getDisplayName()."?<br />";
		print "<form action='".$url->write("furtherAction","edit_offering_detailsAction::deleteOffering")."' method='post'><input type='submit' value='Delete' /></form><input type='button' value='Cancel' onclick='history.back()' />";
		return;
	}

	/***
	* Handles the actual deletion of an offering
	*/

	function deleteOffering(&$offering){
		//$cm =& Services::getService("CourseManagement");
		$can =&  $offering->getCanonicalCourse();
		$can->deleteCourseOffering($offering->getId());

		$harmoni =& Harmoni::instance();
		print "Offering deleted.<br />";
		print "<a href='".$harmoni->history->getReturnURL("polyphony/coursemanagement/edit_offering_details")."'>Go Back</a>";

		return;
	}

	function searchStudentToAdd(&$section) {
	  	$actionRows =& $this->getActionRows();
		$pageRows =& new Container(new YLayout(), OTHER, 1);
		$harmoni =& Harmoni::instance();
		
		$cmm =& Services::getService("CourseManagement");
		$idManager =& Services::getService("Id");
		$am =& Services::GetService("AgentManager");
		
		$courseIdString = RequestContext::value("courseId");
		$courseId = $idManager->getId($courseIdString);
		$courseSection = $cmm->getCourseSection($courseId);
		
		$everyoneId =& $idManager->getId("edu.middlebury.agents.everyone");
		$usersId =& $idManager->getId("edu.middlebury.agents.users");
		
		/*********************************************************
		 * the agent search form
		 *********************************************************/
		// Search header
		$sectionName = $section->getDisplayName();
		$actionRows->add(new Heading(_("Search for students to add to this course."), 2), "100%", null, LEFT, CENTER);
		
		ob_start();
		$self = $harmoni->request->quickURL("coursemanagement", "edit_section_details", 
			array("search_criteria", "search_type", "furtherAction"=>"edit_section_detailsAction::searchStudentToAdd"));
		print _("<p>Please enter a student's name to search and add and select the type of registration (regular student or auditing).</p>")."";
		
		$lastCriteria = $harmoni->request->get("search_criteria");
		$search_criteria_name = RequestContext::name("search_criteria");
		
		$last_type_name = $harmoni->request->get("search_type");
		$search_type_name = RequestContext::name("search_type");
		
		
		print "<form action='$self' method='post'>
			<div>
			<input type='text' name='$search_criteria_name' value='$lastCriteria' />";
			
			
			print "<select name='$search_type_name'>";
			print "<option value='student' selected='selected'>Student</option>";
			print "<option value='auditing'>Auditing</option>";
			print "</select>";
			
	
			print "\n\t<input type='submit' value='"._("Search")."' />";
			print "\n\t<a href='".$harmoni->request->quickURL()."'>";
			print "<input type='button' value='"._("Clear")."' /></a>";
		print "\n</div>\n</form>";
		
		$actionRows->add(new Block(ob_get_contents(), STANDARD_BLOCK), "100%", null, LEFT, CENTER);
		ob_end_clean();
		
		/*********************************************************
		 * the agent search results
		 *********************************************************/
		ob_start();
		

		
		if ($search_criteria = $harmoni->request->get('search_criteria')) {
			//$typeParts = explode("::", @html_entity_decode($search_type, ENT_COMPAT, 'UTF-8'));
			
		
			
			$searchType =& new HarmoniType("Agent & Group Search", "edu.middlebury.harmoni", "TokenSearch");
			$string = "*".$search_criteria."*";
			$agents =& $am->getAgentsBySearch($string, $searchType);
			$displayName = $section->getDisplayName();
			print "<p]><font size=+1>Search results</font></p>";
			print "Click on a student's name to add for the course, <b>$displayName</b>.";
			
			while ($agents->hasNext()) {
				$agent =& $agents->next();
				$id =& $agent->getId();
				$harmoni->history->markReturnURL("polyphony/coursemanagement/edit_section_details");
		
				/*		
				print "\n<p align='center'><a href='".$harmoni->request->quickURL("coursemanagement", 
				"edit_section_details", array("agentId"=>$id->getIdString(), "search_type"=>$search_type_name, 
				"furtherAction"=>"edit_section_detailsAction::addStudent"))."'>";
				
				print "\n".$agent->getDisplayName()."</a>";
								
				$lastType = "student";
				$search_type_name = RequestContext::value("search_type");
				*/
				
				$idString = $id->getIdString();
				$self = $harmoni->request->quickURL("coursemanagement", 
				"edit_section_details", array("agentId"=>$idString, "search_type"=>$search_type_name, 
				"furtherAction"=>"edit_section_detailsAction::addStudent"));
				
				print "<p>";
				print "<form action='$self' method='post'>";
				print "\n<u>".$agent->getDisplayName()."</u>";
				
				/*
				print "<select name='search_type' value=$lastType>";
				print "<option value='student' selected='selected'>Student</option>";
				print "<option value='auditing'>Auditing</option>";
				print "</select>";
				*/
				
				print "\n\t<input type='submit' value='"._("Add")."' />";
				print "</form></p>";				
			}
			print "\n</div>";
		} else {
			print "<p>Please enter a name to search and add to the class.</p>";
			
			$link = $harmoni->request->quickURL("coursemanagement", "edit_section_details", 
			array("furtherAction"=>"edit_section_detailsAction::viewSectionDetails"));
			print "<p>Otherwise, click <a href=$link>here</a> to return to viewing section details.</p>";
		}
		$actionRows->add(new Block(ob_get_contents(), STANDARD_BLOCK), "100%", null, LEFT, CENTER);
		ob_end_clean();
	}
	
	function addStudent(&$section) {
		$actionRows =& $this->getActionRows();
		$pageRows =& new Container(new YLayout(), OTHER, 1);
		$harmoni =& Harmoni::instance();
		
		$cmm =& Services::getService("CourseManagement");
		$idManager =& Services::getService("Id");
		$am =& Services::GetService("AgentManager");
		
		$agentIdString = RequestContext::value("agentId");
		$searchType = RequestContext::value("search_type");
		$agentId = $idManager->getId($agentIdString);
		$agent =& $am->getAgent($agentId);
		
		$everyoneId =& $idManager->getId("edu.middlebury.agents.everyone");
		$usersId =& $idManager->getId("edu.middlebury.agents.users");
		
		$enrollmentStatusType =& new Type("EnrollmentStatusType", "edu.middlebury", $searchType);
		
		// Search the record to see if student is already enrolled.
		$studentPresent = 0;
		$roster =& $section->getRoster();
		while ($roster->hasNextEnrollmentRecord()) {
			$record = $roster->nextEnrollmentRecord();
			$studentId =& $record->getStudent();
			$idString = $studentId->getIdString();
			
			if ($idString == $agentIdString) {
				$studentPresent = 1;
				break;
			}
		}
		
		// Add student to record if student is not enrolled; otherwise, print a message stating that he/she is enrolled.
		$agentName = $agent->getDisplayName();
		$courseName = $section->getDisplayName();
		if ($studentPresent == 0) {
			$section->addStudent($agentId, $enrollmentStatusType);
			print "<p><center>$agentName added to $courseName.</center></p>";
		} else {
		  	print "<p><center>$agentName is already enrolled in $courseName.</center></p>";
		}
		
		print "<hr>";
		
		$url =& new GETMethodURLWriter();
		$url->setModuleAction("coursemanagement","edit_section_details");
		$sectionId =& $section->getId();
		$url->setValue("courseId",$sectionId->getIdString());
		$url->setValue("furtherAction","edit_section_detailsAction::searchStudentToAdd");
		print "<p><a href='".$url->write()."'>Click here to add another student.</a></p>";
		
		$url =& new GETMethodURLWriter();
		$url->setModuleAction("coursemanagement","edit_offering_details");
		$offering =& $section->getCourseOffering();
		$offeringId =& $offering->getId();
		$url->setValue("courseId",$offeringId->getIdString());
		print "<p><a href='".$url->write()."'>Click here to return to Course Offering.</a></p>";
		
		$actionRows->add(new Block(ob_get_contents(), STANDARD_BLOCK), "100%", null, LEFT, CENTER);	
		ob_end_clean();
	}
	
	function removeStudent(&$section) {
		$harmoni =& Harmoni::instance();
		$harmoni->request->startNamespace("polyphony-agents");
		$harmoni->request->passthrough("agentId");
		
		$idManager =& Services::getService("Id");
		$am =& Services::GetService("AgentManager");
		
		$student = RequestContext::value('agentId');
	

		$studentId = $idManager->getId($student);
			
		$agent =& $am->getAgent($studentId);
		$agentName = $agent->getDisplayName();
			
		print "<p><center>'$agentName' has been deleted.</center></p>";
		$section->removeStudent($studentId);
		$roster =& $section->getRoster();
		
		print "\n<hr>\n";

		if ($roster->hasNextEnrollmentRecord()) {
			$url =& new GETMethodURLWriter();
			$url->setModuleAction("coursemanagement","edit_section_details");
			$sectionId =& $section->getId();
			$url->setValue("courseId",$sectionId->getIdString());
			$url->setValue("furtherAction","edit_section_detailsAction::chooseStudentToRemove");
			print "<p><a href='".$url->write()."'>Click here to delete another student</a></p>";
		} else {
			print "<p><i>No students are enrolled in this course.</i></p>";
		}

		$url =& new GETMethodURLWriter();
		$url->setModuleAction("coursemanagement","edit_offering_details");
		$offering =& $section->getCourseOffering();
		$offeringId =& $offering->getId();
		$url->setValue("courseId",$offeringId->getIdString());
		print "<p><a href='".$url->write()."'>Click here to return to Course Offering</a></p>";
	}
	
	function chooseStudentToRemove(&$section) {
		$harmoni =& Harmoni::instance();
		
		$cmm =& Services::getService("CourseManagement");
		$idManager =& Services::getService("Id");
		$am =& Services::GetService("AgentManager");
		
		//$courseIdString = RequestContext::value("courseId");
		///$courseId = $idManager->getId($courseIdString);
		//$courseSection = $cmm->getCourseSection($courseId);
	
		print "<h2>Remove Student</h2>";
	
		
		print "<p>Please select a student.</p>";
		
		//print "\n<form action='".$url."' method='post'>";
		//print "\n<select name='agentId'>";
		//print"<option value=''>Name of Student</option>";
		
		print "<table width='100%'><tr>";
	
		$i = 1;
		
		$roster =& $section->getRoster();
		if (!$roster->hasNextEnrollmentRecord()) {
			print "<p>No students are enrolled in this class.</p>";
			
			$courseId = $section->getId();
			$courseIdString = $courseId->getIdString();
			$harmoni->history->markReturnURL("polyphony/coursemanagement/edit_section_details");
			$link1 = $harmoni->request->quickURL("coursemanagement", "edit_section_details",
			array("courseId"=>$courseIdString, "furtherAction"=>"edit_section_detailsAction::searchStudentToAdd"));
			print "<p><a href='$link1'>Click here to add a student.</a></p>";
			
			$url =& new GETMethodURLWriter();
			$url->setModuleAction("coursemanagement","edit_offering_details");
			$offering =& $section->getCourseOffering();
			$offeringId =& $offering->getId();
			$url->setValue("courseId",$offeringId->getIdString());
			print "<p><a href='".$url->write()."'>Click here to return to Course Offering</a></p>";
		} else {
			while ($roster->hasNextEnrollmentRecord()) {
				$er =& $roster->nextEnrollmentRecord();
				$agent =& $am->getAgent($er->getStudent());
			
				$agentName = $agent->getDisplayName();
				$id =& $agent->getId();
				
				$self = $harmoni->request->quickURL("coursemanagement", "edit_section_details", 
				array("furtherAction"=>"edit_section_detailsAction::removeStudent","agentId"=>$id->getIdString()));
			
				print "<p>";
				print "<form action='$self' method='post'>";
				print "<u>$agentName</u>";
				print "\n\t<input type='submit' value='"._("Remove")."' />";
				print "</form></p>";
			
				/*
				print "<td><a href='".$harmoni->request->quickURL("coursemanagement", "edit_section_details", 
				array("furtherAction"=>"edit_section_detailsAction::removeStudent","agentId"=>$id->getIdString()))."'>
				Remove ".$agentName."</a></td>";
				*/
				
				if ($i%4 == 0) {
					print "</tr><tr>";	
				}
				$i++;
			
				//print "<option value='".$id."'>".$agentName."</option>";
			}
		}
		
		print "</td></table>";
		
		//print "</select>";
		
		//print "<input type='submit' value='"._("Delete")."' />";

		
		
		
		
	}
	

	/*

	function _printAgent($agent){



	if(!is_null($term)){
	//print "\n</table>";
	print "\n\t<tr>";
	print "\n<td><hr></td>";
	print "\n<td><hr></td>";
	print "\n\t\t</tr>";
	print "\n\t<tr>";
	print "\n\t\t<td><h4>";
	print $term->getDisplayName();
	print "</h4</td>";

	}else{
	print "\n\t<tr>";
	print "\n\t\t<td>";

	print "</td>";
	}

	$harmoni =& Harmoni::instance();
	$id =& $offering->getId();
	print "\n\t\t<td>";
	print "\n<a href='".$harmoni->request->quickURL("coursemanagement","edit_offering_details", array("offeringId"=>$id->getIdString()))."'>";
	print $offering->getDisplayName()."</a>";
	print "\n - <a href=\"Javascript:alert('"._("Id:").'\n\t'.addslashes($id->getIdString())."')\">"._("Id")."</a>";
	print "</td>";
	//print "\n\t\t<td>";
	//$term =& $offering->getTerm();
	//print $term->getDisplayName();
	//print "</td>";
	print "\n\t</tr>";



	}*/

}