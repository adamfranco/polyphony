<?php

/**
 * @package polyphony.agents
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: edit_agents.act.php,v 1.4 2007/09/19 14:04:52 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(HARMONI."GUIManager/Components/Blank.class.php");

/**
 * This action will allow for the modification of group Membership.
 *
 * @since 11/10/04 
 * 
 * @package polyphony.agents
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: edit_agents.act.php,v 1.4 2007/09/19 14:04:52 adamfranco Exp $
 */
class edit_agentsAction 
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
 					$idManager->getId("edu.middlebury.authorization.modify_agent"),
 					$idManager->getId("edu.middlebury.authorization.root"));
	}


	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to browse agents.");
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

		$centerPane =$this->getActionRows();
		$cacheName = 'edit_agents_wizard';

		// now, once we've run the content, we need to check if an action was chosen from the browser
		$wizard =$this->getWizard($cacheName);

		if ($harmoni->request->get("reset")) {
			// get the agent browser component and reset it.
			$steps =$wizard->getChild("_steps");
			$step =$steps->getStep("namedescstep");
			$component =$step->getChild("agents");
			$component->reset();
		}

		$this->runWizard ( $cacheName, $centerPane );

		$wizard =$this->getWizard($cacheName);
		
		$values = $wizard->getAllValues();
		
		$browser =$values["namedescstep"]["agents"];
		if ($browser[0] != 'nop') {
			$selected = $browser[1];
			$param = serialize($selected);
			switch ($browser[0]) {
				case 'edit_properties':
					$url =$harmoni->request->mkURL("agents", "edit_properties");
					$harmoni->request->startNamespace("polyphony-agents");
					$url->setValue("agents", $param);
					$url->setValue("mult", "1");
					$harmoni->request->endNamespace();
					$harmoni->history->markReturnURL("polyphony/agents/edit_properties");
					$url->redirectBrowser();
					exit(0);
					break;
				case 'edit_authorizations':
					$harmoni->request->startNamespace("polyphony-authorizations");
					$url =$harmoni->request->mkURL("authorization", "edit_authorizations");
					$url->setValue("agents", $param);
					$url->setValue("mult", "1");
					$harmoni->history->markReturnURL("polyphony/authorization/edit_authorizations");
					$harmoni->request->endNamespace();
					$url->redirectBrowser();
					exit(0);
					break;
				case 'revoke_authorizations':
					$harmoni->request->startNamespace("polyphony-authorizations");
					$url =$harmoni->request->mkURL("authorization", "clear_authorizations");
					$url->setValue("agents", $param);
					$harmoni->history->markReturnURL("polyphony/agents/clear_authorizations");
					$harmoni->request->endNamespace();
					$url->redirectBrowser();
					exit(0);
					break;
				case 'create_group':
					$url =$harmoni->request->mkURL("agents", "add_group");
					$url->setValue("agents", $param);
					$harmoni->history->markReturnURL("polyphony/agents/add_group");
					$url->redirectBrowser();
					exit(0);
					break;
				case 'delete':
					$url =$harmoni->request->mkURL("agents", "delete");
					$url->setValue("agents", $param);
					$return =$harmoni->request->mkURL();
					$return->setValue("reset", "1");
					$harmoni->history->markReturnURL("polyphony/agents/delete", $return);
					$url->redirectBrowser();
					exit(0);
					break;
			}
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
		return _("Browse Agents");
	}

	/**
	 * Create a new Wizard for this action. Caching of this Wizard is handled by
	 * {@link getWizard()} and does not need to be implemented here.
	 * 
	 * @return object Wizard
	 * @access public
	 * @since 4/28/05
	 */
	function createWizard () {

		// Instantiate the wizard, then add our steps.
		$wizard = SimpleStepWizard::withDefaultLayout();

		// :: Name and Description ::
		$step =$wizard->addStep("namedescstep", new WizardStep());
		$step->setDisplayName(_("Name &amp; Description"));

		$browser =$step->addComponent("agents", new WAgentBrowser());
		$browser->addActionOption("edit_properties", _("Edit Properties"));
		$browser->addActionOption("edit_authorizations", _("Edit Authorizations"));
		$browser->addActionOption("revoke_authorizations", _("Clear All Authorizations"));
		$browser->addActionOption("create_group", _("Create Group..."));
		$browser->addActionOption("delete", _("Delete"));
		$step->setContent("[[agents]]");
		
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
		$wizard =$this->getWizard($cacheName);

		if (!$wizard->validate()) return false;

		// Make sure we have a valid Repository
		$idManager = Services::getService("Id");

		$properties =$wizard->getAllValues();

		
	}

	/**
	 * Return the URL that this action should return to when completed.
	 * 
	 * @return string
	 * @access public
	 * @since 4/28/05
	 */
	function getReturnUrl () {
		$harmoni = Harmoni::instance();
		return $harmoni->request->quickURL("admin", "main");
	}


