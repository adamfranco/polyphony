<?php

/**
 * group_membership.act.php
 * This action will allow for the creation/deletion of groups
 * 11/29/04 Ryan Richards, some code from Adam Franco
 *
 * @package polyphony.modules.agents
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: delete.act.php,v 1.3 2007/09/04 20:28:10 adamfranco Exp $
 */

require_once(HARMONI."/GUIManager/Layouts/YLayout.class.php");
require_once(HARMONI."/GUIManager/Components/Heading.class.php");

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");


/**
 * This action will allow for the modification of group Membership.
 *
 * @since 11/10/04 
 * 
 * @package polyphony.modules.agents
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: delete.act.php,v 1.3 2007/09/04 20:28:10 adamfranco Exp $
 */
class deleteAction 
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
 		return $authZManager->isUserAuthorized(
 					$idManager->getId("edu.middlebury.authorization.delete_agent"),
 					$idManager->getId("edu.middlebury.authorization.root"));
	}

	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return dgettext("polyphony", "Delete Agent(s)");
	}

	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {      
		$harmoni = Harmoni::instance();
//		$harmoni->request->startNamespace("polyphony-agents");
                                   
		// Our                       
		$actionRows =$this->getActionRows();
                                   
		$agentManager = Services::getService("Agent");

		// pass our search variables through to new URLs
		$harmoni->request->passthrough("agents");

		if (!$harmoni->request->get("agents")) $harmoni->history->goBack("polyphony/agents/delete");
		$agents = unserialize($harmoni->request->get("agents"));
		
		if (count($agents) == 0) $harmoni->history->goBack("polyphony/agents/delete");
		
		// now, if we have a confirm, go ahead and delete the agents and get back to where we came from
		if ($harmoni->request->get("confirm")) {
			$agentManager = Services::getService("Agent");
			$tokenManager = Services::getService("AgentTokenMapping");
			$authNMethods = Services::getService("AuthNMethodManager");
			$idManager = Services::getService("Id");
			foreach ($agents as $idString) {
				print "deleting agent $idString... <br/>";
				$id =$idManager->getId($idString);
				$mappings =$tokenManager->getMappingsForAgentId($id);
				while($mappings->hasNext()) {
					$mapping =$mappings->next();
					print "handling mapping ... <br/>";
					$tokens =$mapping->getTokens();
					$authNTypes =$authNMethods->getAuthNTypes();
					while($authNTypes->hasNext()) {
						$authNType =$authNTypes->next();
						print "checking type: " . Type::typeToString($authNType) . "<br/>";
						$authNMethod =$authNMethods->getAuthNMethodForType($authNType);
						if ($authNMethod->supportsTokenDeletion() && $authNMethod->tokensExist($tokens)) {
							print "deleting tokens ... <br/>";
							$authNMethod->deleteTokens($tokens);
						}
					}
					$tokenManager->deleteMapping($mapping);
				}
				$agentManager->deleteAgent($id);
			}
			
			// and done
			$harmoni->history->goBack("polyphony/agents/delete");
			exit(0);
		}

		ob_start();
		$confirmUrl =$harmoni->request->mkURL();
		$confirmUrl->setValue("confirm","1");
		if (count($agents) == 1) {
			$string = _("Are you sure you wish to delete the agent selected?");
		} else {
			$string = sprintf(_("Are you sure you wish to delete the %s agents selected?"), count($agents));
		}
		print $string;

		print "\n<div align='right'><a href='".$harmoni->history->getReturnURL("polyphony/agents/delete")."'>&lt;&lt; "._("Go Back")."</a>\n<a href='".$confirmUrl->write()."'>Confirm &gt;&gt;</a></div>\n";

		$actionRows->add(new Block(ob_get_contents(), 4));
		ob_end_clean();


//		$harmoni->request->endNamespace();

		/*********************************************************
		 * Return the main layout.
		 *********************************************************/
		return $actionRows;
	}


}


