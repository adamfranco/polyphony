<?php
/**
* @package concerto.modules.gui
*
* @copyright Copyright &copy; 2006, Middlebury College
* @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
*
* @version $Id: test_page.act.php,v 1.3 2007/09/04 20:28:13 adamfranco Exp $
*/

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(HARMONI."GUIManager/Layouts/XLayout.class.php");
require_once(HARMONI."GUIManager/Layouts/YLayout.class.php");
require_once(HARMONI."GUIManager/Layouts/FlowLayout.class.php");


class test_pageAction
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
		return true;
	}

	/**
	* Return the "unauthorized" string to pring
	*
	* @return string
	* @access public
	* @since 4/26/05
	*/
	function getUnauthorizedMessage () {
		return "Error.";
	}

	/**
	* Return the heading text for this action, or an empty string.
	*
	* @return string
	* @access public
	* @since 4/26/05
	*/
	function getHeadingText () {
		return _("Here's your sample page:");
	}

	/**
	* Build the content for this action
	*
	* @return void
	* @access public
	* @since 4/26/05
	*/
	function buildContent () {



		$actionRows =$this->getActionRows();

		$harmoni = Harmoni::instance();
		$themeIndex = $harmoni->request->get("theme");

		if($themeIndex){

		if( isset($_SESSION[$themeIndex])){

			
			
			
			$theme =$_SESSION[$themeIndex];
			$guiManager = Services::getService("GUI");
			$guiManager->setTheme($theme);

		}else{
			print "Could not find any thing at ".$themeIndex;	
		}
		}else{
			print "The theme index was not passed in.";
		}

		// initialize layouts and theme

		$xLayout = new XLayout();
		$yLayout = new YLayout();
		$flowLayout = new FlowLayout();

		// now create all the containers and components
		$block1 = new Container($yLayout, BLOCK, 1);

		
		$row0 = new Footer("This the Header, which is likely consistent across pages.", 1);

		$block1->add($row0, "100%", null, CENTER, CENTER);
		
		
		$row1 = new Container($xLayout, OTHER, 1);

		$header1 = new Heading("A Harmoni GUI example.<br />Level-1 heading.\n", 1);

		$row1->add($header1, null, null, CENTER, CENTER);

		$menu1 = new Menu($xLayout, 1);

		$menu1_item1 = new MenuItemHeading("Main Menu:\n", 1);
		$menu1_item2 = new MenuItemLink("Home", "http://www.google.com", true, 1);
		$menu1_item3 = new MenuItemLink("Theme Settings", "http://www.middlebury.edu", false, 1);
		$menu1_item4 = new MenuItemLink("Manage Themes", "http://www.cnn.com", false, 1);

		$menu1->add($menu1_item1, "25%", null, CENTER, CENTER);
		$menu1->add($menu1_item2, "25%", null, CENTER, CENTER);
		$menu1->add($menu1_item3, "25%", null, CENTER, CENTER);
		$menu1->add($menu1_item4, "25%", null, CENTER, CENTER);

		$row1->add($menu1, "500px", null, RIGHT, BOTTOM);

		$block1->add($row1, "100%", null, RIGHT, CENTER);

		$row2 = new Block("
			This is some text in a Level-2 text block.
			<p>This is where you would put your <strong>content.</strong> web site. A link might look like <a href=\"http://et.middlebury.edu/\">this</a>.  It is probably important to make this look good. </p>\n", 2);

		$block1->add($row2, "100%", null, CENTER, CENTER);

		$row3 = new Container($xLayout, OTHER, 1);

		$menu2 = new Menu($yLayout, 1);

		$menu2_item1 = new MenuItemHeading("Sub-menu:\n", 1);
		$menu2_item2 = new MenuItemLink("The Architecture", "http://www.google.com", true, 1);
		$menu2_item3 = new MenuItemLink("The Framework", "http://www.middlebury.edu", false, 1);
		$menu2_item4 = new MenuItemLink("Google: Searching", "http://www.cnn.com", false, 1);
		$menu2_item5 = new MenuItemLink("Slashdot", "http://www.slashdot.org", false, 1);
		$menu2_item6 = new MenuItemLink("Background Ex", "http://www.depechemode.com", false, 1);

		$menu2->add($menu2_item1, "100%", null, LEFT, CENTER);
		$menu2->add($menu2_item2, "100%", null, LEFT, CENTER);
		$menu2->add($menu2_item3, "100%", null, LEFT, CENTER);
		$menu2->add($menu2_item4, "100%", null, LEFT, CENTER);
		$menu2->add($menu2_item5, "100%", null, LEFT, CENTER);
		$menu2->add($menu2_item6, "100%", null, LEFT, CENTER);

		$row3->add($menu2, "150px", null, LEFT, TOP);

		$stories = new Container($yLayout, OTHER, 1);

		$heading2_1 = new Heading("The Architecture. Level-2 heading.", 2);

		$stories->add($heading2_1, "100%", null, CENTER, CENTER);

		$story1 = new Block("
	<p>
		Harmoni's architecture is built on a popular <strong>module/action</strong> model, in which your PHP program
		contains multiple <em>modules</em>, each of which contain multiple executable <em>actions</em>. All you,
		as an application developer, need to write are the action files (or classes, or methods, however you
		choose to do it). The following diagram gives a (simplified) example of the execution path/flow of
		a typical PHP program written under Harmoni:
	</p>", 2);

		$stories->add($story1, "100%", null, CENTER, CENTER);

		$heading2_2 = new Heading("The Framework. Level-2 heading.", 2);

		$stories->add($heading2_2, "100%", null, CENTER, CENTER);

		$story2 = new Block("
					<p>
						Alongside the architecture, Harmoni offers a number of <strong>Services</strong>. The services are built with two
						goals: 1) to save you the time of writing the same code over and over again, and 2) to offer a uniform
						environment and abstraction layer between a specific function and the back-end implementation of that function.
						In simpler words, the File StorageHandler, for example, is used by your program by calling methods like
						<em>".'$storageHandler->store($thisFile, $here)'."</em>. Someone using your program can configure Harmoni
						to store that file in a database, on a filesystem, to make backups transparently, or store on a 
						mixture of databases and filesystems and other mediums. This allows your program, using the same 
						code, to store files in a very flexible manner, extending your audience and making for easier installation.
					</p>
					
					<p>
						A short list of the included Services follows:
					</p>", 2);

		$stories->add($story2, "100%", null, CENTER, CENTER);

		$row3->add($stories, null, null, CENTER, TOP);

		$contributors = new Block("
				<h2>
					Level Three block
				</h2>
				
				<h3>
					Empahsis
				</h3>
				
				<p>
					This type of block may be useful for emphasizing this and that.  It may not actually be included, but I might as well add it, just for kicks.  Maybe it could look really cool if you used it.
				</p>
			", 3);

		$row3->add($contributors, "250px", null, LEFT, TOP);

		$block1->add($row3, "100%", null, CENTER, CENTER);

		$row4 = new Footer("This the footer, wheich may or may not be similar to the Header", 1);

		$block1->add($row4, "100%", null, CENTER, CENTER);


		$actionRows->add($block1, "100%", null, LEFT, CENTER);


	}
}

?>