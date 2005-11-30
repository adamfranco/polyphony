<?php

/**
 * @package polyphony.modules.authorization
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: edit_authorizations.act.php,v 1.39 2005/11/30 21:33:06 adamfranco Exp $
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
 * @version $Id: edit_authorizations.act.php,v 1.39 2005/11/30 21:33:06 adamfranco Exp $
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
		$mult = RequestContext::value("mult");
		$agents = RequestContext::value("agents");
		$idObject = $mult?null:$idManager->getId(RequestContext::value("agentId"));
//		$idObject =& $idManager->getId(RequestContext::value("agentId"));
		$GLOBALS["agentId"] =& $idObject;
		$harmoni->request->endNamespace();
		
		if ($mult) {
			return dgettext("polyphony", "Modify Authorizations for Multiple Agents");
		} else if ($agentManager->isGroup($idObject)) {
			$agent =& $agentManager->getGroup($idObject);
			return dgettext("polyphony", "Modify Authorizations for Group").": <em> "
						.$agent->getDisplayName()."</em>";
		
		} else if ($agentManager->isAgent($idObject)) {
			$agent =& $agentManager->getAgent($idObject);
			return dgettext("polyphony", "Modify Authorizations for User").": <em> "
						.$agent->getDisplayName()."</em>";
		
		} else {
			return dgettext("polyphony", "Modify Authorizations for the User/Group Id").": <em> "
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
		$defaultTextDomain = textdomain("polyphony");
		
		$actionRows =& $this->getActionRows();
		$harmoni =& Harmoni::instance();
		
		// start our namespace
		$harmoni->request->startNamespace("polyphony-authorizations");
		$harmoni->request->passthrough();

		// set the return URL
		$harmoni->history->markReturnURL("polyphony/agents/process_authorizations");

		// Intro
		$idManager =& Services::getService("Id");
		$agentManager =& Services::getService("Agent");
		$authZManager =& Services::getService("AuthZ");
		
		// Intro message
		$intro =& new Block("&nbsp; &nbsp; "._("Check or uncheck authorization(s) for the section(s) of your choice.")."<br />
				&nbsp; &nbsp; "._("After each change, the changes are saved automatically.")."<br /><br />", STANDARD_BLOCK);
		
		
		// Get the id of the selected agent using $_REQUEST <-- !!! do we need this???
		$mult = RequestContext::value("mult");
		$agents = RequestContext::value("agents");
		$id = RequestContext::value("agentId");
		
		$actionRows->add($intro);
		 
		// Buttons to go back to edit auths for a different user, or to go home
		ob_start();
		print "<table width='100%'><tr><td align='left'>";
		print "<a href='".$harmoni->history->getReturnURL("polyphony/authorization/edit_authorizations")."'><button>&lt;&lt; "._("Go back")."</button></a>";
		print "</td><td align='right'>";
		print "<a href='".$harmoni->request->quickURL("admin","main")."'><button>"._("Return to the Admin Tools")."</button></a>";
		print "</td></tr></table>";
		
		$nav =& new Block(ob_get_contents(), STANDARD_BLOCK);
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
				$qualifierLayout =& new Block(ob_get_contents(), STANDARD_BLOCK);
				ob_end_clean();
				$actionRows->add($qualifierLayout, "100%", null, LEFT, CENTER);
		
			}
		}
		
		$actionRows->add(new Block(_("Key: Im = Implicit authorization (an authorzation inherited further up the hierarchy or by group membership - must be changed where it was made Explicit)<br/>Ex = Explicit authorization (can be changed)"), STANDARD_BLOCK), "100%", null, TOP, LEFT);
		
		// Buttons to go back to edit auths for a different user, or to go home
		$actionRows->add($nav,"100%", null, LEFT,CENTER);
		
		$harmoni->request->endNamespace();
		
		textdomain($defaultTextDomain);
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
		$harmoni =& Harmoni::instance();
		$idManager =& Services::getService("Id");
		$authZManager =& Services::getService("AuthZ");
		$idManager =& Services::getService("IdManager");
		$agentManager =& Services::getService("AgentManager");
		
		// get a list of agents we are editing
		if ($harmoni->request->get("mult")) {
			$agentIds = unserialize($harmoni->request->get("agents"));
		} else {
			$agentIds = array($harmoni->request->get("agentId"));
		}
		
		$totalAgents = count($agentIds);
		
		$agentIdObjects = array();
		foreach ($agentIds as $idString) {
			$agentIdObjects[] =& $idManager->getId($idString);
		}
		
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
				$numExplicit = 0;
				$numImplicit = 0;
				$authorized = 0;
				$implicitAZs = array();
				$implicitAZOwners = array();
				foreach (array_keys($agentIdObjects) as $key) {
					$allAZs =& $authZManager->getAllAZs($agentIdObjects[$key], $functionId, $qualifierId, FALSE);
					$hasExplicit = $hasImplicit = false;
					while ($allAZs->hasNext()) {
						$az =& $allAZs->next();
						if ($az->isExplicit()) {
							$hasExplicit = true;
						} else {
							$hasImplicit = true;
							$implicitAZs[] =& $az;
							$implicitAZOwners[] =& $agentIdObjects[$key];
						}
					}
					if ($hasExplicit) $numExplicit++;
					if ($hasImplicit) $numImplicit++;
					if ($hasExplicit || $hasImplicit) $authorized++;
				}
				
				// Store values for display output
				if ($authorized == $totalAgents)
					$borderColor = "green";
				else if ($authorized == 0)
					$borderColor = "red";
				else $borderColor = "yellow"; // partial authorizations
	
	
				if ($numExplicit > 0) {
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
				$title = "";
				for ($i=0; $i < count($implicitAZs); $i++) {
					// Built info about the explicit AZs that cause this implictAZ
					$implicitAZ =& $implicitAZs[$i];
					$AZOwnerId =& $implicitAZOwners[$i];
					$displayName = "";
					if ($agentManager->isAgent($AZOwnerId)) {
						$agent =& $agentManager->getAgent($AZOwnerId);
						$displayName = $agent->getDisplayName();
						unset($agent);
					} else if ($agentManager->isGroup($AZOwnerId)) {
						$group =& $agentManager->getGroup($AZOwnerId);
						$displayName = $group->getDisplayName();
						unset($group);
					}
					$explicitAZs =& $authZManager->getExplicitUserAZsForImplicitAZ($implicitAZ);

					while ($explicitAZs->hasNext()) {
						$title .= "$displayName - ";
						$explicitAZ =& $explicitAZs->next();
						$explicitAgentId =& $explicitAZ->getAgentId();
						$explicitQualifier =& $explicitAZ->getQualifier();
						$explicitQualifierId =& $explicitQualifier->getId();
					
						// get the agent/group for the AZ
						if ($agentManager->isAgent($explicitAgentId)) {
							$explicitAgent =& $agentManager->getAgent($explicitAgentId);
							$title .= _("User").": ".$explicitAgent->getDisplayName();
						} else if ($agentManager->isGroup($explicitAgentId)) {
							$explicitGroup =& $agentManager->getGroup($explicitAgentId);
							$title .= _("Group").": ".$explicitGroup->getDisplayName();
						} else {
							$title .= _("User/Group").": ".$explicitAgentId->getIdString();
						}
						$title .= ", "._("Location").": ".$explicitQualifier->getReferenceName();
					//	if ($explicitAZs->hasNext())
							$title .= "; \n";
					}
					

				}
				
				print "\n\t\t\t\t\t<td>";
				
				if ($numImplicit > 0) {
				// print out a checkbox	 for the implicit AZ
// 					$safeTitle = str_replace("\n","\\n",htmlentities($title, ENT_QUOTES));
					print "\n\t\t\t\t\t<span style='white-space: nowrap'>";
					print "\n\t\t\t\t\t\t<input type='checkbox' name='blah' value='blah'";
					print " title='".htmlentities($title, ENT_QUOTES)."'";
					print " checked='checked' disabled='disabled' />";
					if ($totalAgents > 1) {
						print "Im: <b>". $numImplicit . "/" . $totalAgents . "</b> ";
					}
					print "\n\t\t\t\t\t\t<a";
	// 			print " id='".$explicitAgentId->getIdString()
	// 						."-".$functionId->getIdString()
	// 						."-".$explicitQualifierId->getIdString()."'";
					print " title='".htmlentities($title, ENT_QUOTES)."'";
					print " href=\"Javascript:window.alert('".str_replace("\n","\\n",htmlentities($title, ENT_QUOTES))."')\"";
					print ">?</a>";
						
					print "\n\t\t\t\t\t</span>";
				}
				
				// print an extra space
				if (count($implicitAZs))
					print "&nbsp;";
				
				$authZ =& Services::getService("AuthZ");
				$idManager =& Services::getService("IdManager");
				$canEdit = $authZ->isUserAuthorized(
										$idManager->getId("edu.middlebury.authorization.modify_authorizations"),
										$qualifierId);


				if ($totalAgents > 1) {
					$grantURL = $harmoni->request->quickURL("authorization","process_authorizations",
										array(
											"functionId"=>$functionId->getIdString(),
											"qualifierId"=>$qualifierId->getIdString(),
											"operation"=>"create"
											));
					$revokeURL = $harmoni->request->quickURL("authorization","process_authorizations",
										array(
											"functionId"=>$functionId->getIdString(),
											"qualifierId"=>$qualifierId->getIdString(),
											"operation"=>"delete"
											));
					print "\n\t\t\t\t\t\t";
					print "Ex: <b>".$numExplicit . "/" . $totalAgents . "</b>:\n";
					print "<select name='blah'".($canEdit?"":" disabled='disabled'")." onclick='if (this.value != \"nop\") window.location=this.value;'>\n";
					print "<option value='nop'>"._("action")."...</option>\n";
					print "<option value='$grantURL'>"._("Grant to All")."</option>\n";
					print "<option value='$revokeURL'>"._("Revoke Explicit")."</option>\n";
					print "</select><br/>\n";
				} else {
					print "\n\t\t\t\t\t\t<input type='checkbox' name='blah' value='blah' ";
					print $explicitChecked;
				
					// Check that the current user is authorized to modify the authorizations.
					$agentManager =& Services::getService("AgentManager");
					if ($canEdit)
					{
						// The checkbox is really just for show, the link is where we send
						// to our processing to toggle the state of the authorization.
						$toggleURL = $harmoni->request->quickURL(
										"authorization",
										"process_authorizations",
										array(
											"functionId"=>$functionId->getIdString(),
											"qualifierId"=>$qualifierId->getIdString(),
											"operation"=>$toggleOperation
										));
		
						print " onclick=\"Javascript:window.location='".$toggleURL."'\"";
					}
					// If they are not authorized to view the AZs, disable the checkbox
					else {
						print " disabled='disabled'";
					}
					print " />";
				}
//				print "</td>";
		
//				print "\n\t\t\t\t\t<td>";
				print "<span style='white-space: nowrap'>".htmlentities($function->getReferenceName())."</span></td>";
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
