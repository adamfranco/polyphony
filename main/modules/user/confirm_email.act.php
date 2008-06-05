<?php
/**
 * @since 6/5/08
 * @package polyphony.user
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

/**
 * Display a success message and a link to re-send the confirmation email.
 * 
 * @since 6/5/08
 * @package polyphony.user
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class confirm_emailAction
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
		$authNMethodMgr = Services::getService("AuthNMethodManager");
		$visitorAuthType = new Type ("Authentication", "edu.middlebury.harmoni",
			"Visitors");
		
		$centerPane =$this->getActionRows();
		
		$centerPane->add(new Heading(dgettext("polyphony", "Email Address Confirmation"), 1));
		
		ob_start();
		if (RequestContext::value('email')) {
			$authMethod = $authNMethodMgr->getAuthNMethodForType($visitorAuthType);
			$tokens = $authMethod->createTokensObject();
			$tokens->initializeForIdentifier(RequestContext::value('email'));
			
			// Check for previous registration
			if ($authMethod->tokensExist($tokens)) {
				if (!$authMethod->isEmailConfirmed($tokens)) {
					if ($authMethod->confirmEmail($tokens, RequestContext::value('confirmation_code'))) {
						print "\n<div class=''>\n\t";
						print dgettext("polyphony", "Email address confirmed.");
						print "\n</div>";
						print $this->getLoginForm();
					} else {
						print "\n<div class='error'>\n\t";
						print dgettext("polyphony", "Error: Invalid confirmation code.");
						print "\n</div>";
					}
				} else {
					print "\n<div class='error'>\n\t";
					print dgettext("polyphony", "Error: Email address already confirmed.");
					print "\n</div>";
					print $this->getLoginForm();
				}
			} else {
				throw new UnknownIdException("Unknown email, '".RequestContext::value('email')."'.");
			}
		} else {
			throw new InvalidArgumentException(dgettext("polyphony", "No email address specified."));
		}
		
		$centerPane->add(new Block(ob_get_clean(), STANDARD_BLOCK));
	}
	
	/**
	 * Answer the login HTML
	 * 
	 * @return string
	 * @access public
	 * @since 6/5/08
	 */
	public function getLoginForm () {
		ob_start();
		
		$harmoni = Harmoni::instance();
		
		$authN = Services::getService("AuthN");
		$agentM = Services::getService("Agent");
		$idM = Services::getService("Id");
		$authTypes = $authN->getAuthenticationTypes();
		$users = '';
		while ($authTypes->hasNext()) {
			$authType = $authTypes->next();
			$id = $authN->getUserId($authType);
			if (!$id->isEqual($idM->getId('edu.middlebury.agents.anonymous'))) {
				$agent = $agentM->getAgent($id);
				$exists = false;
				foreach (explode("+", $users) as $user) {
					if ($agent->getDisplayName() == $user)
						$exists = true;
				}
				if (!$exists) {
					if ($users == '')
						$users .= $agent->getDisplayName();
					else
						$users .= " + ".$agent->getDisplayName();
				}
			}
		}
		
		if ($users != '') {
			print "\n<div class='' style='margin-top: 10px;'>";
			print "<strong>".dgettext("polyphony", "Logged in as:")."</strong> &nbsp;";
			if (count(explode("+", $users)) == 1)
				print $users."\t";
			else 
				print dgettext("polyphony", "Users: ").$users."\t";
			
			print " | <a href='".$harmoni->request->quickURL("auth",
				"logout")."'>".dgettext("polyphony", "Log Out")."</a></div>";
		} else {
			// set bookmarks for success and failure
			$harmoni->history->markReturnURL("polyphony/display_login", 
				$harmoni->request->quickURL($harmoni->config->get('defaultModule'), $harmoni->config->get('defaultAction')));
			$harmoni->history->markReturnURL("polyphony/login_fail",
				$harmoni->request->quickURL("user", "main", array('login_failed' => 'true')));
	
			$harmoni->request->startNamespace("harmoni-authentication");
			$usernameField = $harmoni->request->getName("username");
			$passwordField = $harmoni->request->getName("password");
			$harmoni->request->endNamespace();
			$harmoni->request->startNamespace("polyphony");
			print  "\n<div style='margin-top: 10px;'>".
				"\n\t<strong>Login:</strong>".
				"\n<form action='".
				$harmoni->request->quickURL("auth", "login").
				"' style='' method='post'>".
				"\n\t".dgettext("polyphony", "Username:")." <input class='' type='text' size='8' 
					name='$usernameField'/>".
				"\n\t".dgettext("polyphony", "Password:")." <input class='' type='password' size ='8' 
					name='$passwordField'/>".
				"\n\t <input class='button' type='submit' value='Log in' />".
				"\n</form></div>\n";
			$harmoni->request->endNamespace();
		}
			
		return ob_get_clean();
	}
	
}

?>