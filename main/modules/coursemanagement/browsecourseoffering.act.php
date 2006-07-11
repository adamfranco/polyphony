<?php
/**
 * @package concerto.modules.asset
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: browsecourseoffering.act.php,v 1.8 2006/07/11 15:00:12 jwlee100 Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(HARMONI."/utilities/StatusStars.class.php");

/**
 * 
 * 
 * @package concerto.modules.asset
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: browsecourseoffering.act.php,v 1.8 2006/07/11 15:00:12 jwlee100 Exp $
 */
class browsecourseofferingAction
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
		// Check that the user can create an asset here.
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		
		return $authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.add_children"),
			$idManager->getId("edu.middlebury.coursemanagement")
		);
	}
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to create a SlideShow in this <em>Exhibition</em>.");
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return _("List of course offerings.");
	}
		
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$harmoni =& Harmoni::instance();
		$actionRows =& $this->getActionRows();
		$harmoni->request->startNamespace("browse-coursesections");

		$courseManagementManager =& Services::getService("CourseManagement");
		$canonicalCourseIterator =& $courseManagementManager->getCanonicalCourses();
		
		print "<p><font size=+1><b>Below is a listing of all the course offerings in the database.</font></b></p>";
		print "<p><b>Current list of course offerings: </b>";
		print "\n<table border=1>";
		
		print "\n\t<tr>";
		print "\n\t<td>";
		print "Title: ";
		print "\n\t<td>";
		print "Number: ";
		print "\n\t<td>";
		print "Description: ";
		print "\n\t<td>";
		print "Offering type: ";
		print "\n\t<td>";
		print "Offering status: ";
		print "\n\t</tr>";
		
		while ($canonicalCourseIterator->hasNext()) {
		  	$canonicalCourse =& $canonicalCourseIterator->next();
		  	$title = $canonicalCourse->getTitle();
	  		$number = $canonicalCourse->getNumber();
	  		$description = $canonicalCourse->getDescription();
	  		$courseType = $canonicalCourse->getCourseType();
	  		$courseKeyword = $courseType->getKeyword();
	  		$courseStatusType = $canonicalCourse->getStatus();
	  		$courseStatusKeyword = $courseStatusType->getKeyword();
	  		$credits = $canonicalCourse->getCredits();
			
			$courseOfferingIterator =& $canonicalCourse->getCourseOfferings();
			while ($courseOfferingIterator->hasNext()) {
				$courseOffering =& $courseOfferingIterator->next();
				$title = $courseOffering->getTitle();
	  			$number = $courseOffering->getNumber();
	  			$description = $courseOffering->getDescription();
	  			$offeringType = $courseOffering->getOfferingType();
	  			$offeringKeyword = $offeringType->getKeyword();
	  			$offeringStatusType = $courseOffering->getStatus();
	  			$offeringStatusKeyword = $offeringStatusType->getKeyword();
	  			
	  			print "\n\t<tr>";
				print "\n\t<td>";
				print $title;
				print "\n\t<td>";
				print $number;
				print "\n\t<td>";
				print $description;
				print "\n\t<td>";
				print $offeringKeyword;
				print "\n\t<td>";
				print $offeringStatusKeyword;
				print "</tr>";
			}			
		}
		print "</table>";
		
		$actionRows->add(new Block(ob_get_contents(), STANDARD_BLOCK), "100%", null, LEFT, CENTER);
		ob_end_clean();
		
		$actionRows =& $this->getActionRows();
	}
}

?>