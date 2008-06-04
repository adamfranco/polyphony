<?php
/**
 * @since 6/4/08
 * @package polyphony.user
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

/**
 * Wizard for visitor registration
 * 
 * @since 6/4/08
 * @package polyphony.user
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class visitor_regAction
	extends MainWindowAction
{
	
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 6/4/08
	 */
	function isAuthorizedToExecute () {
		return true;
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 6/4/08
	 */
	function buildContent () {
// 		$authN = Services::getService("AuthN");
// 		$visitorAuthType = new Type ("Authentication", "edu.middlebury.harmoni",
// 			"Visitor");
		
		
		$centerPane =$this->getActionRows();
		$cacheName = 'visitor_registration_wizard';
		$this->runWizard($cacheName, $centerPane);
	}
	
	/**
	 * Create the wizard
	 * 
	 * @return object Wizard
	 * @access public
	 * @since 6/4/08
	 */
	public function createWizard () {
		$wizard = SimpleWizard::withText(
			"\n<h2>"._("Visitor Registration")."</h2>".
			"\n<p>"._("Please fill out the form below. After you click the 'Register' button an email will be sent with a link to confirm your registration. You must confirm your registration before you will be able to log in.")."</p>".
			"\n<table class='visitor_registration'>".
			"\n\t<tr>\n\t\t<th>"._("EMail Address:<br/>(This is your login handle)")."</th>\n\t\t<td>[[email]]</td>\n\t</tr>".
			"\n\t<tr>\n\t\t<th>"._("Full Name:")."</th>\n\t\t<td>[[name]]</td>\n\t</tr>".
			"\n\t<tr>\n\t\t<th>"._("Password:")."<br/>"._("Password Again:")."</th>\n\t\t<td>[[password]]</td>\n\t</tr>".
			"\n</table>".
			"\n[[captcha]]".
			"\n<table width='100%' border='0' style='margin-top:20px' >\n".
			"<tr>\n".
			"</td>\n".
			"<td align='left' width='50%'>\n".
			"[[_cancel]]".
			"<td align='right' width='50%'>\n".
			"[[_save]]".
			"</td></tr></table>");
		
		$property = $wizard->addComponent("email", new WTextField());
		$property->setStartingDisplayText(_("john_doe@example.com"));
		$property->setErrorText(_("A valid email address is required."));
		$property->setErrorRule(new WECNonZeroRegex("^(([A-Za-z0-9]+_+)|([A-Za-z0-9]+\-+)|([A-Za-z0-9]+\.+)|([A-Za-z0-9]+\++))*[A-Za-z0-9]+@((\w+\-+)|(\w+\.))*\w{1,63}\.[a-zA-Z]{2,6}$"));
		
		$property = $wizard->addComponent("name", new WTextField());
		$property->setStartingDisplayText(_("John Doe"));
		$property->setErrorText(_("A value for this field is required - allowed characters: letters, spaces, ,.'-."));
		$property->setErrorRule(new WECNonZeroRegex("^[\\w\\040,\.'-]{3,}$"));
		
		$property = $wizard->addComponent("password", new WPasswordPair());
		$property->setErrorText(_("Passwords must be between 8 and 100 characters."));
		$property->setErrorRule(new WECNonZeroRegex("^.{8,100}$"));
		
		if (!defined('RECAPTCHA_PUBLIC_KEY'))
			throw new ConfigurationErrorException("RECAPTCHA_PUBLIC_KEY not defined.");
		if (!defined('RECAPTCHA_PRIVATE_KEY'))
			throw new ConfigurationErrorException("RECAPTCHA_PRIVATE_KEY not defined.");
		$property = $wizard->addComponent("captcha", new WReCaptcha(RECAPTCHA_PUBLIC_KEY, RECAPTCHA_PRIVATE_KEY));
		
		$wizard->addComponent("_save", WSaveButton::withLabel("Register"));
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
	 * @since 6/4/08
	 */
	function saveWizard ($cacheName) {
		$wizard =$this->getWizard($cacheName);
		if (!$wizard->validate())
			return false;
		
		$values = $wizard->getAllValues();
		printpre($values);
		
		$authNMgr = Services::getService("AuthN");
		$authNMethodMgr = Services::getService("AuthNMethodManager");
		$tokenMgr = Services::getService("AgentTokenMapping");
		$visitorAuthType = new Type ("Authentication", "edu.middlebury.harmoni",
			"Visitors");
		$authMethod = $authNMethodMgr->getAuthNMethodForType($visitorAuthType);
		
		$tokens = $authMethod->createTokensObject();
		$tokens->initializeForTokens(array('username' => $values['email'], 'password' => $values['password']));
		
		// Check for previous registration
		if ($authMethod->tokensExist($tokens)) {
			print "\n<div class='error'>\n\t";
			print _("This email address has already been registered.");
			print "\n</div>";
			
			if (!$authMethod->isEmailConfirmed($tokens)) {
				print "\n<div class='error'>\n\t";
				print _("Re-send confirmation email?");
				$harmoni = Harmoni::instance();
				print " <a href='".$harmoni->request->quickURL('user', 'send_confirmation', array('email' => $values['email']))."'><button>";
				print _("Send");
				print "</button></a>";
				print "\n</div>";
			}
			
			return false;
		}
		
		// Add the new tokens
		if (!$authMethod->supportsTokenAddition())
			throw new OperationFailedException("Could not add users with the ".$visitorAuthType->asString()." authentication method.");
		
		$authMethod->addTokens($tokens);
		$properties = $authMethod->getPropertiesForTokens($tokens);
		$properties->addProperty('name', $values['name']);
		$authMethod->updatePropertiesForTokens($tokens, $properties);
		
		$this->success = true;
		
		return true;
	}
	
	/**
	 * Return the URL that this action should return to when completed.
	 * 
	 * @return string
	 * @access public
	 * @since 6/4/08
	 */
	function getReturnUrl () {
		$harmoni = Harmoni::instance();
		
		if ($this->success)
			return $harmoni->request->quickURL("user", "visitor_reg_success");
		else
			return $harmoni->request->quickURL("user", "main");
	}
	
}

?>