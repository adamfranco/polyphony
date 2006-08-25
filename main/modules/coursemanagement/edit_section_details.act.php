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
* @version $Id: edit_section_details.act.php,v 1.17 2006/08/25 19:15:37 jwlee100 Exp $
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
		$idManager =& Services::getService("Id");

		$actionRows =& $this->getActionRows();
		$pageRows =& new Container(new YLayout(), OTHER, 1);
		$harmoni =& Harmoni::instance();

		$harmoni->request->startNamespace("polyphony-agents");
		$harmoni->request->passthrough("courseId");
		$sectionIdString = $harmoni->request->get("courseId");
		$furtherAction = $harmoni->request->get("furtherAction");

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
		);


		if($furtherAction && in_array($furtherAction, $actionFunctions)){
			eval($furtherAction.'($section);');
		}else{
			print "No such action '".$furtherAction."'";	
		}

		print "</div>";

		
		
		// Layout
		$actionRows->add(new Block(ob_get_contents(), 2),"100%", null, CENTER, TOP);
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

		print "</td><td>";




		//actions menu
		$harmoni =& Harmoni::instance();
		$url =& $harmoni->request->mkURL();

		
		$courseId =& $section->getId();
		$courseIdString = $courseId->getIdString();
		
		print "<ul>";
		
		$link1 = $harmoni->request->quickURL("coursemanagement", "edit_section_details", 
		array("courseId"=>$courseIdString, "furtherAction"=>"edit_section_detailsAction::editSectionDetails"));				
		print "<li><a href='".$link1."'>Edit section</a></li>";
		
		$link2 = $harmoni->request->quickURL("coursemanagement", "edit_section_details", 
		array("courseId"=>$courseIdString, "furtherAction"=>"edit_section_detailsAction::deleteSection"));				
		print "<li><a href='".$link2."'>Delete section</a></li>";
		
		$harmoni->history->markReturnURL("polyphony/coursemanagement/edit_section_details");
		$link1 = $harmoni->request->quickURL("coursemanagement", "edit_section_roster",
		array("courseId"=>$courseIdString));
		print 	"<li><a href='$link1'>Edit roster</a></li>";
		

		print "</ul>";
		
		print "\n</td></tr></table>";


		print "\n<h4>Members</h4>";




		$enrollmentRecordIterator =& $section->getRoster();

		$am =& Services::getService("AgentManager");



		print "<table cellpadding='20'>";
		print "\t<tr>";
		$column = 0;
		if (!$enrollmentRecordIterator->hasNextEnrollmentRecord()) {
			print "<td></td>";
		} else {
			while ($enrollmentRecordIterator->hasNextEnrollmentRecord()) {
				if ($column == 4) {
					$column = 0;
					print "\n\t</tr><tr>";
				}
				$column++;
	
				$er =& $enrollmentRecordIterator->nextEnrollmentRecord();
				$id =& $er->getStudent();
				$member =& $am->getAgent($id);
	

				$harmoni =& Harmoni::instance();

				$harmoni->history->markReturnURL("polyphony/agents/edit_agent_details");

				print "\n<td><a href='".$harmoni->request->quickURL("agents", "edit_agent_details", 
																	array("agentId"=>$id->getIdString()))."'>";
				print "\n".$member->getDisplayName()."</a>";
				print "\n - <a href=\"Javascript:alert('"._("Id:").'\n\t'.addslashes($id->getIdString())."')\">Id</a>
					  </td>";
			}
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
}