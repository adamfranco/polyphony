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
		$authNManager = Services::getService("AuthN");
		$authTypes = $authNManager->getAuthenticationTypes();
		$visitorType = new Type ("Authentication", "edu.middlebury.harmoni", "Visitors");
		while($authTypes->hasNext()) {
			$authType = $authTypes->next();
			if ($visitorType->isEqual($authType)) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Answer the unauthorized Message
	 * 
	 * @return string
	 * @access public
	 * @since 6/5/08
	 */
	public function getUnauthorizedMessage () {
		return dgettext("polyphony", "Visitor registration is not enabled.");
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
			"\n<h2>".dgettext("polyphony", "Visitor Registration")."</h2>".
			"\n<p>".dgettext("polyphony", "Please fill out the form below. After you click the 'Register' button an email will be sent with a link to confirm your registration. You must confirm your registration before you will be able to log in.")."</p>".
			"\n<table class='visitor_registration'>".
			"\n\t<tr>\n\t\t<th>".dgettext("polyphony", "EMail Address:<br/>(This is your login handle)")."</th>\n\t\t<td>[[email]]</td>\n\t</tr>".
			"\n\t<tr>\n\t\t<th>".dgettext("polyphony", "Full Name:")."</th>\n\t\t<td>[[name]]</td>\n\t</tr>".
			"\n\t<tr>\n\t\t<th>".dgettext("polyphony", "Password:")."<br/>".dgettext("polyphony", "Password Again:")."</th>\n\t\t<td>[[password]]</td>\n\t</tr>".
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
		$property->setStartingDisplayText(dgettext("polyphony", "john_doe@example.com"));
		$property->setErrorText(dgettext("polyphony", "A valid email address is required."));
		$property->setErrorRule(new WECNonZeroRegex("^(([A-Za-z0-9]+_+)|([A-Za-z0-9]+\-+)|([A-Za-z0-9]+\.+)|([A-Za-z0-9]+\++))*[A-Za-z0-9]+@((\w+\-+)|(\w+\.))*\w{1,63}\.[a-zA-Z]{2,6}$"));
		
		$property = $wizard->addComponent("name", new WTextField());
		$property->setStartingDisplayText(dgettext("polyphony", "John Doe"));
		$property->setErrorText(dgettext("polyphony", "A value for this field is required - allowed characters: letters, spaces, ,.'-."));
		$property->setErrorRule(new WECNonZeroRegex("^[\\w\\040,\.'-]{3,}$"));
		
		$property = $wizard->addComponent("password", new WPasswordPair());
		$property->setErrorText(dgettext("polyphony", "Passwords must be between 8 and 100 characters."));
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
// 		printpre($values);
		
		$authNMgr = Services::getService("AuthN");
		$authNMethodMgr = Services::getService("AuthNMethodManager");
		$visitorAuthType = new Type ("Authentication", "edu.middlebury.harmoni",
			"Visitors");
		$authMethod = $authNMethodMgr->getAuthNMethodForType($visitorAuthType);
		
		$tokens = $authMethod->createTokensObject();
		$tokens->initializeForTokens(array('username' => $values['email'], 'password' => $values['password']));
		
		$harmoni = Harmoni::instance();
		
		// Check for previous registration
		if ($authMethod->tokensExist($tokens)) {
			print "\n<div class='error'>\n\t";
			print dgettext("polyphony", "This email address has already been registered.");
			print "\n</div>";
			
			if (!$authMethod->isEmailConfirmed($tokens)) {
				print "\n<div class='error'>\n\t";
				print dgettext("polyphony", "Re-send confirmation email?");
				print " <a href='".$harmoni->request->quickURL('user', 'send_confirmation', array('email' => $values['email']))."'><button>";
				print dgettext("polyphony", "Send");
				print "</button></a>";
				print "\n</div>";
			}
			
			return false;
		}
		
		// Check that addition is supported
		if (!$authMethod->supportsTokenAddition())
			throw new OperationFailedException("Could not add users with the ".$visitorAuthType->asString()." authentication method.");
		
		// Add the new tokens
		try {
			$authMethod->addTokens($tokens);
		} catch (OperationFailedException $e) {
			print "\n<div class='error'>\n\t";
			print $e->getMessage();
			print "\n</div>";
			return false;
		}
		$properties = $authMethod->getPropertiesForTokens($tokens);
		$properties->addProperty('name', $values['name']);
		$authMethod->updatePropertiesForTokens($tokens, $properties);
		
		$authMethod->sendConfirmationEmail($tokens, $harmoni->request->mkURL('user', 'confirm_email'));
		
		$this->success = true;
		$this->email = $values['email'];
		
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
			return $harmoni->request->quickURL("user", "visitor_reg_success", array('email' => $this->email));
		else
			return $harmoni->request->quickURL("user", "main");
	}
	
}

?>