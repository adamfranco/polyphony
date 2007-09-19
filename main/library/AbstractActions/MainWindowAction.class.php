<?php
/**
 * @package polyphony.AbstractActions
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: MainWindowAction.class.php,v 1.13 2007/09/19 14:04:41 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/WizardAction.class.php");
require_once(HARMONI."GUIManager/Container.class.php");
require_once(HARMONI."GUIManager/Layouts/XLayout.class.php");
require_once(HARMONI."GUIManager/Layouts/YLayout.class.php");
require_once(HARMONI."GUIManager/Layouts/TableLayout.class.php");
require_once(HARMONI."GUIManager/Components/Block.class.php");
require_once(HARMONI."GUIManager/Components/UnstyledBlock.class.php");
require_once(HARMONI."GUIManager/Components/Menu.class.php");
require_once(HARMONI."GUIManager/Components/MenuItemHeading.class.php");
require_once(HARMONI."GUIManager/Components/MenuItemLink.class.php");
require_once(HARMONI."GUIManager/Components/Heading.class.php");
require_once(HARMONI."GUIManager/Components/Footer.class.php");

/**
 * The MainWindowAction is an abstract class that provides a standard way of setting
 * up and executing an action in the main window of the application. It provides
 * a structure for accessing various parts of this main window, as well as delegating
 * the implementation of some methods to decendent classes.
 * 
 * @package polyphony.AbstractActions
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: MainWindowAction.class.php,v 1.13 2007/09/19 14:04:41 adamfranco Exp $
 */
class MainWindowAction 
	extends WizardAction {

/*********************************************************
 * Instance Variables
 *********************************************************/
	
	/**
	 * @var object $_actionRows; 
	 * @access private
	 * @since 7/7/05
	 */
	var $_actionRows;
	
/*********************************************************
 * Instance Methods
 *********************************************************/

	/**
	 * Build the content for this action.
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		throwError(new Error(__CLASS__."::".__FUNCTION__."() must be overridded in child classes."));
	}
		
	/**
	 * Execute this action. This is a template method that handles setting up
	 * components of the screen as well as authorization, delegating the various
	 * parts to descendent classes.
	 * 
	 * @return mixed
	 * @access public
	 * @since 4/25/05
	 */
	function execute () {
		$harmoni = Harmoni::instance();
		$pageTitle = $harmoni->config->get('programTitle');
		
		
		// Our Rows for action content
		$actionRows =$this->getActionRows();
		
		// Check authorization
		if (!$this->isAuthorizedToExecute()) {
			$actionRows->add(new Block($this->getUnauthorizedMessage(), EMPHASIZED_BLOCK),
				"100%", null, CENTER, TOP);
			return $actionRows;
		}
		
		// Add a heading if specified
		if ($headingText = $this->getHeadingText()) {
			$actionRows->add(
				new Heading($headingText, 1),
				"100%",
				null, 
				LEFT, 
				CENTER);
			
			$pageTitle .= ": ".$headingText;
		}
		
		// Set the page title and other new header items
		$outputHandler =$harmoni->getOutputHandler();
		ob_start();
		
		// Remove any existing title tags from the head text
		print preg_replace("/<title>[^<]*<\/title>/", "", $outputHandler->getHead());
		
		//Add our new title
		print "\n\t\t<title>";
		print strip_tags(preg_replace("/<(\/)?(em|i|b|strong)>/", "*", $pageTitle));
		print "</title>";
		
		// Add our common Harmoni javascript libraries
		require(POLYPHONY_DIR."/main/library/Harmoni.js.inc.php");
		
		$outputHandler->setHead(ob_get_clean());
		
		// Pass content generation off to our child classes
		$this->buildContent();
		
		return $actionRows;
	}
	
	/**
	 * Return the actionRows container
	 * 
	 * @return object Container
	 * @access public
	 * @since 4/26/05
	 */
	function getActionRows () {
		if (!is_object($this->_actionRows))
			$this->_actionRows = new Container(new YLayout(), BLOCK, BACKGROUND_BLOCK);
		
		return $this->_actionRows;
	}
}

?>