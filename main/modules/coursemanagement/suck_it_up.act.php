<?php

/**
 * @package polyphony.coursemanagement
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: suck_it_up.act.php,v 1.5 2007/09/19 14:04:54 adamfranco Exp $
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
 		$authZManager = Services::getService("AuthZ");
 		$idManager = Services::getService("IdManager");
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
		
		$actionRows =$this->getActionRows();
		$pageRows = new Container(new YLayout(), OTHER, 1);
		$harmoni = Harmoni::instance();
		
		$harmoni->request->startNamespace("polyphony-agents");

		$agentManager = Services::getService("Agent");
		$idManager = Services::getService("Id");
		$cm = Services::getService("CourseManagement");
		
		
		
		
		
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
		
		$searchTypes =$agentManager->getAgentSearchTypes();
		
		
		$classId =$idManager->getId("OU=Classes,OU=Groups,DC=middlebury,DC=edu ");
	$classes =$agentManager->getGroup($classId);
	$terms =$classes->getGroups(false);
	
	while($terms->hasNext()){
		$termGroup =$terms->next();
		
		$termName = $termGroup->getDisplayName();
		
		$term = $this->_getTerm($termName);
		
		
		$id =$term->getId();
		$idString = $id->getIdString();
		print "\n\t\t<option value='".$idString."'";
			if ($harmoni->request->get('term_name') == $idString){
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
		if ($termIdString = $harmoni->request->get('term_name') ) {
			

	
		
	$classId =$idManager->getId("OU=Classes,OU=Groups,DC=middlebury,DC=edu ");
	$classes =$agentManager->getGroup($classId);
	
	
	
	
	$terms =$classes->getGroups(false);
	
	
	
	while($terms->hasNext()){
		$termGroup =$terms->next();
		
		$termName = $termGroup->getDisplayName();
		
		$term = $this->_getTerm($termName);
		$id=$term->getId();
		
		if($termIdString==$id->getIdString()){
			break;
		
		}
		
	}
	
				
 	$pageRows->add(new Heading(_("Courses Sucked from ".$term->getDisplayName().""), 2), "100%", null, LEFT, CENTER);

		
	ob_start();	
	
	
	$last = "";
		
		$sections =$termGroup->getGroups(false);
			
		while($sections->hasNext()){
			
			
			
			
			$section =$sections->next();
			
			
			$sectionName = $section->getDisplayName();	
			
		
			if(substr($sectionName,0,4)=="phed"){
			
				continue;	
			}
			if(substr($sectionName,0,strlen($sectionName)-5)!=$last){
				$last=substr($sectionName,0,strlen($sectionName)-5);
				$canonicalCourseId = $this->_getCanonicalCourse($sectionName);
				
			
			}
		
		
		}
	
	
		print "Success!";
	
			// Create a layout for this group using the GroupPrinter
		
			$groupLayout = new Block(ob_get_contents(), STANDARD_BLOCK);
			ob_end_clean();
			
			
			$pageRows->add($groupLayout, "100%", null, LEFT, CENTER);	
		//}
		
		// In order to preserve proper nesting on the HTML output, the checkboxes
		// are all in the pagerows layout instead of actionrows.
 		$actionRows->add($pageRows, null, null,CENTER, CENTER);	
 		
 		
		}
 		
 		textdomain($defaultTextDomain);
	}
	
	
	
	
	/**
	 *Gets a term from the LDAP name, creating it if necesary
	 **/
	function _getTerm($termName ){
		
		$cm = Services::getService("CourseManagement");
		
		$season =  substr($termName,0,strlen($termName)-2);		
		$year = '20'.substr($termName,strlen($termName)-2,2);
		$name = $season." ".$year;
		
		$termType = new Type("Coursemanagement","edu.middlebury",$season);
		
		$index = $cm->_typeToIndex('term',$termType);
		
		
		
		
		$dbHandler = Services::getService("DBHandler");
		$query= new SelectQuery;
		$query->addTable('cm_term');
		$query->addWhere("name='".addslashes($name)."'");
		$query->addWhere("fk_cm_term_type='".addslashes($index)."'");
		$query->addColumn('id');
		$res=$dbHandler->query($query);



		if($res->getNumberOfRows()==0){
			
			
			
			$term =$cm->createTerm($termType,$arr=array());
			$term->updateDisplayName($name);
			
			return $term;
			//$termId =$term->getId();

			//return $termId->getIdString();
			
		}else{
				
			$row = $res->getCurrentRow();
		
			$idManager = Services::getService("Id");
			$id =$idManager->getId($row['id']);
			$term =$cm->getTerm($id);
			
			return $term;

		}		
	}
	
	
	
	function _getCanonicalCourse($courseString ){
		
		$cm = Services::getService("CourseManagement");
		
				
		//$num = substr($courseString,4,4);
		$number = substr($courseString,0,strlen($courseString)-5);
		
		
		
		
		
		
		
		
		$dbHandler = Services::getService("DBHandler");
		$query= new SelectQuery;
		$query->addTable('cm_can');
		$query->addWhere("number='".addslashes($number)."'");
		$query->addColumn('id');
		$res=$dbHandler->query($query);


		

		if($res->getNumberOfRows()==0){
			
		
			
			//$termType = new Type("Coursemanagement","edu.middlebury",$season);		
			//$index = $cm->_typeToIndex('term',$termType);
			
			$dept =  substr($courseString,0,strlen($courseString)-9);
			
			$type = new Type("Coursemanagement","edu.middlebury",$dept);
			$stattype = new Type("Coursemanagement","edu.middlebury","default");
			
			$can =$cm->createCanonicalCourse($number,$number,"",$type,$stattype,1);
			
		
			
			print "<font size=4><b>".$number."</b> </font>\n";
			return $can;
			//$canId =$can->getId();

			//return $canId->getIdString();
		}else{
			
			
			$row = $res->getCurrentRow();
			//$the_index = $row['id'];
			$idManager = Services::getService("Id");
			$id =$idManager->getId($row['id']);
			$can =$cm->getCanonicalCourse($id);
			
			print "<font size=4>".$number." </font>\n";
			
			return $can;
			
			
			
			//return $the_index;

		}		
	}
	
}