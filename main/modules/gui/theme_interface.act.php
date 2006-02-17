<?php
/**
 * @package polyphony.modules.gui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: theme_interface.act.php,v 1.6 2006/02/17 21:36:41 cws-midd Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

/**
 * 
 * 
 * @package polyphony.modules.gui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: theme_interface.act.php,v 1.6 2006/02/17 21:36:41 cws-midd Exp $
 */
class theme_interfaceAction 
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
		$authZManager =& Services::getService("AuthZ");
		$idManager =& Services::getService("IdManager");
		if ($authZManager->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.create_agent"),
					$idManager->getId("edu.middlebury.authorization.root")))
		{
			return TRUE;
		} else
			return FALSE;
	}
	
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to access the Theme Interface.");
	}	
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return _("Theme Management");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$harmoni =& Harmoni::instance();
		//$currenttheme =& $harmoni->getTheme();
		$guimanager =& Services::getService('GUIManager');
		$currenttheme =& $guimanager->getTheme();
		$array =& $currenttheme->getAllRegisteredSPs();
		
		$actionRows =& $this->getActionRows();
		$cacheName = 'theme_interface_wizard';
		
		$this->runWizard ( $cacheName, $actionRows );
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
		$harmoni =& Harmoni::instance();
		
		
		// Instantiate the wizard, then add our steps.
		$wizard =& SimpleStepWizard::withDefaultLayout();
		$step =& $wizard->addStep("themesettings", new WizardStep());
		$step->setDisplayName(_("Current Theme Settings"));
		$guimanager =& Services::getService('GUIManager');
		$currenttheme =& $guimanager->getTheme();
		$array =& $currenttheme->getAllRegisteredSPs();
		$actionlist =& $step->addComponent("selectaction",new WSelectList());
		$actionlist->addOption("save", _("Save as New Theme"));
		$actionlist->addOption("update", _("Update Current Theme"));
		ob_start();
		print "<h2>Current Theme: <em>".$currenttheme->getDisplayName()."</em></h2>";
		print "<p></p>";
		print "<h3>List of Modifiable Style Properties:</h3>";
		print "<p></p>";
		$modifiable = true;
// 		printpre($array);
		if(count($array)!=0){
			foreach (array_keys($array) as $stylePropertyKey){
				$sp =& $array[$stylePropertyKey];
				print "<b>".$sp->getDisplayName()."</b>: <br />";
				$scs = $sp->getSCs();
				$output ="";
				foreach(array_keys($scs) as $styleComponentKey){
					$sc =& $scs[$styleComponentKey];
					$output.=$sc->getDisplayName().": ";
					$output.=$sc->getValue()."<br />";
				}
				print $output;
				print "<p>";
			}
		}
		else{
			print "<b><em>This Theme has no Modifiable Properties.</em></b><p></p>";
			$modifiable = false;
		}
		
		if($modifiable){
			print "<p>Please choose whether you want to create a new Theme or update the current one.";
			print"\n<br />[[selectaction]]";
		}
		$step->setContent(ob_get_contents());
		ob_end_clean();	
		
		if($modifiable){//add Second Step only if there are modifiable properties
		
		//************** Second Step ***************//
		$step =& $wizard->addStep("thememodify", new WizardStep());
		$step->setDisplayName(_("Modify Theme"));
		ob_start();
		print "\n<h3>"._("Here you can update the Style Components that you want to. The current values are default.")."</h2><br />";
		$counter=0;
		print "<table>";
			foreach (array_keys($array) as $stylePropertyKey){
				$sp =& $array[$stylePropertyKey];
				print "<tr><td><em>".$sp->getDisplayName()."</em>:</td>";
				$scs =& $sp->getSCs();
				
				foreach(array_keys($scs) as $styleComponentKey){
					
					if($counter!=0){
						print "</tr><tr><td></td>";
					}
					$counter++;
					$sc =& $scs[$styleComponentKey];
					print "<td>".$sc->getDisplayName()."</td><td>";
					$textfield =& $step->addComponent(str_replace(" ", "_",$sp->getDisplayName()."_".$sc->getDisplayName()), new WTextField());
					$textfield->setValue($sc->getValue());
					$textfield->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
					$textfield->setErrorRule(new WECNonZeroRegex("[\\w]+"));
					print "[[".str_replace(" ", "_",$sp->getDisplayName()."_".$sc->getDisplayName())."]]";
					"</td></tr>";
					
				}
				
				$counter=0;
				
			}
		//print "</table>";
		$textfield =& $step->addComponent("themeid", new WTextField());
		$textfield->setErrorText("<nobr>"._("You need to specify an id to save the theme.")."</nobr>");
		$textfield->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		print "<tr><td><em>Save with id:</em></td><td>
				Theme Id:</td>
				<td> [[themeid]]
				</td></tr>
				</table>";
		$colorWheel =& $step->addComponent("colorwheel", new WColorWheel());
		
		print "[[colorwheel]]";
		$step->setContent(ob_get_contents());
		ob_end_clean();
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
		$properties =& $wizard->getAllValues();

		$guimanager =& Services::getService('GUIManager');
		$currenttheme =& $guimanager->getTheme();
		$array =& $currenttheme->getAllRegisteredSPs();
			
			foreach (array_keys($array) as $stylePropertyKey){
				$sp =& $array[$stylePropertyKey];
				$scs =& $sp->getSCs();
				foreach(array_keys($scs) as $styleComponentKey){
					$sc =& $scs[$styleComponentKey];
					$value=$properties["thememodify"][str_replace(" ", "_",$sp->getDisplayName()."_".$sc->getDisplayName())];
					$sc->setValue($value);
				}
				//print "<br/ >";
			}
			if(isset($properties["thememodify"]["themeid"])){
				$id = new HarmoniId($properties["thememodify"]["themeid"]);
				if($properties["themesettings"]["selectaction"]=="save")
					$guimanager->saveThemeState($currenttheme,$properties["thememodify"]["themeid"]);
				else if($properties["themesettings"]["selectaction"]=="update")
					$guimanager->replaceThemeState($id,$currenttheme);
				return true;
			}
			else
				return false;
		
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
		$url =& $harmoni->request->mkURLWithPassthrough("admin", "main");
		return $url->write();
	}
	
}

?>