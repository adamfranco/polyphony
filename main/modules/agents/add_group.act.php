<?php

/**
 * @package polyphony.modules.agents
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 * @version $Id: add_group.act.php,v 1.2 2005/09/07 21:18:25 adamfranco Exp $
 **/

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

/**
 * Allows the addition of a group using a Wizard interface.
 *
 * @package polyphony.modules.agents
 * @copyright Copyright &copy; 2005, Middlebury College
 * @author Gabriel Schine
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 * @version $Id: add_group.act.php,v 1.2 2005/09/07 21:18:25 adamfranco Exp $
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
			$wizard =& SimpleWizard::withText(ob_get_contents());
			ob_end_clean();

			// Create the properties.
			$displayNameProp =& $wizard->addComponent("display_name", new WTextField());
			$displayNameProp->setErrorText(_("A value for this field is required."));
			$displayNameProp->setErrorRule(new WECRegex("[\\w]+"));

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
			$property->setStartingDisplayText("Groups");

			$property =& $wizard->addComponent("type_authority", new WTextField());
			$property->setStartingDisplayText("edu.middlebury.polyphony");

			$property =& $wizard->addComponent("type_keyword", new WTextField());
			$property->setStartingDisplayText("Generic");
			
			$property =& $wizard->addComponent("type_description", WTextArea::withRowsAndColumns(3, 50));
			
			$wizard->addComponent("_save", WSaveButton::withLabel(_("Create Group")));
			$wizard->addComponent("_cancel", new WCancelButton());

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
			$agents->createGroup($properties["display_name"], $theType, $properties["description"], $propObj);

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