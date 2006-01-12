<?php

/**
 * @package polyphony.modules.authorization
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: edit_authorizations.act.php,v 1.42 2006/01/12 14:52:33 adamfranco Exp $
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
 * @version $Id: edit_authorizations.act.php,v 1.42 2006/01/12 14:52:33 adamfranco Exp $
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
		$actionRows->add($intro);
		
		
		
		// Get the id of the selected agent using $_REQUEST <-- !!! do we need this???
		$mult = RequestContext::value("mult");
		$agents = RequestContext::value("agents");
		$id = RequestContext::value("agentId");
		
		// get a list of agents we are editing
		if ($harmoni->request->get("mult")) {
			$agentIds = unserialize($harmoni->request->get("agents"));
		} else {
			$agentIds = array($harmoni->request->get("agentId"));
		}
		
		$totalAgents = count($agentIds);
		
		$this->_agentIds = array();
		foreach ($agentIds as $idString) {
			$this->_agentIds[] =& $idManager->getId($idString);
		}
		
		
		// Break the path info into parts for the enviroment and parts that
		// designate which nodes to expand.
		$this->expandedNodes = explode("!", $harmoni->request->get('expanded_nodes'));
		
		 
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
				
				$this->printAZTable($qualifier);
				
// 				HierarchyPrinter::printNode($qualifier, $harmoni,
// 										2,
// 										"edit_authorizationsAction::printQualifier",
// 										"edit_authorizationsAction::hasChildQualifiers",
// 										"edit_authorizationsAction::getChildQualifiers",
// 										new HTMLColor("#ddd")
// 									);
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
	
	/**
	 * Print the table of authorizations, topped by columns for the functions
	 * 
	 * @param object Qualifier $qualifier
	 * @return void
	 * @access public
	 * @since 1/6/06
	 */
	function printAZTable ( &$qualifier ) {
		$azManager =& Services::getService("AuthZ");
		$functionOrder = array();
		
		print "\n\n<table border='0' style='text-align: center'>";
		
		$funcTypes =& $azManager->getFunctionTypes();
		$funcTypeRow = '';
		ob_start();
		while ($funcTypes->hasNext()) {
			$funcType =& $funcTypes->next();
			$functions =& $azManager->getFunctions($funcType);
			$i = 0;
			while ($functions->hasNext()) {
				$i++;
				$function =& $functions->next();
				$functionOrder[] =& $function->getId();
				print "\n\t\t<th style='vertical-align: bottom; border: 1px solid; border-bottom: 0px;'>";
				
				$name = htmlspecialchars($function->getReferenceName());
				for ($m = 0; $m < strlen($name); $m++)
					print $name[$m].'<br/>';
				
				print "</th>";
			}
			$funcTypeRow .= "\n\t\t<th colspan='$i' style='vertical-align: top; text-align: center; border: 1px solid; border-bottom: 0px;'>";
// 			$funcTypeRow .= "<span style='white-space: nowrap;'>"._("Functions for:")."</span><br/>";
			$funcTypeRow .= $funcType->getKeyword().":";
			print "</th>";
		}
		$funcRow = ob_get_clean();
		
		// Function Group Row
		print "\n\t<tr>";
		print "\n\t\t<td rowspan='2'>&nbsp;</td>";
		print $funcTypeRow;		
		print "\n\t</tr>";
		
		// Function Row
		print "\n\t<tr>";
		print $funcRow;
		print "\n\t</tr>";
		
		// Recursively print the qualifier rows
		$this->printQualifierRows($qualifier, $functionOrder);
		
		print "\n</table>";
	}
	
	/**
	 * print the qualifier AZ Row
	 * 
	 * @param object Qualifier $qualifier
	 * @param array $functionOrder
	 * @param array $ancestorQualifierIds
	 * @param optional integer $depth
	 * @return void
	 * @access public
	 * @since 1/6/06
	 */
	function printQualifierRows( &$qualifier, &$functionOrder, $depth=0 ) {
		$qualifierId =& $qualifier->getId();
		$type =& $qualifier->getQualifierType();
		
		$title = _("Id: ").$qualifierId->getIdString()." ";
		$title .= _("Type: ").$type->getDomain()."::".$type->getAuthority()."::".$type->getKeyword();
		
		print "\n\t<tr>";
		print "\n\t\t<th style='white-space: nowrap; text-align: left; border: 1px solid; border-right: 0px;'>";
		
		
		/*********************************************************/
		$marginLeft = ($depth * 20).'px';
		print "\n\t\t\t<table style='margin-left: $marginLeft'>";
		print "\n\t\t\t\t<tr><td valign='top'>";
		// Print The node
		if ($qualifier->isParent()) {
		?>

<div style='
	border: 1px solid #000; 
	width: 15px; 
	height: 15px;
	text-align: center;
	text-decoration: none;
	font-weight: bold;
'>
		<?php
/**
 * @package polyphony.modules.authorization
 */		
			// The child nodes are already expanded for this node. 
			// Show option to collapse the list.		
			if (in_array($qualifierId->getIdString(), $this->expandedNodes)) {
				$newExpandedNodes = array_diff($this->expandedNodes, 
					array($qualifierId->getIdString())); 	
				$symbol = '-';
				$expanded = TRUE;
			
			// The node is not already expanded.  Show option to expand.	
			} else { 
				$newExpandedNodes = array_merge($this->expandedNodes, 
					array($qualifierId->getIdString())); 
				$symbol = '+';
				$expanded = FALSE;
			}
			
			$harmoni =& Harmoni::instance();
			$url =& $harmoni->request->mkURLWithPassthrough();
			$url->setValue('expanded_nodes', implode('!', $newExpandedNodes));
			
			print "<a style='text-decoration: none;' href='".$url->write()."'>".$symbol."</a>";
			
			print "\n\t\t</div>";			
		// The node has no children.  Do not show options to expand/collapse.
		} else {
			print "\n\t\t<div style='width: 15px;'>&nbsp;</div>";
		}
		print "\n\t\t\t\t</td>";
		print "\n\t\t\t\t<th>";
		/*********************************************************/
		
		print "\n\t\t\t\t\t<a title='".htmlspecialchars($title, ENT_QUOTES)."'>";
		print htmlspecialchars($qualifier->getReferenceName(), ENT_QUOTES);
		print "</a>";
		
		print "\n\t\t\t\t</th>";
		print "\n\t\t\t\t</tr>";
		print "\n\t\t\t</table>";
		print "\n\t\t</th>";
		
		// Load the explicit AZs that affect the agents
		$this->loadAZs($qualifierId);
		
		for ($i = 0; $i < count($functionOrder); $i++) {
			$this->printAZCell($qualifier, $functionOrder[$i]);
		}
		
		print "\n\t</tr>";
		
		
		// Recursively print the children.
		if (in_array($qualifierId->getIdString(), $this->expandedNodes)) {
			$children =& $qualifier->getChildren();
			while ($children->hasNext()) {
				$this->printQualifierRows( $children->next(), $functionOrder, $depth + 1 );
			}
		}
	}
	
	/**
	 * Print a cell with the authorizations for a given qualifier and function
	 * 
	 * @param object Qualifier $qualifier
	 * @param object Id $functionId
	 * @return void
	 * @access public
	 * @since 1/6/06
	 */
	function printAZCell ( &$qualifier, &$functionId ) {
		$harmoni =& Harmoni::instance();
		$idManager =& Services::getService("Id");
		$authZManager =& Services::getService("AuthZ");
		$idManager =& Services::getService("IdManager");
		$agentManager =& Services::getService("AgentManager");
		
		// Authorized Agents: none, some, all
		$authorizedAgents = 'none';
		foreach ($ancestorQualifierIds as $ancestorId) {
			
		}
		
		print "\n\t\t<td>";
		
		// AZs
		print "\n\t\t\t<table>\n\t\t\t\t<tr>";
		$this->printNewEditOptions($qualifier, $functionId);
		print "\n\t\t\t\t</tr>\n\t\t\t</table>";
		
		
		// Old version
// 		print "<hr/>";
// 		print "\n\t\t<table><tr>";
// 		$this->printEditOptions($qualifier, $functionId);
// 		print "\n\t\t</tr></table>";
		
		
		print "\n\t\t</td>";
	}
	
	/**
	 * Load the explicit AZs
	 * 
	 * @return void
	 * @access public
	 * @since 1/9/06
	 */
	function loadAZs (&$qualifierId) {
		$this->_agentAZs = array();
		$azManager =& Services::getService("AuthZ");
		foreach (array_keys($this->_agentIds) as $key) {
			$agentIdString = $this->_agentIds[$key]->getIdString();
			$null = null;
			$azs =& $azManager->getAllAZs($this->_agentIds[$key], $null, $qualifierId, FALSE);
			$agentAZs[$agentIdString] = array();
			while ($azs->hasNext()) {
				$az =& $azs->next();
				$function =& $az->getFunction();
				$functionId =& $function->getId();
// 				$qualifier =& $az->getQualifier();
// 				$qualifierid =& $qualifier->getId();
				
				if (!isset($this->_agentAZs[$agentIdString][$qualifierId->getIdString()]))
					$this->_agentAZs[$agentIdString][$qualifierId->getIdString()] = array();
				
				if (!isset($this->_agentAZs[$agentIdString][$qualifierId->getIdString()][$functionId->getIdString()]))
					$this->_agentAZs[$agentIdString][$qualifierId->getIdString()][$functionId->getIdString()] = array();
				
				$this->_agentAZs[$agentIdString][$qualifierId->getIdString()][$functionId->getIdString()][] =& $az;
			}
		}
		
		
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
	
		print "\n<a title='".htmlspecialchars($title, ENT_QUOTES)."'><strong>".htmlspecialchars($qualifier->getReferenceName(), ENT_QUOTES)."</strong></a>";
		
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
	 * Callback function for printing a table of all functions.  
	 * To be used for each qualifier in the hierarchy.
	 * 
	 * @param object Qualifier $qualifier
	 * @return void
	 * @access public
	 * @ignore
	 */
	function printEditOptions( &$qualifier, &$functionId ) {
		$qualifierId =& $qualifier->getId();
		$harmoni =& Harmoni::instance();
		$idManager =& Services::getService("Id");
		$authZManager =& Services::getService("AuthZ");
		$idManager =& Services::getService("IdManager");
		$agentManager =& Services::getService("AgentManager");
		
		$totalAgents = count($this->_agentIds);
				
				// IF an authorization exists for the user on this qualifier, 
				// make checkbox already checked
				$numExplicit = 0;
				$numImplicit = 0;
				$authorized = 0;
				$implicitAZs = array();
				$implicitAZOwners = array();
				foreach (array_keys($this->_agentIds) as $key) {
					$allAZs =& $authZManager->getAllAZs($this->_agentIds[$key], $functionId, $qualifierId, FALSE);
					$hasExplicit = $hasImplicit = false;
					while ($allAZs->hasNext()) {
						$az =& $allAZs->next();
						if ($az->isExplicit()) {
							$hasExplicit = true;
						} else {
							$hasImplicit = true;
							$implicitAZs[] =& $az;
							$implicitAZOwners[] =& $this->_agentIds[$key];
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
				
				print "\n\t\t<td style='border: 1px solid ".$borderColor.";'>";
				print "\n\t\t\t\t<table align='left'>";
				print "\n\t\t\t\t<tr>";
				
				// Print out a disabled checkbox for each implicit Auth.
// 				$title = "";
// 				for ($i=0; $i < count($implicitAZs); $i++) {
// 					// Built info about the explicit AZs that cause this implictAZ
// 					$implicitAZ =& $implicitAZs[$i];
// 					$AZOwnerId =& $implicitAZOwners[$i];
// 					$displayName = "";
// 					if ($agentManager->isAgent($AZOwnerId)) {
// 						$agent =& $agentManager->getAgent($AZOwnerId);
// 						$displayName = $agent->getDisplayName();
// 						unset($agent);
// 					} else if ($agentManager->isGroup($AZOwnerId)) {
// 						$group =& $agentManager->getGroup($AZOwnerId);
// 						$displayName = $group->getDisplayName();
// 						unset($group);
// 					}
// 					$explicitAZs =& $authZManager->getExplicitUserAZsForImplicitAZ($implicitAZ);
// 
// 					while ($explicitAZs->hasNext()) {
// 						$title .= "$displayName - ";
// 						$explicitAZ =& $explicitAZs->next();
// 						$explicitAgentId =& $explicitAZ->getAgentId();
// 						$explicitQualifier =& $explicitAZ->getQualifier();
// 						$explicitQualifierId =& $explicitQualifier->getId();
// 					
// 						// get the agent/group for the AZ
// 						if ($agentManager->isAgent($explicitAgentId)) {
// 							$explicitAgent =& $agentManager->getAgent($explicitAgentId);
// 							$title .= _("User").": ".$explicitAgent->getDisplayName();
// 						} else if ($agentManager->isGroup($explicitAgentId)) {
// 							$explicitGroup =& $agentManager->getGroup($explicitAgentId);
// 							$title .= _("Group").": ".$explicitGroup->getDisplayName();
// 						} else {
// 							$title .= _("User/Group").": ".$explicitAgentId->getIdString();
// 						}
// 						$title .= ", "._("Location").": ".$explicitQualifier->getReferenceName();
// 					//	if ($explicitAZs->hasNext())
// 							$title .= "; \n";
// 					}
// 					
// 
// 				}
				
				print "\n\t\t\t\t\t<td style='white-space: nowrap'>";
				
				if ($numImplicit > 0) {
				// print out a checkbox	 for the implicit AZ
// 					$safeTitle = str_replace("\n","\\n",htmlspecialchars($title, ENT_QUOTES));
					print "\n\t\t\t\t\t<span style='white-space: nowrap'>";
					print "\n\t\t\t\t\t\t<input type='checkbox' name='blah' value='blah'";
// 					print " title='".htmlspecialchars($title, ENT_QUOTES)."'";
					print " checked='checked' disabled='disabled' />";
					if ($totalAgents > 1) {
						print "Im: <b>". $numImplicit . "/" . $totalAgents . "</b> ";
					}
// 					print "\n\t\t\t\t\t\t<a";
// 	// 			print " id='".$explicitAgentId->getIdString()
// 	// 						."-".$functionId->getIdString()
// 	// 						."-".$explicitQualifierId->getIdString()."'";
// 					print " title='".htmlspecialchars($title, ENT_QUOTES)."'";
// 					print " href=\"Javascript:window.alert('".str_replace("\n","\\n",htmlspecialchars($title, ENT_QUOTES))."')\"";
// 					print ">?</a>";
						
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
				print "</td>";
				print "\n\t\t\t\t</tr>";
				print "\n\t\t\t\t</table>";
		
		print "\n\t\t</td>";
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
	function printNewEditOptions( &$qualifier, &$functionId) {
		$qualifierId =& $qualifier->getId();
		$harmoni =& Harmoni::instance();
		$idManager =& Services::getService("Id");
		$authZManager =& Services::getService("AuthZ");
		$idManager =& Services::getService("IdManager");
		$agentManager =& Services::getService("AgentManager");
		
		$totalAgents = count($this->_agentIds);
				
				// IF an authorization exists for the user on this qualifier, 
				// make checkbox already checked
				$numExplicit = 0;
				$numImplicit = 0;
				$authorized = 0;
				$implicitAZs = array();
				$implicitAZOwners = array();
				foreach (array_keys($this->_agentIds) as $key) {
					$allAZs =& $this->_agentAZs
						[$this->_agentIds[$key]->getIdString()]
						[$qualifierId->getIdString()]
						[$functionId->getIdString()];
					$hasExplicit = $hasImplicit = false;
					if (is_array($allAZs)) {
						foreach (array_keys($allAZs) as $azKey) {
							$az =& $allAZs[$azKey];
							if ($az->isExplicit()) {
								$hasExplicit = true;
							} else {
								$hasImplicit = true;
								$implicitAZs[] =& $az;
								$implicitAZOwners[] =& $this->_agentIds[$key];
							}
						}
						if ($hasExplicit) $numExplicit++;
						if ($hasImplicit) $numImplicit++;
						if ($hasExplicit || $hasImplicit) $authorized++;
					}
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
				
				print "\n\t\t<td style='border: 1px solid ".$borderColor.";'>";
				print "\n\t\t\t\t<table align='left'>";
				print "\n\t\t\t\t<tr>";
				
				// Print out a disabled checkbox for each implicit Auth.
// 				$title = "";
// 				for ($i=0; $i < count($implicitAZs); $i++) {
// 					// Built info about the explicit AZs that cause this implictAZ
// 					$implicitAZ =& $implicitAZs[$i];
// 					$AZOwnerId =& $implicitAZOwners[$i];
// 					$displayName = "";
// 					if ($agentManager->isAgent($AZOwnerId)) {
// 						$agent =& $agentManager->getAgent($AZOwnerId);
// 						$displayName = $agent->getDisplayName();
// 						unset($agent);
// 					} else if ($agentManager->isGroup($AZOwnerId)) {
// 						$group =& $agentManager->getGroup($AZOwnerId);
// 						$displayName = $group->getDisplayName();
// 						unset($group);
// 					}
// 					$explicitAZs =& $authZManager->getExplicitUserAZsForImplicitAZ($implicitAZ);
// 
// 					while ($explicitAZs->hasNext()) {
// 						$title .= "$displayName - ";
// 						$explicitAZ =& $explicitAZs->next();
// 						$explicitAgentId =& $explicitAZ->getAgentId();
// 						$explicitQualifier =& $explicitAZ->getQualifier();
// 						$explicitQualifierId =& $explicitQualifier->getId();
// 					
// 						// get the agent/group for the AZ
// 						if ($agentManager->isAgent($explicitAgentId)) {
// 							$explicitAgent =& $agentManager->getAgent($explicitAgentId);
// 							$title .= _("User").": ".$explicitAgent->getDisplayName();
// 						} else if ($agentManager->isGroup($explicitAgentId)) {
// 							$explicitGroup =& $agentManager->getGroup($explicitAgentId);
// 							$title .= _("Group").": ".$explicitGroup->getDisplayName();
// 						} else {
// 							$title .= _("User/Group").": ".$explicitAgentId->getIdString();
// 						}
// 						$title .= ", "._("Location").": ".$explicitQualifier->getReferenceName();
// 					//	if ($explicitAZs->hasNext())
// 							$title .= "; \n";
// 					}
// 					
// 
// 				}
				
				print "\n\t\t\t\t\t<td style='white-space: nowrap'>";
				
				if ($numImplicit > 0) {
				// print out a checkbox	 for the implicit AZ
// 					$safeTitle = str_replace("\n","\\n",htmlspecialchars($title, ENT_QUOTES));
					print "\n\t\t\t\t\t<span style='white-space: nowrap'>";
					print "\n\t\t\t\t\t\t<input type='checkbox' name='blah' value='blah'";
// 					print " title='".htmlspecialchars($title, ENT_QUOTES)."'";
					print " checked='checked' disabled='disabled' />";
					if ($totalAgents > 1) {
						print "Im: <b>". $numImplicit . "/" . $totalAgents . "</b> ";
					}
// 					print "\n\t\t\t\t\t\t<a";
// 	// 			print " id='".$explicitAgentId->getIdString()
// 	// 						."-".$functionId->getIdString()
// 	// 						."-".$explicitQualifierId->getIdString()."'";
// 					print " title='".htmlspecialchars($title, ENT_QUOTES)."'";
// 					print " href=\"Javascript:window.alert('".str_replace("\n","\\n",htmlspecialchars($title, ENT_QUOTES))."')\"";
// 					print ">?</a>";
						
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
				print "</td>";
				print "\n\t\t\t\t</tr>";
				print "\n\t\t\t\t</table>";
		
		print "\n\t\t</td>";
	}


}


?>
