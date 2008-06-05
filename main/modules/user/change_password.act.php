<?php

/**
 * @package polyphony.user
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: change_password.act.php,v 1.6 2007/09/19 14:04:58 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

/**
 * This file will allow the user to change their HarmoniDB password.
 *
 * @since 10/24/05 
 * @author Christopher W. Shubert
 * 
 * @package polyphony.user
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: change_password.act.php,v 1.6 2007/09/19 14:04:58 adamfranco Exp $
 */
class change_passwordAction 
	extends MainWindowAction
{
	
	/**
	 * Answer the current AuthNMethod
	 * 
	 * @return object AuthMethod
	 * @access protected
	 * @since 6/5/08
	 */
	protected function getMethod () {
		if (!isset($this->_method)) {
			$authN = Services::getService("AuthN");
			$types = $authN->getAuthenticationTypes();
			while ($types->hasNext()) {
				$type = $types->next();
				if ($authN->isUserAuthenticated($type)) {
					$methodMgr = Services::getService("AuthNMethodManager");
					$this->_method = $methodMgr->getAuthNMethodForType($type);
					return $this->_method;
				}
			}
			throw new OperationFailedException("Must be authenticated.");
		}
		
		return $this->_method;
	}
	
	/**
	 * @var object AuthMethod $_method;  
	 * @access private
	 * @since 6/5/08
	 */
	private $_method;
	
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		try {
			$method = $this->getMethod();
			return $method->supportsTokenUpdates();
		} catch (OperationFailedException $e) {
			return false;
		}
	}
	
	function getUnauthorizedMessage() {
		return dgettext("polyphony", "You must be Authenticated with a type that supports password changing.");
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		try {
			$keyword = $this->getMethod()->getType()->getKeyword();
			return str_replace('%1', $keyword, dgettext("polyphony", "Change Your '%1' Password"));
		} catch (OperationFailedException $e) {
			return dgettext("polyphony", "Change Your Password");
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
		$authN = Services::getService("AuthN");
		
		$type = $this->getMethod()->getType();
		
		$centerPane =$this->getActionRows();
		
		$id =$authN->getUserId($type);
		$cacheName = 'change_password_wizard_'.$id->getIdString();
		
		$this->runWizard($cacheName, $centerPane);
	}
	
	/**
	 * creates the wizard
	 * 
	 * @return object Wizard
	 * @access public
	 * @since 10/24/05
	 */
	function createWizard() {
		$harmoni = Harmoni::Instance();
		$wizard = SimpleWizard::withText(
			"\n<h2>".dgettext("polyphony", "Old Password")."</h2>".
			"\n<br \>[[old_password]]".
			"\n<h2>".dgettext("polyphony", "New Password")."</h2>".
			"\n".dgettext("polyphony", "Please enter your new password twice").
			"\n<br />[[new_password]]".
			"\n<br />[[n_p_again]]".
			"<table width='100%' border='0' style='margin-top:20px' >\n".
			"<tr>\n".
			"</td>\n".
			"<td align='left' width='50%'>\n".
			"[[_cancel]]".
			"<td align='right' width='50%'>\n".
			"[[_save]]".
			"</td></tr></table>");
		$error = $harmoni->request->get("error");
		if (!is_null($error))
			print $error;
			
		$pass =$wizard->addComponent("old_password", new WPasswordField());
		$pass =$wizard->addComponent("new_password", new WPasswordField());
		$pass =$wizard->addComponent("n_p_again", new WPasswordField());
		
		$save =$wizard->addComponent("_save", 
			WSaveButton::withLabel("Change Password"));
		$cancel =$wizard->addComponent("_cancel", new WCancelButton());

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
	 * @since 10/24/05
	 */
	function saveWizard ($cacheName) {
		$harmoni = Harmoni::Instance();
		$authN = Services::getService("AuthN");
		$tokenM = Services::getService("AgentTokenMapping");
		$wizard =$this->getWizard($cacheName);
		
		$properties = $wizard->getAllValues();
		
		$type = $this->getMethod()->getType();		
		$id =$authN->getUserId($type);
		$it =$tokenM->getMappingsForAgentId($id);

		while ($it->hasNext()) {
			$mapping =$it->next();
			
			if ($mapping->getAuthenticationType() == $type)
				$tokens =$mapping->getTokens();
		}
		if (isset($tokens)) {
			$method = $this->getMethod();
				
			$uname = $tokens->getUsername();
			
			// Validate the old password
			$oldTokens = $method->createTokens(
							array(	'username' => $uname, 
									'password' => $properties['old_password']));
			if (!$method->authenticateTokens($oldTokens)) {
				$error = "Invalid old password";
				$localizedError = dgettext("polyphony", "Invalid old password, please try again.")."\n<br/>";
			}
			
			// Reset the password if old tokens are valid and new tokens are valid
			else if (($properties['new_password'] != '') && 
				($properties['new_password'] == $properties['n_p_again'])) {
					
				// Log the action
				if (Services::serviceRunning("Logging")) {
					$loggingManager = Services::getService("Logging");
					$log =$loggingManager->getLogForWriting("Authentication");
					$formatType = new Type("logging", "edu.middlebury", "AgentsAndNodes",
									"A format in which the acting Agent[s] and the target nodes affected are specified.");
					$priorityType = new Type("logging", "edu.middlebury", "Event_Notice",
									"Normal events.");
					
					$item = new AgentNodeEntryItem("Modify Agent", "Password changed for:\n<br/>&nbsp; &nbsp; &nbsp;".$uname."\n<br/>&nbsp; &nbsp; &nbsp;".$type->getKeyword());
					$item->addAgentId($id);
					
					$log->appendLogWithTypes($item,	$formatType, $priorityType);
				}

				$t_array = array("username" => $uname, 
					"password" => $properties['new_password']);
				$authNTokens = $method->createTokens($t_array);
				
				// Add it to the system and login with new password
				if ($method->supportsTokenUpdates()) {
					$method->updateTokens($tokens, $authNTokens);
					$harmoni->request->startNamespace("harmoni-authentication");
					$harmoni->request->set("username", $uname);
					$harmoni->request->set("password", 
						$properties['new_password']);
					$harmoni->request->endNamespace();
					$authN->authenticateUser($type);
					return TRUE;
				}
				
			} else {
				$error = "Invalid new password";
				$localizedError = dgettext("polyphony", "Invalid new password, please try again.")."\n<br/>";
			}
		 } 
		 if (isset($error)) {
		 	// Log the action
			if (Services::serviceRunning("Logging")) {
				$loggingManager = Services::getService("Logging");
				$log =$loggingManager->getLogForWriting("Authentication");
				$formatType = new Type("logging", "edu.middlebury", "AgentsAndNodes",
								"A format in which the acting Agent[s] and the target nodes affected are specified.");
				$priorityType = new Type("logging", "edu.middlebury", "Error",
								"Normal events.");
				
				$item = new AgentNodeEntryItem("Modify Agent", "Password change error:\n<br/>&nbsp; &nbsp; &nbsp;".$error."\n<br/>for:\n<br/>&nbsp; &nbsp; &nbsp;".$uname."\n<br/>&nbsp; &nbsp; &nbsp;".$type->getKeyword());
				$item->addAgentId($id);
				
				$log->appendLogWithTypes($item,	$formatType, $priorityType);
			}
		 	
		 	$this->closeWizard($cacheName);
		 	RequestContext::locationHeader($harmoni->request->quickURL("user",
		 		"change_password", array("error" => $localizedError)));
		}
	}
	
	/**
	 * Return the URL that this action should return to when completed.
	 * 
	 * @return string
	 * @access public
	 * @since 10/24/05
	 */
	function getReturnUrl () {
		$harmoni = Harmoni::instance();
		
		return $harmoni->request->quickURL("user", "main");
	}
}
?>