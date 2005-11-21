<?php

/**
 * @package polyphony.modules.agents
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 * @version $Id: add_group.act.php,v 1.6 2005/11/21 21:43:05 adamfranco Exp $
 **/

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

/**
 * Allows the addition of a group using a Wizard interface.
 *
 * @package polyphony.modules.agents
 * @copyright Copyright &copy; 2005, Middlebury College
 * @author Gabriel Schine
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 * @version $Id: add_group.act.php,v 1.6 2005/11/21 21:43:05 adamfranco Exp $
 */
class add_groupAction extends MainWindowAction
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
				$idManager->getId("edu.middlebury.agents.all_groups"));
		}

		/**
		 * Return the "unauthorized" string to pring
		 * 
		 * @return string
		 * @access public
		 * @since 4/26/05
		 */
		function getUnauthorizedMessage () {
			return _("You are not authorized to create <em>Groups</em>.");
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
			
			$centerPane =& $this->getActionRows();
			$cacheName = 'create_group_wizard';
			
//			$this->cancelWizard($cacheName);
			$this->runWizard ( $cacheName, $centerPane );
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

			// Instantiate the wizard, then add our steps.
			ob_start();
			print "<h2>"._("Create Group")."</h2>";
			print "<b>"._("Display name")."</b>: [[display_name]]<br/>";
			print "<b>"._("Description")." ("._("optional").")"."</b>: <br/>[[description]] <br/>";
			print "<b>"._("Type")."</b>: [[type]] ("._("or use fields below to create a new type").")<br/>";
			print "<br/>";
			print "<b>"._("Type domain")."</b>: [[type_domain]]<br/>";
			print "<b>"._("Type authority")."</b>: [[type_authority]]<br/>";
			print "<b>"._("Type keyword")."</b>: [[type_keyword]]<br/>";
			print "<b>"._("Type description")."</b>:<br/> [[type_description]]<br/>";
			print "<div align='right'>[[_cancel]]\n[[_save]]</div>";
			print "[[members]]";
			
			if (RequestContext::value("agents") && count(($list=unserialize(RequestContext::value("agents")))) > 0 && is_array($list)) {
				// print out a list of agents
				print "<div>"._("The group will be created with the following members:")."<ul>\n";
				$agentManager =& Services::getService("Agent");
				$idManager =& Services::getService("Id");
				foreach ($list as $idString) {
					$id =& $idManager->getId($idString);
					if ($agentManager->isGroup($id)) {
						$agent =& $agentManager->getGroup($id);
						$name = _("Group").": ".$agent->getDisplayName();
					} else if ($agentManager->isAgent($id)) {
						$agent =& $agentManager->getAgent($id);
						$name = _("Agent").": ".$agent->getDisplayName();
					}
					
					print "<li>$name</li>\n";
				}
				print "</ul></div>";
			}
			$wizard =& SimpleWizard::withText(ob_get_contents());
			ob_end_clean();

			// Create the properties.
			$displayNameProp =& $wizard->addComponent("display_name", new WTextField());
			$displayNameProp->setErrorText(_("A value for this field is required."));
			$displayNameProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));

			$descriptionProp =& $wizard->addComponent("description", WTextArea::withRowsAndColumns(3,50));

			$property =& $wizard->addComponent("type", new WSelectList());
			$property->addOption("NONE", _("Use Fields Below..."));
			$agentMgr =& Services::getService("Agent");
			$types =& $agentMgr->getGroupTypes();
			while ($types->hasNext()) {
				$type =& $types->next();
				$typeKey = urlencode(HarmoniType::typeToString($type));
				$property->addOption($typeKey, HarmoniType::typeToString($type));
			}
			$property->setValue("NONE");

			$property =& $wizard->addComponent("type_domain", new WTextField());
			$property->setStartingDisplayText(_("Domain, i.e. 'groups'"));

			$property =& $wizard->addComponent("type_authority", new WTextField());
			$property->setStartingDisplayText(_("Authority, i.e. 'edu.middlebury'"));

			$property =& $wizard->addComponent("type_keyword", new WTextField());
			$property->setStartingDisplayText(_("Keyword, i.e 'classes"));
			
			$property =& $wizard->addComponent("type_description", WTextArea::withRowsAndColumns(3, 50));
			
			$wizard->addComponent("_save", WSaveButton::withLabel(_("Create Group")));
			$wizard->addComponent("_cancel", new WCancelButton());
			
			$members =& $wizard->addComponent("members", new WHiddenField());
			if (RequestContext::value("agents")) {
				// the members of the group to be created. an array of agent ids
				$members->setValue(RequestContext::value("agents"));
			}

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

			if (!$wizard->validate()) return false;

			// Make sure we have a valid Repository
			$idManager =& Services::getService("Id");
			$authZ =& Services::getService("AuthZ");

			$properties =& $wizard->getAllValues();

			// check if they entered a valid type.
			if ($properties["type"] == "NONE") {
				$domain = $properties["type_domain"];
				$authority = $properties["type_authority"];
				$keyword = $properties["type_keyword"];
				if (!($domain && $authority && $keyword)) return false;
				
				$desc = $properties["type_description"];
				$theType =& new Type($domain, $authority, $keyword, $desc);
			} else {
				$theType =& Type::stringToType(urldecode($properties["type"]));
			}
			
			// empty properties set
			$propObj =& new HarmoniProperties(new Type("Properties", "edu.middlebury.polyphony", "Generic"));
			
			$agents =& Services::getService("Agent");
			$group =& $agents->createGroup($properties["display_name"], $theType, $properties["description"], $propObj);
			
			if ($properties["members"] && count(($list=unserialize($properties["members"]))) > 0) {
				$ids =& Services::getService("Id");
				$agents =& Services::getService("Agent");
				foreach ($list as $agentId) {
					$id =& $ids->getId($agentId);
					if ($agents->isGroup($id)) {
						$agent =& $agents->getGroup($id);
					} else if ($agents->isAgent($id)) {
						$agent =& $agents->getAgent($id);
					}
					
					$group->add($agent);
				}
			}
			
			return true;
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
			$url = $harmoni->history->getReturnURL("polyphony/agents/add_group");
			if (!$url) $url = $harmoni->request->quickURL("agents","add_delete_group");
			return $url;
		}
} // END class add_groupAction

?>