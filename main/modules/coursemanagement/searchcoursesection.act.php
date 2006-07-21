<?php

/**
 * @package polyphony.modules.coursemanagement
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: searchcoursesection.act.php,v 1.7 2006/07/21 15:47:51 jwlee100 Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(HARMONI."GUIManager/Components/Blank.class.php");


class searchcoursesectionAction 
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
		return dgettext("polyphony", "Search Course Sections");
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
		
		ob_start();
		$self = $harmoni->request->quickURL();
		print ("<p align='center'><b><font size=+1>Search for a course section by the following criteria").": 
				</font></b></p>";
		print "<form action='$self' method='post'>
			<div>
			Title: <input type='text' name='search_title'>
			<br>Number: <input type='text' name='search_number'>";
			
		print "<br>Course Section Type: <select name='search_type'>";
		print "<option value='' selected='selected'>Choose a course section type</option>";
		
		$typename = "section";	
		$dbHandler =& Services::getService("DBHandler");
		$query=& new SelectQuery;
		$query->addTable('cm_'.$typename."_type");
		$query->addColumn('id');
		$keyword = $query->addColumn('keyword');
		$res =& $dbHandler->query($query);
		while($res->hasMoreRows()){
			$row = $res->getCurrentRow();
			$res->advanceRow();
			print "<option value='".$row['keyword']."'>".$row['keyword']."</option>";
		}
		
		print "\n\t</select>";
		
		print "<br>Course Section Status Type<select name='search_status'>";
		print "<option value='' selected='selected'>Choose a course section status type</option>";
		
		$typename = "section_stat";	
		$dbHandler =& Services::getService("DBHandler");
		$query=& new SelectQuery;
		$query->addTable('cm_'.$typename."_type");
		$query->addColumn('id');
		$keyword = $query->addColumn('keyword');
		$res =& $dbHandler->query($query);
		while($res->hasMoreRows()){
			$row = $res->getCurrentRow();
			$res->advanceRow();
			print "<option value='".$row['keyword']."'>".$row['keyword']."</option>";
		}
		
		print "\n\t</select>";
		
		print "<br>Location: <input type='text' name='search_location'>";
		
		print "\n\t<p><input type='submit' value='"._("Search!")."' />";
		//print "\n\t<a href='".$harmoni->request->quickURL()."'>";
		
		print "\n</p>\n</div></form>";
		print "\n  <p align='center'><i>Search may take a few minutes.  Please be patient.</i></p>";
		
		print "<p><hr>";
		print "<p><a href='".$harmoni->request->quickURL("coursemanagement","createcoursesection")."'>";
		print _("Click here to create a course section.");
		print "<p><a href='".$harmoni->request->quickURL("coursemanagement","browsecoursesection")."'>";
		print _("Click here to browse through all existing course sections.");
		
		$actionRows->add(new Block(ob_get_contents(), STANDARD_BLOCK), "100%", null, LEFT, CENTER);
		ob_end_clean();
		
		$searchTitle = RequestContext::value('search_title');
		$searchNumber = RequestContext::value('search_number');
		$searchType = RequestContext::value('search_type');
		$searchStatus = RequestContext::value('search_status');
		$searchLocation = RequestContext::value('search_location');
		
		if ($searchTitle != "" || $searchNumber != "" || $searchType != "" || $searchStatus != "" ||
			$searchLocation != "") {
			$pageRows->add(new Heading("Course section search results", STANDARD_BLOCK), "100%", null, LEFT, CENTER);
				
			ob_start();
						
			print "\n<table border=1>";
			print "\n\t<tr align=center>";
			print "\n\t<td>";
			print "<b>Title</b>";
			print "\n\t<td>";
			print "<b>Number</b>";
			print "\n\t<td>";
			print "<b>Description</b>";
			print "\n\t<td>";
			print "<b>Course Section Type</b>";
			print "\n\t<td>";
			print "<b>Course Section Status</b>";
			print "\n\t<td>";
			print "<b>Location</b>";
			print "\n\t</tr>";
			$canonicalCourseIterator = $cmm->getCanonicalCourses();
			while ($canonicalCourseIterator->hasNext()) {
				$canonicalCourse = $canonicalCourseIterator->next();
				$courseOfferingIterator = $canonicalCourse->getCourseOfferings();
				while ($courseOfferingIterator->hasNext()) {
					$courseOffering =& $courseOfferingIterator->next();
					$courseSectionIterator =& $courseOffering->getCourseSections();
					while ($courseSectionIterator->hasNext()) {
						$courseSection =& $courseSectionIterator->next();
						$title = $courseSection->getTitle();
	  					$number = $courseSection->getNumber();
	  					$sType = $courseSection->getSectionType();
	  					$sectionType = $sType->getKeyword();
	  					$sectionStatusType = $courseSection->getStatus();
	  					$sectionStatus = $sectionStatusType->getKeyword();
	  					$location = $courseSection->getLocation();
						if (($searchTitle == $title || $searchTitle == "") && 
							($searchNumber == "" || $searchNumber == $number) &&
							($searchType == $sectionType || $searchType == "") && 
							($searchStatus == "" || $searchStatus == $sectionStatus) &&
							($searchLocation == "" || $searchLocation == $location)) 		
						{
							$description = $canonicalCourse->getDescription();
					
							print "<tr>";
							print "<td>";
							print $title;
							print "<td>";
							print $number;
							print "<td>";
							print $description;
							print "<td>";
							print $sectionType;
							print "<td>";
							print $sectionStatus;
							print "<td>";
							print $location;
							print "</tr>";
						}
					}
				}
			}
		
			$groupLayout =& new Block(ob_get_contents(), STANDARD_BLOCK);
			ob_end_clean();
			
			$pageRows->add($groupLayout, "100%", null, LEFT, CENTER);	
			$actionRows->add($pageRows, "100%", null, LEFT, CENTER);	
		}
	}
}