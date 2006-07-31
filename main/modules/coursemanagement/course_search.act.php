<?php

/**
* @package polyphony.modules.coursemanagement
*
* @copyright Copyright &copy; 2006, Middlebury College
* @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
*
* @version $Id: course_search.act.php,v 1.2 2006/07/31 14:57:54 sporktim Exp $
*/

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(HARMONI."GUIManager/Components/Blank.class.php");
require_once(POLYPHONY."/main/modules/agents/edit_agent_details.act.php");


class course_searchAction
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
		return dgettext("polyphony", "Search for Courses");
	}

	/**
	* Build the content for this action
	*
	* @return void
	* @access public
	* @since 4/26/05
	*/
	function buildContent () {
		$cm =& Services::getService("CourseManagement");

		$defaultTextDomain = textdomain("polyphony");
		
		$actionRows =& $this->getActionRows();
		$pageRows =& new Container(new YLayout(), OTHER, 1);
		$harmoni =& Harmoni::instance();


		$searchNumber = RequestContext::value('search_number');
		$searchTerm = RequestContext::value('search_term');
		
		if(is_null($searchNumber)){
			$searchNumber="";
		}
		if(is_null($searchTerm)){
			$searchTerm="";
		}
	

		ob_start();
		
			
		
			
		
		$self = $harmoni->request->quickURL();
		print ("<p align='center'><b><font size=+1>Search for courses offering by the following criteria").":
				</font></b></p>";
		print "\n\t<form action='$self' method='post'>
			\n\t<div>";

		print "<table>";
	
		
		print "\n\t<tr><td>Number: </td><td><input type='text' name='search_number' value=".$searchNumber."></td>";
	

		print "\n\t<tr><td>Term: </td><td><select name='search_term'>";
		print "\n\t<option value=''";
		
		if($searchTerm==""){
			print "selected='selected'";
		}
		
		print ">Choose a term</option>";


		//@TODO this sorting is probably pretty slow--it's multiple queries per term.
		$numOfImproperOfferingTerms=-1;
		$terms =& $cm->getTerms();
		while($terms->hasNextTerm()){
			$term =& $terms->nextTerm();
			$schedule =& $term->getSchedule();
			if($schedule->hasNextScheduleItem()){
				$item1 =& $schedule->nextScheduleItem();
				$terms2[$item1->getStart()] =&  $term;
			}else{
				$terms2[$numOfImproperOfferingTerms] =& $term;
				$numOfImproperOfferingTerms--;
			}

		}
	
		
		krsort($terms2);
		foreach($terms2 as 	$term){
			$id =& $term->getId();
			print "\n\t<option value='".$id->getIdString()."'";
			if($searchTerm==$id->getIdString()){
				print "selected='selected'";
			}
			print ">".$term->getDisplayName()."</option>";
		}
		print "\n\t</select></td></tr>";
		print "\n</table>";
	

		print "\n\t<p><input type='submit' value='"._("Search!")."' />";


		//$actionRows =& $this->getActionRows();
		$actionRows->add(new Block(ob_get_contents(),2),"100%", null, CENTER, TOP);
		ob_end_clean();

			
			

		

		if ($searchNumber != "" || $searchTerm != "")
		{
			
			
		ob_start();
			
			$pageRows->add(new Heading("Canonical course search results", STANDARD_BLOCK), "100%", null, LEFT, CENTER);
		  	ob_start();

			print "<p><h2>Search Results</h2></p>";
			
			
			$dbHandler =& Services::getService("DBHandler");
			$query=& new SelectQuery;
			$query->addTable('cm_offer');
			$query->addColumn('id');
		
			if($searchNumber!=null){
		
				$query->addWhere("number like '%".addslashes($searchNumber)."%'");
			}
			if($searchTerm!=null){
	
				$query->addWhere("fk_cm_term='".addslashes($searchTerm)."'");
			}
	
			
			$res =& $dbHandler->query($query);
			$array=array();
			$cm =& Services::getService("CourseManagement");
			$im =& Services::getService("Id");
			
	
			
			while($res->hasMoreRows()){
	
				$row = $res->getCurrentRow();
				$res->advanceRow();
				$id =& $im->getId($row['id']);
				$array[] =& $cm->getCourseOffering($id);
			}
			
			$offerings =& new HarmoniCourseOfferingIterator($array);		
			edit_agent_detailsAction::printCourseOfferings($offerings);



			$actionRows->add(new Block(ob_get_contents(),2),"100%", null, CENTER, TOP);
		ob_end_clean();

			
		}
	}



}