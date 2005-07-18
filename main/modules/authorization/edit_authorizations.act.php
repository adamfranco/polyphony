<?php

/**
 * @package polyphony.modules.authorization
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: edit_authorizations.act.php,v 1.34 2005/07/18 13:53:47 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

/**
 * edit_authorizations.act.php
 * This file will allow the user to edit authorizations for a given user.
 * The chosen user information will have been passed from choose_agents.act.php via FORM action.
 * 11/11/04 Ryan Richards
 *
 * @since 11/11/04 
 * @author Ryan Richards
 * @author Adam Franco
 * 
 * @package polyphony.modules.authorization
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: edit_authorizations.act.php,v 1.34 2005/07/18 13:53:47 adamfranco Exp $
 */
class edit_authorizationsAction 
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
		return TRUE;
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
		$idManager =& Services::getService("Id");
		$agentManager =& Services::getService("Agent");
			
		// Get the id of the selected agent using $_REQUEST
		$harmoni->request->startNamespace("polyphony-authorizations");
		$idObject =& $idManager->getId(RequestContext::value("agentId"));
		$harmoni->request->endNamespace();
		
		
		if ($agentManager->isGroup($idObject)) {
			$agent =& $agentManager->getGroup($idObject);
			return _("Modify Authorizations for Group").": <em> "
						.$agent->getDisplayName()."</em>";
		
		} else if ($agentManager->isAgent($idObject)) {
			$agent =& $agentManager->getAgent($idObject);
			return _("Modify Authorizations for User").": <em> "
						.$agent->getDisplayName()."</em>";
		
		} else {
			return _("Modify Authorizations for the User/Group Id").": <em> "
						.$idObject->getIdString()."</em>";
		}
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
		$harmoni =& Harmoni::instance();
		
		// start our namespace
		$harmoni->request->startNamespace("polyphony-authorizations");
		$harmoni->request->passthrough();

		// Intro
		$idManager =& Services::getService("Id");
		$agentManager =& Services::getService("Agent");
		$authZManager =& Services::getService("AuthZ");
		
		// Intro message
		$intro =& new Block("&nbsp; &nbsp; "._("Check or uncheck authorization(s) for the section(s) of your choice.")."<br />
				&nbsp; &nbsp; "._("After each change, the changes are saved automatically.")."<br /><br />", 3);
		
		
		// Get the id of the selected agent using $_REQUEST
		$id = RequestContext::value("agentId");
		$idObject =& $idManager->getId($id);
		$GLOBALS["agentId"] =& $idObject;
		$GLOBALS["harmoniAuthType"] =& new HarmoniAuthenticationType();		
		

		
		$actionRows->add($intro);
		 
		// Buttons to go back to edit auths for a different user, or to go home
		ob_start();
		print "<table width='100%'><tr><td align='left'>";
		print "<a href='".$harmoni->request->quickURL("authorization","choose_agent")."'><button>&lt;-- "._("Choose a different User/Group to edit")."</button></a>";
		print "</td><td align='right'>";
		print "<a href='".$harmoni->request->quickURL("admin","main")."'><button>"._("Return to the Admin Tools")."</button></a>";
		print "</td></tr></table>";
		
		$nav =& new Block(ob_get_contents(),2);
		$actionRows->add($nav, "100%", null, LEFT, CENTER);
		ob_end_clean();
		
		// Get all hierarchies and their root qualifiers
		$hierarchyIds =& $authZManager->getQualifierHierarchies();
		$hierarchyManager =& Services::getService("Hierarchy");
		while ($hierarchyIds->hasNext()) {
			$hierarchyId =& $hierarchyIds->next();
			
			$hierarchy =& $hierarchyManager->getHierarchy($hierarchyId);
			$header =& new Heading($hierarchy->getDisplayName()." - <em>".$hierarchy->getDescription()."</em>", 2);
			$actionRows->add($header, "100%", null, LEFT, CENTER);
		
			// Get the root qualifiers for the Hierarchy
			$qualifiers =& $authZManager->getRootQualifiers($hierarchyId);
			while ($qualifiers->hasNext()) {
				$qualifier =& $qualifiers->next();
		
				// Create a layout for this qualifier
				ob_start();
				HierarchyPrinter::printNode($qualifier, $harmoni,
										2,
										"edit_authorizationsAction::printQualifier",
										"edit_authorizationsAction::hasChildQualifiers",
										"edit_authorizationsAction::getChildQualifiers",
										new HTMLColor("#ddd")
									);
				$qualifierLayout =& new Block(ob_get_contents(), 4);
				ob_end_clean();
				$actionRows->add($qualifierLayout, "100%", null, LEFT, CENTER);
		
		
			}
		}
		
		// Buttons to go back to edit auths for a different user, or to go home
		$actionRows->add($nav,"100%", null, LEFT,CENTER);
		
		$harmoni->request->endNamespace();

	}


