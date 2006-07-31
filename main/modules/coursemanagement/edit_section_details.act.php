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
 * @version $Id: edit_section_details.act.php,v 1.2 2006/07/31 14:57:57 sporktim Exp $
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
		$sectionIdString = $harmoni->request->get("sectionId");

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
		$harmoni->request->passthrough("offeringId");
		$sectionIdString = $harmoni->request->get("sectionId");
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
		$harmoni->request->passthrough("sectionId");
		$sectionIdString = $harmoni->request->get("sectionId");
		$furtherAction = $harmoni->request->get("furtherAction");

		$idManager =& Services::getService("Id");
		$sectionId =& $idManager->getId($sectionIdString);
		$cm =& Services::getService("CourseManagement");
		$section =& $cm->getCourseOffering($sectionId);

		
		ob_start();
		
		print "<div style='margin-left: 15px'>";
		
		if (!$furtherAction) $furtherAction = "edit_section_detailsAction::viewSectionDetails";
		
		$actionFunctions = array(
			"edit_section_detailsAction::viewSectionDetails",
			"edit_section_detailsAction::confirmDeleteSection",
			"edit_section_detailsAction::deleteSection",
			"edit_section_detailsAction::addStudent",
			"edit_section_detailsAction::removeStudent",
			
		);
			
		
		if($furtherAction && in_array($furtherAction, $actionFunctions)){
			eval($furtherAction.'($section);');
		}
		
		print "</div>";
		
		// Layout
		$actionRows->add(new Block(ob_get_contents(),2),"100%", null, CENTER, TOP);
		ob_end_clean();
		$harmoni->request->forget("sectionId");
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

		print "<ul>
				<li><a href='".$url->write("furtherAction","edit_section_detailsAction::editOffering")."'>Edit section</a></li>
				<li><a href='".$url->write("furtherAction","edit_section_detailsAction::deleteOffering")."'>Delete section</a></li>
				<li><a href='".$url->write("furtherAction","edit_section_detailsAction::deleteOffering")."'>Add student</a></li>
				<li><a href='".$url->write("furtherAction","edit_section_detailsAction::deleteOffering")."'>Delete student</a></li>
				</ul>";
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