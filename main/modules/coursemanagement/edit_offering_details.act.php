<?php

/**
 * This action is the central page for viewing and modifying course offering information.
 *
 * @package polyphony.modules.coursemanagement
 *
 *
 * @since 7/28/06 
 *
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: edit_offering_details.act.php,v 1.4 2006/08/22 15:56:28 jwlee100 Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(HARMONI."GUIManager/Components/Blank.class.php");
require_once(POLYPHONY."/main/modules/coursemanagement/edit_section_details.act.php");

class edit_offering_detailsAction 
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
		$offeringIdString = $harmoni->request->get("courseId");

		$harmoni->request->endNamespace();
		
		if ($authZManager->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.modify"),
					$idManager->getId($offeringIdString)))
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
		$offeringIdString = $harmoni->request->get("courseId");
		$idManager =& Services::getService("Id");
		$offeringId =& $idManager->getId($offeringIdString);
		$cm =& Services::getService("CourseManagement");
		$offering =& $cm->getCourseOffering($offeringId);
		return dgettext("polyphony", $offering->getDisplayName());
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
		//$pageRows =& new Container(new YLayout(), OTHER, 1);
		$harmoni =& Harmoni::instance();
		
		$harmoni->request->startNamespace("polyphony-agents");
		$harmoni->request->passthrough("courseId");
		$offeringIdString = $harmoni->request->get("courseId");
		$furtherAction = $harmoni->request->get("furtherAction");

		$idManager =& Services::getService("Id");
		$offeringId =& $idManager->getId($offeringIdString);
		$cm =& Services::getService("CourseManagement");
		$offering =& $cm->getCourseOffering($offeringId);

		
		ob_start();
		
		
		print "<div style='margin-left: 15px'>";
		
		if (!$furtherAction) $furtherAction = "edit_offering_detailsAction::viewOfferingDetails";
		
		$actionFunctions = array(
			"edit_offering_detailsAction::viewOfferingDetails",
			"edit_offering_detailsAction::confirmDeleteOffering",
			"edit_offering_detailsAction::deleteOffering",
		);
			
		
		if($furtherAction && in_array($furtherAction, $actionFunctions)){
			eval($furtherAction.'($offering);');
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
	function viewOfferingDetails(&$offering){
		
		
		edit_offering_detailsAction::refreshOfferingDetails($offering);

		$offeringId =& $offering->getId();
		$offeringIdString = $offeringId->getIdString();
		
		//display offering info	
		//print "\n<h3>".$offering->getDisplayName()."</h3>";
		
		
		
		
		
		print "\n<table><tr><td>";
		
		//print "\n<table bgcolor='#AAAAAA' cellspacing='1' cellpadding='3'>";

		//print "\n\t<tr><td>";
				
		$term =& $offering->getTerm();
		print "\n<h1>".$offering->getDisplayName()." - ".$term->getDisplayName()."</h1>";

		/*
		
		print "\n\t\t<td>Display Name</td>";
		print "\n\t\t<td>";
		print $offering->getDisplayName();
		print "</td>";	
		print "\n\t</tr>";
		print "\n\t<tr>";
		print "\n\t\t<td>Number</td>";
		print "\n\t\t<td>";
		print $offering->getNumber();
		print "</td>";	
		print "\n\t</tr>";
		print "\n\t<tr>";
		print "\n\t\t<td>Term</td>";
		print "\n\t\t<td>";
		$term =& $offering->getTerm();
		print $term->getDisplayName();
		*/		
		//print "</td>";	
		//print "\n\t</tr>";			
		//print "\n</table>";
		
		
		
		
		
		print "\n</td><td>";
		

		//actions menu
		$harmoni =& Harmoni::instance();
		$url =& $harmoni->request->mkURL();

		print "<ul>
				<li><a href='".$url->write("furtherAction","edit_offering_detailsAction::editOffering")."'>Edit Offering</a></li>
				<li><a href='".$url->write("furtherAction","edit_offering_detailsAction::deleteOffering")."'>Delete Offering</a></li>
				</ul>";
		print "\n</td></tr></table>";
		
		
		$actionRows =& $this->getActionRows();
		//$actionRows->add(new Block(ob_get_contents(),2),"100%", null, CENTER, TOP);
		//ob_end_clean();

		//ob_start();
		
		//$actionRows =& $this->getActionRows();
		//$pageRows =& new Container(new YLayout(), OTHER, 1);
		
		$sections =& $offering->getCourseSections();
		
		//print "<h2>Sections:</h2>";
		
		
		while($sections->hasNextCourseSection()){
			
		
		$actionRows->add(new Block(ob_get_contents(),2),"100%", null, CENTER, TOP);
		ob_end_clean();

		ob_start();
		
		$actionRows =& $this->getActionRows();
		$pageRows =& new Container(new YLayout(), OTHER, 1);
		
		
		$section =& $sections->nextCourseSection();
		
		edit_section_detailsAction::viewSectionDetails($section);
		
		}
		

		
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
		print "\n<a href='".$harmoni->request->quickURL("coursemanagement","edit_offering_details", array("courseId"=>$id->getIdString()))."'>";
		print $offering->getDisplayName()."</a>";		
		print "\n - <a href=\"Javascript:alert('"._("Id:").'\n\t'.addslashes($id->getIdString())."')\">"._("Id")."</a>";
		print "</td>";
		//print "\n\t\t<td>";
		//$term =& $offering->getTerm();
		//print $term->getDisplayName();
		//print "</td>";		
		print "\n\t</tr>";
		
		
		
	}
	
	function refreshOfferingDetails(&$offering){
		
		
		$term =& $offering->getTerm();
		$termName = $term->getDisplayName();
		$year = substr($termName,strlen($termName)-2,2);
		$season = substr($termName,0,strlen($termName)-5);
		
		//print "Hmmm?";
		//print $termName;
		//print $season;
		//print $year;
		//print "Hmmm?";
	
		
		if($season === "Winter"){
			$seasonChar = 'w';
		}elseif($season === "Spring"){
			$seasonChar = 's';
		}elseif($season === "Summer"){
			$seasonChar = 'l';
		}elseif($season === "Fall"){
			$seasonChar = 'f';
		}else{
			//print "*'".$season."'*?";
		}
		
		$type =& new Type("EnrollmentRecordType","edu.middlebury","LDAP");
		
		$distinguishedNameBase = "-".$seasonChar.$year.",OU=".$season.$year.",OU=Classes,OU=Groups,DC=middlebury,DC=edu";
		
		//print $distinguishedNameBase;
		
		$sections =& $offering->getCourseSections();
		
		$am =& Services::getService("AgentManager");
		$im =& Services::getService("Id");
		while($sections->hasNextCourseSection()){
			$section = $sections->nextCourseSection();
			
			
			//print "'CN=".$section->getNumber().$distinguishedNameBase."'";
			
			$id = $im->getId("CN=".$section->getNumber().$distinguishedNameBase);
			$group =& $am->getGroup($id);
			
			$agentIterator =& $group->getMembers(false);
			
			
			while($agentIterator->hasNext()){
				$agent = $agentIterator->nextAgent();
				$agentId =& $agent->getId(); 
				
				
				$dbManager =& Services::getService("DatabaseManager");
			$query=& new SelectQuery;
			$query->addTable('cm_enroll');
			$query->addWhere("fk_cm_section='".addslashes($section->_id->getIdString())."'");
			$query->addWhere("fk_student_id='".addslashes($agentId->getIdString())."'");
			//I don't need Id, but I need to select something for the query to work
			$query->addColumn('id');//@TODO select count instead
			$res=& $dbManager->query($query);
			if($res->getNumberOfRows()==0){
				$section->addStudent($agentId, $p = new Type("EnrollmentStatusType","edu.middlebury","LDAP"));
			}
				
			
				//$section->addStudent($agentId,$type);
			}
			
		}
		
		
		
		
	}
}