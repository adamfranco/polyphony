<?php

/**
 * @package polyphony.modules.coursemanagement
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: searchcanonicalcourses.act.php,v 1.1 2006/07/18 20:24:59 jwlee100 Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(HARMONI."GUIManager/Components/Blank.class.php");


class suck_it_upAction 
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
		return dgettext("polyphony", "Refresh Canonical Courses by Term");
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

		$agentManager =& Services::getService("Agent");
		$idManager = Services::getService("Id");
		$cm =& Services::getService("CourseManagement");
		
		
		
		
		
		/*********************************************************
		 * the select menu
		 *********************************************************/
		// Users header
		//$actionRows->add(new Heading(_("Select Term"), 2), "100%", null, LEFT, CENTER);
		
		ob_start();
		$self = $harmoni->request->quickURL();
		
		$lastTerm = $harmoni->request->get("term_name");
		$term_name = RequestContext::name("term_name");
		//$search_type_name = RequestContext::name("search_type");
		print _("<p align='center'>Select a Term").": </p>";
		print <<<END
		<form action='$self' method='post'>
			<div>
		
			<p align='center'><select name='$term_name'>
		END;
		
		$searchTypes =& $agentManager->getAgentSearchTypes();
		
		// harmoni->requestContext::$type;
		$classId =& $idManager->getId("OU=Classes,OU=Groups,DC=middlebury,DC=edu ");
		$classes =& $agentManager->getGroup($classId);
		$terms =& $classes->getGroups(false);
	
		while ($terms->hasNext()) {
			$termGroup =& $terms->next();
			$termName = $termGroup->getDisplayName();
			$term = $this->_getTerm($termName);
		
			$id =& $term->getId();
			$idString = $id->getIdString();
			print "\n\t\t<option value='".$idString."'";
			if ($harmoni->request->get('type') == $idString) {
				print " selected='selected'";
			}
			print ">".$term->getDisplayName()."</option>";
		}
		
		print "\n\t</select>";
		print "\n\t<input type='submit' value='"._("Suck!")."' />";
		//print "\n\t<a href='".$harmoni->request->quickURL()."'>";
		print "\n</p>\n</div></form>";
		print "\n  <p align='center'>Sucking may take a few minutes</p>";
		
		$actionRows->add(new Block(ob_get_contents(), STANDARD_BLOCK), "100%", null, LEFT, CENTER);
		ob_end_clean();
		
		
		/*********************************************************
		 * the agent search results
		 *********************************************************/
		ob_start();
		$values = $wizard->getAllValues();
		printpre($values);
					
		$courseType =& $courseManager->_indexToType($values['namedescstep']['type'],'can');
		$statusType =& $courseManager->_indexToType($values['namedescstep']['statusType'],'can_stat');
			
		print "\n<table border=1>";
		print "\n\t<tr>";
		print "\n\t<td>";
		print "<b>Title</b>";
		print "\n\t<td>";
		print "<b>Number</b>";
		print "\n\t<td>";
		print "<b>Description</b>";
		print "\n\t<td>";
		print "<b>Course Type</b>";
		print "\n\t<td>";
		print "<b>Course Status</b>";
		print "\n\t<td>";
		print "<b>Credits</b>";
		print "\n\t</tr>";
		$canonicalCourseIterator = $courseManager->getCanonicalCourses();
		while ($canonicalCourseIterator->hasNext()) {
			$canonicalCourse = $canonicalCourseIterator->next();
			$title = $canonicalCourse->getTitle();
	  		$number = $canonicalCourse->getNumber();
	  		$courseType = $canonicalCourse->getCourseType();
	  		$courseKeyword = $courseType->getKeyword();
	  		$courseStatusType = $canonicalCourse->getStatus();
	  		$courseStatusKeyword = $courseStatusType->getKeyword();
			if ($values['namedescstep']['title'] == $title ||
				$values['namedescstep']['number'] == $number ||
				$values['namedescstep']['type'] == $courseKeyword ||
				$values['namedescstep']['statusType'] == $courseStatusKeyword) {
				$description = $canonicalCourse->getDescription();
				$credits = $canonicalCourse->getCredits();
				
				print "<tr>";
				print "<td>";
				print $title;
				print "<td>";
				print $number;
				print "<td>";
				print $description;
				print "<td>";
				print $courseKeyword;
				print "<td>";
				print $courseStatusKeyword;
				print "<td>";
				print $credits;
				print "</tr>";
			}
		}
		if ($termIdString = RequestContext::value('term_name') ) {
			$classId =& $idManager->getId("OU=Classes,OU=Groups,DC=middlebury,DC=edu ");
			$classes =& $agentManager->getGroup($classId);
			$terms =& $classes->getGroups(false);
			while($terms->hasNext()){
				$termGroup =& $terms->next();
				$termName = $termGroup->getDisplayName();
				$term = $this->_getTerm($termName);
				$id=& $term->getId();
		
			if ($termIdString==$id->getIdString()) {
				break;
			}
		}
	
				
 		$pageRows->add(new Heading(_("Courses Sucked from ".$term->getDisplayName().""), 2), "100%", null, LEFT, CENTER);

		ob_start();	
		$last = "";
		$sections =& $termGroup->getGroups(false);
			
		while ($sections->hasNext()) {
			$section =& $sections->next();
			$sectionName = $section->getDisplayName();	
			if (substr($sectionName,0,4)=="phed") {
				continue;	
			}
			if(substr($sectionName,0,strlen($sectionName)-5)!=$last){
				$last=substr($sectionName,0,strlen($sectionName)-5);
				$canonicalCourseId = $this->_getCanonicalCourse($sectionName);
			}
		}
	
	
		print "Success!";
	
		// Create a layout for this group using the GroupPrinter
		
		$groupLayout =& new Block(ob_get_contents(), STANDARD_BLOCK);
		ob_end_clean();
			
		$pageRows->add($groupLayout, "100%", null, LEFT, CENTER);	
		//}
		
		// In order to preserve proper nesting on the HTML output, the checkboxes
		// are all in the pagerows layout instead of actionrows.
 		$actionRows->add($pageRows, null, null,CENTER, CENTER);	
 		
 		textdomain($defaultTextDomain);
	}
}
?>