/*********************************************************
 * Functions used for the GroupPrinter
 *********************************************************/

	/**
	 * Callback function for printing a group
	 * 
	 * @param object Group $group
	 * @return void
	 * @access public
	 * @ignore
	 */
	function printGroup($group) {
		$idManager = Services::getService("Id");
		$everyoneId =$idManager->getId("edu.middlebury.agents.everyone");
		$usersId =$idManager->getId("edu.middlebury.agents.users");

		$id =$group->getId();
		$groupType =$group->getType();

		print "\n&nbsp; &nbsp; &nbsp;";

		print "\n<a title='".htmlspecialchars($groupType->getAuthority()." :: ".$groupType->getDomain()." :: ".$groupType->getKeyword()." - ".$groupType->getDescription())."'>";
		print "\n<span style='text-decoration: underline; font-weight: bold;'>".$id->getIdString()." - ".htmlspecialchars($group->getDisplayName())."</span></a>";


		print "\n - <em>".htmlspecialchars($group->getDescription())."</em>";

		// print out the properties of the Agent
		print "\n<em>";
		$propertiesIterator =$group->getProperties();

		while($propertiesIterator->hasNext()) {
			$properties =$propertiesIterator->next();
			$propertiesType =$properties->getType();
			print "\n\t(<a title='".htmlspecialchars($propertiesType->getDomain()." :: ".$propertiesType->getAuthority()." :: ".$propertiesType->getKeyword()." - ".$propertiesType->getDescription())."'>";

			$keys =$properties->getKeys();
			$i = 0;

			while ($keys->hasNext()) {
				$key =$keys->next();			
				print htmlspecialchars("\n\t\t".(($i)?", ":"").$key.": ".$properties->getProperty($key));
				$i++;
			}

			print "\n\t</a>)";
		}
		print "\n</em>";
	}

	/**
	 * Callback function for printing an agent
	 * 
	 * @param object Agent $member
	 * @return void
	 * @access public
	 * @ignore
	 */
	function printMember($member) {
		$harmoni = Harmoni::instance();
		$id =$member->getId();

		$memberType =$member->getType();
		$harmoni->history->markReturnURL("polyphony/agents/edit_agent_details");

		print "\n<a href='".$harmoni->request->quickURL("agents","edit_agent_details", array("agentId"=>$id->getIdString()))."' title='".htmlspecialchars($memberType->getDomain()." :: ".$memberType->getAuthority()." :: ".$memberType->getKeyword()." - ".$memberType->getDescription())."'>";
		print "\n<span style='text-decoration: none;'>".$id->getIdString()." - ".htmlspecialchars($member->getDisplayName())."</span></a>";

		// print out the properties of the Agent
		print "\n<em>";
		$propertiesIterator = NULL;
		$propertiesIterator =$member->getProperties();
		while($propertiesIterator->hasNext()) {
			$properties = NULL;
			$properties =$propertiesIterator->next();

			$propertiesType =$properties->getType();
			print "\n\t(<a title='".htmlspecialchars($propertiesType->getDomain()." :: ".$propertiesType->getAuthority()." :: ".$propertiesType->getKeyword()." - ".$propertiesType->getDescription())."'>";

			$keys =$properties->getKeys();
			$i = 0;
			while ($keys->hasNext()) {
				$key =$keys->next();			
				print htmlspecialchars("\n\t\t".(($i)?", ":"").$key.": ".$properties->getProperty($key));
				$i++;
			}

			print "\n\t</a>)";
		}
		print "\n</em>";
	}
}