/*********************************************************
 * Callback Functions for printing
 *********************************************************/
	
	/**
	 * Callback function for printing a qualifier.
	 * 
	 * @param object Qualifier $qualifier
	 * @return void
	 * @access public
	 * @ignore
	 */
	function printQualifier(& $qualifier) {
		$id =& $qualifier->getId();
		$type =& $qualifier->getQualifierType();
		
		$title = _("Id: ").$id->getIdString()." ";
		$title .= _("Type: ").$type->getDomain()."::".$type->getAuthority()."::".$type->getKeyword();
	
		print "\n<a title='".htmlentities($title, ENT_QUOTES)."'><strong>".htmlentities($qualifier->getReferenceName(), ENT_QUOTES)."</strong></a>";
		
		// Check that the current user is authorized to see the authorizations.
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("IdManager");
		if ($authZ->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.view_authorizations"),
					$id)) 
		{
			print "\n<div style='margin-left: 10px;'>";
			edit_authorizationsAction::printEditOptions($qualifier);
			print "\n</div>";
		}
		// If they are not authorized to view the AZs, notify
		else {
			print " <em>"._("You are not authorized to view authorizations here.")."<em>";
		}
	}
	
	/**
	 * Callback function for determining if a qualifier has children.
	 * 
	 * @param object Qualifier $qualifier
	 * @return boolean
	 * @access public
	 * @ignore
	 */
	function hasChildQualifiers(& $qualifier) {
		return $qualifier->isParent();
	}
	
	/**
	 * Callback function for fetching the children of a qualifier.
	 * 
	 * @param object Qualifier $qualifier
	 * @return array
	 * @access public
	 * @ignore
	 */
	function &getChildQualifiers(& $qualifier) {
		$array = array();
		$iterator =& $qualifier->getChildren();
		while ($iterator->hasNext()) {
			$array[] =& $iterator->next();
		}
		return $array;
	}
	
	/**
	 * Callback function for printing a table of all functions.  
	 * To be used for each qualifier in the hierarchy.
	 * 
	 * @param object Qualifier $qualifier
	 * @return void
	 * @access public
	 * @ignore
	 */
	function printEditOptions(& $qualifier) {
		$qualifierId =& $qualifier->getId();
		$agentId =& $GLOBALS["agentId"];
		$harmoni =& Harmoni::instance();
		$authZManager =& Services::getService("AuthZ");
		$idManager =& Services::getService("IdManager");
		$agentManager =& Services::getService("AgentManager");
		
		$functionTypes =& $authZManager->getFunctionTypes();
		print "\n<table>";
		while ($functionTypes->hasNext()) {
			print "\n<tr>";
			$functionType =& $functionTypes->next();
			$functions =& $authZManager->getFunctions($functionType);
			
			$title = _("Functions for")." ";
			$title .= $functionType->getKeyword();
			
			print "\n\t<th style='margin-bottom: 3px'>";
			print "\n\t\t<a";
			print " title='".htmlentities($title, ENT_QUOTES)."'";
			print " href=\"Javascript:window.alert('".htmlentities($title, ENT_QUOTES)."')\"";
			print ">?</a>";
			print "\n\t</th>";
			
			$numFunctions = 0;
			while ($functions->hasNext()) {
				$numFunctions++;
				$function =& $functions->next();
				$functionId =& $function->getId();
				
				// IF an authorization exists for the user on this qualifier, 
				// make checkbox already checked
				$hasExplicit = FALSE;
				$implicitAZs = array();
				$allAZs =& $authZManager->getAllAZs($agentId, $functionId, $qualifierId, FALSE);
				while ($allAZs->hasNext()) {
					$az =& $allAZs->next();
					if ($az->isExplicit()) {
						$hasExplicit = TRUE;
					} else {
						$implicitAZs[] =& $az;
					}
				}
				// Store values for display output
				if ($authZManager->isAuthorized($agentId, $functionId, $qualifierId))
					$borderColor = "green";
				else
					$borderColor = "red";
	
	
				if ($hasExplicit) {
					$explicitChecked = "checked='checked'";
					$toggleOperation = "delete";
				} else {
					$explicitChecked = "";
					$toggleOperation = "create";
				}
				
				print "\n\t<td style='border: 1px solid ".$borderColor.";' align='right'>";
				print "\n\t\t\t\t<table>";
				print "\n\t\t\t\t<tr>";
				
				// Print out a disabled checkbox for each implicit Auth.
				for ($i=0; $i < count($implicitAZs); $i++) {
					// Built info about the explicit AZs that cause this implictAZ
					$implicitAZ =& $implicitAZs[$i];
					$explicitAZs =& $authZManager->getExplicitUserAZsForImplicitAZ($implicitAZ);
					$title = "";
					while ($explicitAZs->hasNext()) {
						$explicitAZ =& $explicitAZs->next();
						$explicitAgentId =& $explicitAZ->getAgentId();
						$explicitQualifier =& $explicitAZ->getQualifier();
						$explicitQualifierId =& $explicitQualifier->getId();
					
						// get the agent/group for the AZ
						if ($agentManager->isAgent($explicitAgentId)) {
							$explicitAgent =& $agentManager->getAgent($explicitAgentId);
							$title = _("User").": ".$explicitAgent->getDisplayName();
						} else if ($agentManager->isGroup($explicitAgentId)) {
							$explicitGroup =& $agentManager->getGroup($explicitAgentId);
							$title = _("Group").": ".$explicitGroup->getDisplayName();
						} else {
							$title = _("User/Group").": ".$explicitAgentId->getIdString();
						}
						$title .= ", "._("Location").": ".$explicitQualifier->getReferenceName();
						if ($explicitAZs->hasNext())
							$title .= "; ";
					}
					
					// print out a checkbox for the implicit AZ
					print "\n\t\t\t\t\t<td><span style='white-space: nowrap'>";
					print "\n\t\t\t\t\t\t<input type='checkbox' name='blah' value='blah'";
					print " title='".htmlentities($title, ENT_QUOTES)."'";
					print " checked='checked' disabled='disabled' />";
					print "\n\t\t\t\t\t\t<a";
	// 				print " id='".$explicitAgentId->getIdString()
	// 						."-".$functionId->getIdString()
	// 						."-".$explicitQualifierId->getIdString()."'";
					print " title='".htmlentities($title, ENT_QUOTES)."'";
					print " href=\"Javascript:window.alert('".htmlentities($title, ENT_QUOTES)."')\"";
					print ">?</a>";
					print "\n\t\t\t\t\t</span></td>";
				}
				
				print "\n\t\t\t\t\t<td>";
				// print an extra space
				if (count($implicitAZs))
					print "&nbsp;";
					
				print "\n\t\t\t\t\t\t<input type='checkbox' name='blah' value='blah' ";
				print $explicitChecked;
				
				// Check that the current user is authorized to modify the authorizations.
				$authZ =& Services::getService("AuthZ");
				$idManager =& Services::getService("IdManager");
				$agentManager =& Services::getService("AgentManager");
				if ($authZ->isUserAuthorized(
							$idManager->getId("edu.middlebury.authorization.modify_authorizations"),
							$qualifierId))
				{
					// The checkbox is really just for show, the link is where we send
					// to our processing to toggle the state of the authorization.
					$harmoni->history->markReturnURL("polyphony/agents/process_authorizations");
					$toggleURL = $harmoni->request->quickURL(
									"authorization",
									"process_authorizations",
									array(
										"functionId"=>$functionId->getIdString(),
										"qualifierId"=>$qualifierId->getIdString(),
										"operation"=>$toggleOperation
									));
		
					print " onclick=\"Javascript:window.location='".htmlentities($toggleURL, ENT_QUOTES)."'\"";
				}
				// If they are not authorized to view the AZs, disable the checkbox
				else {
					print " disabled='disabled'";
				}
				print " /></td>";
	
				print "\n\t\t\t\t\t<td><span style='white-space: nowrap'>".htmlentities($function->getReferenceName())."</span></td>";
				print "\n\t\t\t\t</tr>";
				print "\n\t\t\t\t</table>";
				print "\n\t</td>";
				
				// If we are up to six and we have more, start a new row.
				if ($numFunctions % 5 == 0 && $functions->hasNext())
					print "\n</tr>\n<tr>\n\t<th>\n\t\t&nbsp;\n\t</th>";
			}
			print "\n</tr>";
		}
		print"\n</table>";
	
	}

}


?>
