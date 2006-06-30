<?php
/**
 * @package concerto.modules.asset
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: browsecanonicalcourses.act.php,v 1.1 2006/06/30 19:38:43 jwlee100 Exp $
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
 * @version $Id: browsecanonicalcourses.act.php,v 1.1 2006/06/30 19:38:43 jwlee100 Exp $
 */
class browsecanonicalcoursesAction
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
		$idManager =& Services::getService("Id");
		$courseManagementManager =& Services::getService("CourseManagement");
		$canonicalCourseA =& $courseManagementManager->getCanonicalCourses("edu.middlebury.coursemanagement");
		return _("Add a SlideShow to the")." <em>".$canonicalCourseA."</em> "._("Exhibition");
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
		$harmoni->request->passthrough("exhibition_id");
		
		$actionRows =& $this->getActionRows();
		
		$idManager =& Services::getService("Id");
		$exhibitionAssetId =& $idManager->getId(RequestContext::value('exhibition_id'));
		
		$cacheName = 'add_slideshow_wizard_'.$exhibitionAssetId->getIdString();
		
		$this->runWizard ( $cacheName, $actionRows );
	}
		
	/**
	 * Create a new Wizard for this action. Caching of this Wizard is handled by
	 * {@link getWizard()} and does not need to be implemented here.
	 * 
	 * @return object Wizard
	 * @access public
	 * @since 4/28/05
	 */
	
	function browse_canonicalcourse() {
	  	$harmoni =& Harmoni::instance();
		$courseManagementManager =& Services::getService("CourseManagement");
		$canonicalCourseIterator =& $courseManagementManager->getCanonicalCourses();
		
		print "\n<table>";
		while ($canonicalCourseIterator->hasNext()) {
		  	$canonicalCourse =& $canonicalCourseIterator->next();
		  	$id =& $canonicalCourse->getId();
		  	print "\n\t<tr>";
		  	attributesPrinter($canonicalCourse, $id);
			print "</tr>";			
		}
		print "</table>";			
	}
	
	/**
	 * Return the URL that this action should return to when completed.
	 * 
	 * @return string
	 * @access public
	 * @since 4/28/05
	 */
	function getReturnUrl () {
		$harmoni =& Harmoni::instance();
		$url =& $harmoni->request->mkURL("coursemanagementmanager");
		return $url->write();
	}
	
	function attributesPrinter(&$canonicalCourse, $id) {
	  	$title = $canonicalCourse->getTitle($id);
	  	$number = $canonicalCourse->getNumber($id);
	  	$description = $canonicalCourse->getNumber($id);
	  	$type = $canonicalCourse->getType();
	  	$statusType = $canonicalCourse->getCourseStatusType();
	  	
		print "\n\t<td>";
		print "Title: ";
		print $title;
		print "\n\t<td>";
		print "\n\t<td>";
		print "Number: ";
		print $number;
		print "\n\t<td>";
		print "Description: ";
		print $description;
		print "\n\t<td>";
		print "Type: ";
		print $type;
		print "\n\t<td>";
		print "Status Type: ";
		print $statusType;
	}
}

?>