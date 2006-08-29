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
* @version $Id: createcoursesection.act.php,v 1.13 2006/08/29 20:40:12 jwlee100 Exp $
*/

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(HARMONI."GUIManager/Components/Blank.class.php");

class createcoursesectionAction
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
		return _("Add or remove a course section.");
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
		$cmm =& Services::getService("CourseManagement");
		$harmoni =& Harmoni::instance();
		
		$harmoni->request->startNamespace("polyphony-agents");
		$harmoni->request->passthrough("courseId");
		
		$courseIdString = $harmoni->request->get("courseId");
		$courseId =& $idManager->getId($courseIdString);
		
		$cm =& Services::getService("CourseManagement");
		
		$offering =& $cmm->getCourseOffering($courseId);
				
		// Process any changes and add or remove courses as necessary
		if (RequestContext::value("sectionType") && RequestContext::value("sectionStatus") &&
			RequestContext::value("sectionLocation"))
			$this->addSection($offering, RequestContext::value("sectionType"), RequestContext::value("sectionStatus"),
							  RequestContext::value("sectionLocation"));
			
		// Print out the add form and course list
		$actionRows =& $this->getActionRows();
		
		$actionRows->add(new Heading(_("Add or remove courses."), 2), "100%", null, LEFT, CENTER);
		
		$actionRows->add($this->getAddForm($offering), "100%", null, LEFT, CENTER);
		
		$harmoni->request->endNamespace();

		textdomain($defaultTextDomain);
	}

	/***************************FUNCTIONS***************************************/

	/*********************************************************
	* The form to add information for adding course sections
    *********************************************************/
		

	function &getAddForm(&$offering) {
		$harmoni =& Harmoni::instance();
		
		$cmm =& Services::getService("CourseManagement");
		$idManager =& Services::getService("Id");
		$am =& Services::GetService("AgentManager");
		
		ob_start();
		
		$offeringName = $offering->getDisplayName();
		$offeringId =& $offering->getId();
		$offeringIdString = $offeringId->getIdString();
		
		print _("<h3>".$offeringName."</h3>")."";
		print _("<h4>Please enter the following information to add a course section in ".$offeringName.".</h4>")."";
		
		// Search header
		$self = $harmoni->request->quickURL("coursemanagement", "createcoursesection", 
			array("sectionType", "sectionStatus", "sectionLocation"));
		
		$last_type = $harmoni->request->get("sectionType");
		$section_type = RequestContext::name("sectionType");
		$last_status = $harmoni->request->get("sectionStatus");
		$section_status = RequestContext::name("sectionStatus");
		$last_location = $harmoni->request->get("sectionLocation");
		$section_location = RequestContext::name("sectionLocation");
		
		print "<form action='$self' method='post'>
			<div>
			<p>Section Type: <br/><input type='text' name='$section_type' value='$last_type' /></p>
			<p>Section Status: <br/><input type='text' name='$section_status' value='$last_type' /></p>
			<p>Section Location: <br/><input type='text' name='$section_location' value='$last_location' /></p>";
		
		print "\n\t<input type='submit' value='"._("Add")."' />";
		print "\n\t<a href='".$harmoni->request->quickURL()."'>";
		print "<input type='button' value='"._("Clear")."' /></a>";
		print "\n</div>\n</form>\n";
		
		$link = $harmoni->request->quickURL("coursemanagement", "edit_offering_details", 
											array("courseId"=>$offeringIdString));
		print _("<h4><a href='$link'>Click here to return to offering details.</a></h4>")."";
		
		$output =& new Block(ob_get_clean(), STANDARD_BLOCK);
		return $output;
	}
	
	// Add the course section
	function addSection(&$offering, $type, $status, $location) {
					     
		$actionRows =& $this->getActionRows();
		$pageRows =& new Container(new YLayout(), OTHER, 1);
		$harmoni =& Harmoni::instance();
		
		$cmm =& Services::getService("CourseManagement");
		$idManager =& Services::getService("Id");
		
		$everyoneId =& $idManager->getId("edu.middlebury.agents.everyone");
		$usersId =& $idManager->getId("edu.middlebury.agents.users");
		
		$sectionType =& new Type("CourseManagement", "edu.middlebury", $type);
		$sectionStatus =& new Type("CourseManagement", "edu.middlebury", $status);
		
		$offering->createCourseSection($offering->getTitle(), $offering->getNumber(), $offering->getDescription(),
									   $sectionType, $sectionStatus, $sectionLocation);
	}
}