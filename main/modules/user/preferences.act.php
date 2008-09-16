<?php
/**
 * @since 9/16/08
 * @package polyphony.user_prefs
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

/**
 * Display and edit user preferences
 * 
 * @since 9/16/08
 * @package polyphony.user_prefs
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class preferencesAction
	extends MainWindowAction
{
		
	/**
	 * Authorization
	 * 
	 * @return boolean
	 * @access public
	 * @since 9/16/08
	 */
	public function isAuthorizedToExecute () {
		return true;
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return dgettext("polyphony", "User Preferences");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$defaultTextDomain = textdomain("polyphony");
		
		$actionRows =$this->getActionRows();
		$harmoni = Harmoni::instance();
		
		ob_start();
		
		$userPrefs = UserPreferences::instance();
		
// 		$userPrefs->setPreference('test_pref2', 'goodbye world');

		print "\n<input type='button' value='"._("Clear Session-Values")."'/>";
// 		print "\n<input type='button' value='"._("Delete All Values")."'/>";
		
		print "\n<table border='1'>";
		print "\n\t<thead>";
		print "\n\t\t<tr>";
		print "\n\t\t\t<th>"."</th>";
		print "\n\t\t\t<th>"._('Key')."</th>";
		print "\n\t\t\t<th>"._('Value')."</th>";
		print "\n\t\t\t<th>"."</th>";
		print "\n\t\t\t<th>"._('Session-Value')."</th>";
		print "\n\t\t\t<th>"._('Stored-Value')."</th>";
		print "\n\t\t</tr>";
		print "\n\t</thead>";
		print "\n\t<tbody>";
		
		foreach ($userPrefs->getPreferenceKeys() as $key) {
			print "\n\t\t<tr>";
			print "\n\t\t\t<td><input type='button' name='".RequestContext::name('delete')."' value='"._("Delete")."'/></td>";
			print "\n\t\t\t<td class='user_pref_key'>".$key."</td>";
			print "\n\t\t\t<td class='user_pref_val'><input type='text' name='".RequestContext::name('pref_val')."' value=\"".$userPrefs->getPreference($key)."\"/></td>";
			print "\n\t\t\t<td><input type='button' name='".RequestContext::name('save')."' value='"._("Save Changes")."'/></td>";
			print "\n\t\t\t<td class='user_pref_sess_val'>".$userPrefs->getPreferenceSessionValue($key)."</td>";
			print "\n\t\t\t<td class='user_pref_stored_val'>".$userPrefs->getPreferencePersistantValue($key)."</td>";
			print "\n\t\t</tr>";
		}
		
		print "\n\t\t<tr>";
		print "\n\t\t\t<td></td>";
		print "\n\t\t\t<td><input type='text' name='".RequestContext::name('pref_key')."'/></td>";
		print "\n\t\t\t<td><input type='text' name='".RequestContext::name('pref_val')."'/></td>";
		print "\n\t\t\t<td><input type='button' name='".RequestContext::name('new')."' value='"._("Create New Preference")."'/></td>";
		print "\n\t\t\t<td></td>";
		print "\n\t\t\t<td></td>";
		print "\n\t\t</tr>";
		print "\n\t</tbody>";
		print "\n</table>";
		
		$actionRows->add(new Block(ob_get_clean(), STANDARD_BLOCK), "100%", null, LEFT, TOP);
		
		textdomain($defaultTextDomain);
	}
}

?>