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

require_once(dirname(__FILE__).'/confirm_email.act.php');

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
class send_confirmationAction
	extends confirm_emailAction
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
		
		$centerPane->add(new Heading(dgettext("polyphony", "Send Confirmation Email"), 1));
		
		ob_start();
		
		
		if (RequestContext::value('email')) {
			$authMethod = $authNMethodMgr->getAuthNMethodForType($visitorAuthType);
			$tokens = $authMethod->createTokensObject();
			$tokens->initializeForIdentifier(RequestContext::value('email'));
			
			// Check for previous registration
			if ($authMethod->tokensExist($tokens)) {
				if (!$authMethod->isEmailConfirmed($tokens)) {
					$harmoni = Harmoni::instance();
					$authMethod->sendConfirmationEmail($tokens, $harmoni->request->mkURL('user', 'confirm_email'));
					
					print "\n<div class=''>\n\t";
					print dgettext("polyphony", "A confirmation email has been sent to you.")." <br/>";
		print dgettext("polyphony", "You must click on the confirmation link in the email before you will be able to log in.");
					print "\n</div>";
					print "\n\n<p>";
					print dgettext("polyphony", "Re-send confirmation email?");
					$harmoni = Harmoni::instance();
					print " <a href='".$harmoni->request->quickURL('user', 'send_confirmation', array('email' => RequestContext::value('email')))."'><button>";
					print dgettext("polyphony", "Send");
					print "</button></a>";
					print "</p>";
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
	
}

?>