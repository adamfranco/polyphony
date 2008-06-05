<?php
/**
 * @since 6/4/08
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY."/main/library/Wizard/WizardComponent.abstract.php");
require_once(POLYPHONY."/recaptcha/recaptchalib.php");


/**
 * A captcha field for validating that the user is human.
 * 
 * @since 6/4/08
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class WReCaptcha
	extends WizardComponent
{
	/**
	 * @var string $_publicKey;  
	 * @access private
	 * @since 6/4/08
	 */
	private $_publicKey;
	
	/**
	 * @var string $_privateKey;  
	 * @access private
	 * @since 6/4/08
	 */
	private $_privateKey;
	
	/**
	 * @var string $_error;  
	 * @access private
	 * @since 6/4/08
	 */
	private $_error = null;
	
	/**
	 * @var boolean $_valid;  
	 * @access private
	 * @since 6/4/08
	 */
	private $_valid = false;
	
	/**
	 * Constructor. Sign up for reCAPTCHA keys at http://recaptcha.net/
	 * 
	 * @param string $publicKey
	 * @param string $privateKey
	 * @return void
	 * @access public
	 * @since 6/4/08
	 */
	public function __construct ($publicKey, $privateKey) {
		ArgumentValidator::validate($publicKey, NonzeroLengthStringValidatorRule::getRule());
		ArgumentValidator::validate($privateKey, NonzeroLengthStringValidatorRule::getRule());
		
		$this->_publicKey = $publicKey;
		$this->_privateKey = $privateKey;
	}
		
	/**
	 * Tells the wizard component to update itself - this may include getting
	 * form post data or validation - whatever this particular component wants to
	 * do every pageload. 
	 * @param string $fieldName The field name to use when outputting form data or
	 * similar parameters/information.
	 * @access public
	 * @return boolean - TRUE if everything is OK
	 */
	public function update ($fieldName) {
		if (isset($_POST["recaptcha_challenge_field"]) 
			&& isset($_POST["recaptcha_challenge_field"]))
		{
			$this->_valid = false;
			
			$resp = recaptcha_check_answer(	$this->_privateKey,
										$_SERVER["REMOTE_ADDR"],
										$_POST["recaptcha_challenge_field"],
		                                $_POST["recaptcha_response_field"]);
			if (!$resp->is_valid) {
				$this->_error = $resp->error;
			} else
				$this->_valid = true;
		}
		return $this->_valid;
	}
	
	/**
	 * Validate this field
	 * 
	 * @return boolean
	 * @access public
	 * @since 6/4/08
	 */
	public function validate () {
		return $this->_valid;
	}
	
	/**
	 * Returns the values of wizard-components. Should return an array if children are involved,
	 * otherwise a whatever type of object is expected.
	 * @access public
	 * @return mixed
	 */
	public function getAllValues () {
		return null;
	}
	
	/**
	 * Returns a block of XHTML-valid code that contains markup for this specific
	 * component. 
	 * @param string $fieldName The field name to use when outputting form data or
	 * similar parameters/information.
	 * @access public
	 * @return string
	 */
	public function getMarkup ($fieldName) {
		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
			$server = RECAPTCHA_API_SECURE_SERVER;
		else
			$server = RECAPTCHA_API_SERVER;
		
		ob_start();
		
		$errorpart = "";
        if ($this->_error) {
           $errorpart = "&amp;error=" . $this->_error;
           $this->_error = null;
        }
        print '<script type="text/javascript" src="'. $server . '/challenge?k=' . $this->_publicKey . $errorpart . '"></script>

	<noscript>
  		<iframe src="'. $server . '/noscript?k=' . $this->_publicKey . $errorpart . '" height="300" width="500" frameborder="0"></iframe><br/>
  		<textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
  		<input type="hidden" name="recaptcha_response_field" value="manual_challenge"/>
	</noscript>';
		return ob_get_clean();
	}
}

?>