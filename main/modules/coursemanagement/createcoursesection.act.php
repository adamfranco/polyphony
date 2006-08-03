<?php
/**
 * @package concerto.modules.asset
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: createcoursesection.act.php,v 1.12 2006/08/03 20:32:08 jwlee100 Exp $
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
 * @version $Id: createcoursesection.act.php,v 1.12 2006/08/03 20:32:08 jwlee100 Exp $
 */
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
		return _("Create a course section.");
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
		$cacheName = "createCourseSectionWizard";
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
	
	function &createWizard () {
		$harmoni =& Harmoni::instance();
		$courseManager =& Services::getService("CourseManagement");
		$canonicalCourseIterator =& $courseManager->getCanonicalCourses();
		
		// Instantiate the wizard, then add our steps.
		$wizard =& SimpleStepWizard::withDefaultLayout();
		
		// :: Name and Description ::
		$step =& $wizard->addStep("namedescstep", new WizardStep());
		$step->setDisplayName(_("Please enter the information about a course offering:"));
		
		$select =& new WSelectList();
		$dbHandler =& Services::getService("DBHandler");
		$query=& new SelectQuery;
		$query->addTable('cm_offer');
		$query->addColumn('id');
		$query->addColumn('title');
		$query->addColumn('number');
		$query->addOrderBy('number');
		$res=& $dbHandler->query($query);
		//$select->addOption("1","There");
		while($res->hasMoreRows()){
			$row = $res->getCurrentRow();
			$res->advanceRow();
			$select->addOption($row['id'],$row['number']." ".$row['title']);
		}
		
		
		//$select->addOption("2","Here");
		$canonicalCourse =& $step->addComponent("courseid", $select);
		
					
		
		
		$descriptionProp =& $step->addComponent("description", WTextArea::withRowsAndColumns(10,30));
		
		
		$select =& new WSelectList();
	
		$dbHandler =& Services::getService("DBHandler");
		$query=& new SelectQuery;
		$query->addTable('cm_term');
		$query->addColumn('id');
		$query->addColumn('name');
		$res=& $dbHandler->query($query);
		while($res->hasMoreRows()){
			$row = $res->getCurrentRow();
			$res->advanceRow();
			$select->addOption($row['id'],$row['name']);
		}
		$termProp =& $step->addComponent("term", $select);
		
		
		$select =& new WSelectList();
		$typename = "section";	
		$dbHandler =& Services::getService("DBHandler");
		$query=& new SelectQuery;
		$query->addTable('cm_'.$typename."_type");
		$query->addColumn('id');
		$query->addColumn('keyword');
		$res=& $dbHandler->query($query);
		while($res->hasMoreRows()){
			$row = $res->getCurrentRow();
			$res->advanceRow();
			$select->addOption($row['id'],$row['keyword']);
		}
		$typeProp =& $step->addComponent("type", $select);
		
		
		
		$select =& new WSelectList();
		$typename = "section_stat";	
		$dbHandler =& Services::getService("DBHandler");
		$query=& new SelectQuery;
		$query->addTable('cm_'.$typename."_type");
		$query->addColumn('id');
		$query->addColumn('keyword');
		$res=& $dbHandler->query($query);
		while($res->hasMoreRows()){
			$row = $res->getCurrentRow();
			$res->advanceRow();
			$select->addOption($row['id'],$row['keyword']);
		}
		$statusTypeProp =& $step->addComponent("statusType", $select);
		
		// Text box for location
		$locationProp =& $step->addComponent("location", new WTextField());
		$locationProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
		$locationProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		
		$actionRows =& $this->getActionRows();
		$pageRows =& new Container(new YLayout(), OTHER, 1);
		$harmoni =& Harmoni::instance();
		
		$cmm =& Services::getService("CourseManagement");
		$idManager =& Services::getService("Id");
		$am =& Services::GetService("AgentManager");
		
		$courseIdString = RequestContext::value("courseId");
		$courseId = $idManager->getId($courseIdString);
		$courseSection = $cmm->getCourseSection($courseId);
				
		ob_start();
				
		print "\n<table border=1>";
		print "\n<tr>Name of Student</tr>";
		
		$agentType =& new Type("CourseManagement", "edu.middlebury", "student");
		$properties =& new HarmoniProperties($propertiesType);
		$agent1 =& $am->createAgent("Gladius", $agentType, $properties);
		$enrollStatType1 =& new Type("CourseManagement", "edu.middlebury", "attending", "");
		
		$roster =& $courseSection->getRoster();
		while ($roster->hasNext()) {
			$er =& $iter->next();
			$agent =& $am->getAgent($er->getStudent());
			
			$agentName = $agent->getDisplayName();
			print "<tr>";
			print $agentName;
			print "</tr>";
		}
		
		$groupLayout =& new Block(ob_get_contents(), STANDARD_BLOCK);
		print "</table>";
		ob_end_clean();
		
		$pageRows->add($groupLayout, "100%", null, LEFT, CENTER);	
		$actionRows->add($pageRows, "100%", null, LEFT, CENTER);
		
		
		// Create the step text
		ob_start();
		print "\n<font size=+2><h2>"._("Course Section")."</h2></font>";
		//print "\n<h2>"._("Title")."</h2>";
		//print "\n"._("The title of this <em>course section</em>: ");
		//print "\n<br />[[title]]";
		//print "\n<h2>"._("Number")."</h2>";
		//print "\n"._("The number of this <em>course section</em>: ");
		//print "\n<br />[[number]]";
		print "\n<h2>"._("Course Section")."</h2>";
		print "\n"._("The course for which you want to make a <em>section</em>: ");
		print "\n<br />[[courseid]]";
		//print "\n<h2>"._("Description")."</h2>";
		//print "\n"._("The description of this <em>course section</em>: ");
		//print "\n<br />[[description]]";
		print "\n<h2>"._("Term")."</h2>";
		print "\n"._("The term of this <em>course section</em>: ");
		print "\n<br />[[term]]";
		print "\n<h2>"._("Section Type")."</h2>";
		print "\n"._("The type of this <em>course section</em>: ");
		print "\n<br />[[type]]";
		print "\n<h2>"._("Status type")."</h2>";
		print "\n"._("The status type of this <em>course sectiong</em>: ");
		print "\n<br />[[statusType]]";
		print "\n<h2>"._("Location")."</h2>";
		print "\n"._("The location of this <em>course section</em>: ");
		print "\n<br />[[location]]";
		print "\n<div style='width: 400px'> &nbsp; </div>";
		
		print "<p><hr>";
		print "<p><font size=+1><a href='".$harmoni->request->quickURL("coursemanagement","browsecoursesection")."'>";
		print _("Click here to browse through all existing course sections.</font>");
		print "<p><font size=+1><a href='".$harmoni->request->quickURL("coursemanagement","searchcoursesection")."'>";
		print _("Click here to search for course sections.</font>");
		
		$step->setContent(ob_get_contents());
		ob_end_clean();
		
		return $wizard;
	}
		
	/**
	 * Save our results. Tearing down and unsetting the Wizard is handled by
	 * in {@link runWizard()} and does not need to be implemented here.
	 * 
	 * @param string $cacheName
	 * @return boolean TRUE if save was successful and tear-down/cleanup of the
	 *		Wizard should ensue.
	 * @access public
	 * @since 4/28/05
	 */
	function saveWizard ( $cacheName ) {
		$wizard =& $this->getWizard($cacheName);
		
		// Make sure we have a valid Repository
		$courseManager =& Services::getService("CourseManagement");
		$idManager =& Services::getService("Id");
		$courseManagementId =& $idManager->getId("edu.middlebury.coursemanagement");

		
		// First, verify that we chose a parent that we can add children to.
		$authZ =& Services::getService("AuthZ");
		if ($authZ->isUserAuthorized(
						$idManager->getId("edu.middlebury.authorization.add_children"), 
						$courseManagementId))
		{
			
			$values = $wizard->getAllValues();
			printpre($values);
			
			$termId =& $idManager->getId($values['namedescstep']['term']); 
          	
          	
          	
          	
          	/*
			$courseType =& new Type("CourseManagement", "edu.middlebury", $values['namedescstep']['type']);
			$statusType =& new Type("CourseManagement", "edu.middlebury", $values['namedescstep']['statusType']);
			$courseGradeType = new Type("CourseManagement", "edu.middlebury", $values['namedescstep']['courseGrade']);
			*/
			$sectionType =& $courseManager->_indexToType($values['namedescstep']['type'],'section');
			$statusType =& $courseManager->_indexToType($values['namedescstep']['statusType'],'section_stat');
			$courseGradeType =& $courseManager->_indexToType($values['namedescstep']['courseGrade'],'grade');
			$location =& $values['namedescstep']['location'];
			
			/*$canonicalCourse =& $courseManager->createCanonicalCourse($values['namedescstep']['title'], 
																	   $values['namedescstep']['number'], 	
																	   $values['namedescstep']['description'], 
																	   $courseType, $statusType, 
																	   $values['namedescstep']['credits']);*/
																	   
			$id =& $idManager->getId($values['namedescstep']['courseid']);   
			$courseOffering =& $courseManager->getCanonicalCourse($id);														   
																	   
			$courseOffering =& $canonicalCourse->createCourseOffering($canonicalCourse->getTitle(), 
																	  $canonicalCourse->getNumber(),
																	  $canonicalCourse->getDescription(),	
																	  $sectionType, $statusType, 
																	  $location);
			RequestContext::sendTo($this->getReturnUrl());
			exit();
			return TRUE;
		} 
		// If we don't have authorization to add to the picked parent, send us back to
		// that step.
		else {
			return FALSE;
		}
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
		$url =& $harmoni->request->mkURL("admin", "main");
		return $url->write();
	}
}

?>