<?php

/**
 * @package polyphony.modules.coursemanagement
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: searchcourseoffering.act.php,v 1.7 2006/07/20 16:36:15 jwlee100 Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(HARMONI."GUIManager/Components/Blank.class.php");


class searchcourseofferingAction 
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
		return dgettext("polyphony", "Search Course Offerings");
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
		print ("<p align='center'><b>Search for a course offering by the following criteria").": </b></p>";
		print "<form action='$self' method='post'>
			<div>
			Title: <input type='text' name='search_title'>
			<br>Number: <input type='text' name='search_number'>";
			
		print "<br>Course Offering Type: <select name='search_type'>";
		print "<option value='' selected='selected'>";
		
		$typename = "offer";	
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
		
		print "<br>Course Offering Status Type<select name='search_status'>";
		print "<option value='' selected='selected'>";
		
		$typename = "offer_stat";	
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
		
		/*
		print "<br>Term: <select name='search_term'>";
		print "<option value='' selected='selected'>";
		
		$dbHandler =& Services::getService("DBHandler");
		$query=& new SelectQuery;
		$query->addTable('cm_term');
		$query->addColumn('id');
		$keyword = $query->addColumn('keyword');
		$res =& $dbHandler->query($query);
		while($res->hasMoreRows()){
			$row = $res->getCurrentRow();
			$res->advanceRow();
			print "<option value='".$keyword."'></option>";
		}
		
		print "\n\t</select>";
		*/
		
		print "\n\t<p><input type='submit' value='"._("Search!")."' />";
		//print "\n\t<a href='".$harmoni->request->quickURL()."'>";
		
		print "\n</p>\n</div></form>";
		print "\n  <p align='center'>Search may take a few minutes</p>";
		
		$actionRows->add(new Block(ob_get_contents(), STANDARD_BLOCK), "100%", null, LEFT, CENTER);
		ob_end_clean();
		
		$searchTitle = RequestContext::value('search_title');
		$searchNumber = RequestContext::value('search_number');
		$searchType = RequestContext::value('search_type');
		$searchStatus = RequestContext::value('search_status');
		
		if ($searchTitle != "" || $searchNumber != "" || $searchType != "" || $searchStatus != "") {
			$pageRows->add(new Heading("Course offering search results", STANDARD_BLOCK), "100%", null, LEFT, CENTER);
			
			ob_start();
					
			print "\n<table border=1>";
			print "\n\t<tr>";
			print "\n\t<td>";
			print "<b>Title</b>";
			print "\n\t<td>";
			print "<b>Number</b>";
			print "\n\t<td>";
			print "<b>Description</b>";
			print "\n\t<td>";
			print "<b>Course Offering Type</b>";
			print "\n\t<td>";
			print "<b>Course Offering Status</b>";
			print "\n\t</tr>";
			$canonicalCourseIterator = $cmm->getCanonicalCourses();
			while ($canonicalCourseIterator->hasNext()) {
				$canonicalCourse = $canonicalCourseIterator->next();
				$courseOfferingIterator = $canonicalCourse->getCourseOfferings();
				while ($courseOfferingIterator->hasNext()) {
					$courseOffering =& $courseOfferingIterator->next();
					$title = $courseOffering->getTitle();
	  				$number = $courseOffering->getNumber();
	  				$oType = $courseOffering->getOfferingType();
	  				$offeringType = $oType->getKeyword();
	  				$offeringStatusType = $courseOffering->getStatus();
	  				$offeringStatus = $offeringStatusType->getKeyword();
					if (($searchTitle == $title || $searchTitle == "") && 
						($searchNumber == "" || $searchNumber == $number) &&
						($searchType == $offeringType || $searchType == "") && 
						($searchStatus == "" || $searchStatus == $offeringStatus)) 		
					{
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
						print $offeringType;
						print "<td>";
						print $offeringStatus;
						print "</tr>";
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