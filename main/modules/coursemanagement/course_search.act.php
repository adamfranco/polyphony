<?php

/**
* @package polyphony.modules.coursemanagement
*
* @copyright Copyright &copy; 2006, Middlebury College
* @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
*
* @version $Id: course_search.act.php,v 1.9 2006/08/21 19:34:52 jwlee100 Exp $
*/

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(HARMONI."GUIManager/Components/Blank.class.php");
require_once(POLYPHONY."/main/modules/agents/edit_agent_details.act.php");

require_once(HARMONI."oki2/agent/AgentSearches/ClassTokenSearch.class.php");


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

		if(is_null($searchNumber)){
			$searchNumber="";
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
		print "selected='selected'";
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
			/*if($searchTerm==$id->getIdString()){
				print "selected='selected'";
			}*/
			print ">".$term->getDisplayName()."</option>";
		}
		print "\n\t</select></td></tr>";

		print "\n</table>";


		print "\n\t<p><input type='submit' value='"._("Search!")."' />";


		//$actionRows =& $this->getActionRows();
		$actionRows->add(new Block(ob_get_contents(),2),"100%", null, CENTER, TOP);
		ob_end_clean();

		$searchTerm = RequestContext::value('search_term');
		
		if(is_null($searchTerm)){
			$searchTerm="";
		}

		/*

		if ($search_criteria = $harmoni->request->get('search_criteria')) {
		//$typeParts = explode("::", @html_entity_decode($search_type, ENT_COMPAT, 'UTF-8'));



		$searchType =& new HarmoniType("Agent & Group Search", "edu.middlebury.harmoni", "TokenSearch");
		//$searchType =& new HarmoniType("Agent & Group Search", "edu.middlebury.harmoni", "WildcardSearch");
		$string=	"*".$search_criteria."*";
		$agents =& $agentManager->getAgentsBySearch($string, $searchType);
		print "search: " . $search_criteria;



		while ($agents->hasNext()) {
		$agent =& $agents->next();
		$id =& $agent->getId();




		$harmoni->history->markReturnURL("polyphony/agents/edit_agent_details");

		print "\n<p align='center'><a href='".$harmoni->request->quickURL("agents","edit_agent_details", array("agentId"=>$id->getIdString()))."'>";
		print "\n".$agent->getDisplayName()."</a>";
		print "\n - <a href=\"Javascript:alert('"._("Id:").'\n\t'.addslashes($id->getIdString())."')\">Id</a></p>";
		}
		print "\n</div>";

		$actionRows->add(new Block(ob_get_contents(), STANDARD_BLOCK), "100%", null, LEFT, CENTER);
		ob_end_clean();


		}

		*/



		if ($searchNumber != "" || $searchTerm != "")
		{


			ob_start();

			$pageRows->add(new Heading("Canonical course search results", STANDARD_BLOCK), "100%", null, LEFT, CENTER);
			ob_start();

			print "<p><h2>Search Results</h2></p>";



			$searchType =& new ClassTokenSearch();

			$string = "*".$searchNumber."*";
			$DNs =& $searchType->getClassDNsBySearch($string);
			print "search: " . $searchNumber;

			/*

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


			$res =& $dbHandler->query($query);*/


			$sections=array();
			$cm =& Services::getService("CourseManagement");
			$im =& Services::getService("Id");





			foreach($DNs as $idString) {



				if(substr($idString,strlen($idString)-42,42)!=",OU=Classes,OU=Groups,DC=middlebury,DC=edu"){
					continue;
				}




				$len = 0;
				while($idString[$len+3]!=","&&$len<strlen($idString)){
					$len++;
				}
				$name = substr($idString,3,$len);

				

				/*
				if(!$term somthings $searchTerm){
				//continue
				}
				*/

				//filter out semesters


				if(substr($name,strlen($name)-4,1)!="-"){
					continue;
				}

				//filter out gym--actually, that's not fair, is it?
				//if(substr($name,0,4)=="phed"){
				//
				//	continue;
				//}

				$sections[]=& suck_by_agentAction::_figureOut($name,$agentId = null);



			}

			$offerings = array();

			$termId = null;
			if($searchTerm != ""){
					//$term = substr($name, strlen($name)-3,3);
					$idManager =& Services::getService("Id");
					// $term =& $cm->getTerm($idManager->getId($searchTerm));
					// $termId =& $term->getId();
					$termId =& $idManager->getId($searchTerm);
			}
			
			foreach($sections as $section){

				$offering =& $section->getCourseOffering();
				
				$term2 =& $offering->getTerm();
				$term2Id =& $term2->getId();
				if(!is_null($termId)&&!$termId->isEqual($term2Id)){
					continue;
				}
				
				
				
				$offeringId =& $offering->getId();
				$offerings[$offeringId->getIdString()] =& $offering;

			}
			/*

			foreach($DNs as $DN){





			$id =& $im->getId($row['id']);
			$array[] =& $cm->getCourseOffering($id);
			}*/

			$iter =& new HarmoniCourseOfferingIterator($offerings);
			edit_agent_detailsAction::printCourseOfferings($iter);



			$actionRows->add(new Block(ob_get_contents(),2),"100%", null, CENTER, TOP);
			ob_end_clean();


		}
	}



}