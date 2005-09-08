<?php

/**
 *
 * @package polyphony.modules.agents
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: clear_authorizations.act.php,v 1.1 2005/09/08 20:48:53 gabeschine Exp $
 */

require_once(HARMONI."/GUIManager/Layouts/YLayout.class.php");
require_once(HARMONI."/GUIManager/Components/Heading.class.php");

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");


/**
 * This action clears all authorizations for the passed user(s).
 *
 * @since 11/10/04 
 * 
 * @package polyphony.modules.agents
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: clear_authorizations.act.php,v 1.1 2005/09/08 20:48:53 gabeschine Exp $
 */
class clear_authorizationsAction 
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
 		return $authZManager->isUserAuthorized(
 					$idManager->getId("edu.middlebury.authorization.modify_authorizations"),
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
		return dgettext("polyphony", "Clear Authorizations");
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
		$harmoni->request->startNamespace("polyphony-authorizations");
                                   
		// Our                       
		$actionRows =& $this->getActionRows();
		
		// pass our search variables through to new URLs
		$harmoni->request->passthrough("agents");

		if (!$harmoni->request->get("agents")) $harmoni->history->goBack("polyphony/agents/clear_authorizations");
		$agents = unserialize($harmoni->request->get("agents"));
		
		if (count($agents) == 0) $harmoni->history->goBack("polyphony/agents/clear_authorizations");
		
		// now, if we have a confirm, go ahead and delete the agents and get back to where we came from
		if ($harmoni->request->get("confirm")) {
			$authZ =& Services::getService("AuthZ");
			$idManager =& Services::getService("Id");
			// clear all authorizations for the users selected
			foreach ($agents as $agentIdString) {
				$authorizations =& $authZ->getAllExplicitAZsForAgent($idManager->getId($agentIdString), false);
				
				while($authorizations->hasNext()) {
					$authorization =& $authorizations->next();
					$authZ->deleteAuthorization($authorization);
				}
			}
			
			// and done
			$harmoni->history->goBack("polyphony/agents/clear_authorizations");
			exit(0);
		}

		ob_start();
		$confirmUrl =& $harmoni->request->mkURL();
		$confirmUrl->setValue("confirm","1");
		if (count($agents) == 1) {
			$string = _("Are you sure you wish to clear all authorizations for the agent selected?");
		} else {
			$string = sprintf(_("Are you sure you wish to clear all authorizations for the %s agents selected?"), count($agents));
		}
		print $string;

		print "\n<div align='right'><a href='".$harmoni->history->getReturnURL("polyphony/authorizations/clear_authorizations")."'>&lt;&lt; "._("Go Back")."</a>\n<a href='".$confirmUrl->write()."'>Confirm &gt;&gt;</a></div>\n";

		$actionRows->add(new Block(ob_get_contents(), 4));
		ob_end_clean();


		$harmoni->request->endNamespace();

		/*********************************************************
		 * Return the main layout.
		 *********************************************************/
		return $actionRows;
	}


}


