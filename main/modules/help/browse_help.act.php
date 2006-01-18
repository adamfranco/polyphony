<?php
/**
 * @since 12/8/05
 * @package polyphony.modules.help
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: browse_help.act.php,v 1.6 2006/01/18 19:15:20 adamfranco Exp $
 */
 
require_once(POLYPHONY."/main/library/AbstractActions/Action.class.php");

require_once(DOMIT);

require_once(HARMONI."GUIManager/Container.class.php");
require_once(HARMONI."GUIManager/Layouts/XLayout.class.php");
require_once(HARMONI."GUIManager/Layouts/YLayout.class.php");
require_once(HARMONI."GUIManager/Components/Block.class.php");
require_once(HARMONI."GUIManager/Components/UnstyledBlock.class.php");
require_once(HARMONI."GUIManager/Components/Menu.class.php");
require_once(HARMONI."GUIManager/Components/MenuItemHeading.class.php");
require_once(HARMONI."GUIManager/Components/MenuItemLink.class.php");
require_once(HARMONI."GUIManager/Components/Heading.class.php");
require_once(HARMONI."GUIManager/Components/Footer.class.php");


/**
 * This is the help-browser action which enables browsing of help documentation.
 * 
 * @since 12/8/05
 * @package polyphony.modules.help
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: browse_help.act.php,v 1.6 2006/01/18 19:15:20 adamfranco Exp $
 */
class browse_helpAction 
	extends Action
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
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return dgettext('polyphony', 'Help Browser');
	}
	
	/**
	 * Execute this action.
	 * 
	 * @param object Harmoni $harmoni
	 * @return mixed
	 * @access public
	 * @since 4/25/05
	 */
	function &execute ( &$harmoni ) {
		$actionRows =& new Container(new YLayout, BLOCK, BACKGROUND_BLOCK);
		
		$heading = dgettext("polyphony", 'Help');
		if ($topic = $this->getTopic())
			$heading .= " - ".$topic;
		$actionRows->add(new Heading($heading, 1));
		
		$actionCols =& $actionRows->add(new Container(new XLayout, BLANK, 1));
		
		$actionCols->add($this->getHelpMenu(), "250px", null, null, TOP);
		
		$actionCols->add($this->getTopicContents($this->getTopic()), null, null, null, TOP);
		
		return $actionRows;
	}
	
	/**
	 * Create the main help Menu
	 * 
	 * @return object Component
	 * @access public
	 * @since 12/8/05
	 */
	function &getHelpMenu () {
		$harmoni =& Harmoni::instance();
		
		$menu =& new Menu(new YLayout, 1);
		
		$menuItem =& new MenuItemLink(
			$this->getMainTopic(), 
			$harmoni->request->quickURL("help", "browse_help"), 
			(RequestContext::value("topic"))?FALSE:TRUE,
			1);
			
		$menu->add($menuItem, "100%", null, LEFT, CENTER);
		
		foreach ($this->getHelpTopics() as $file => $topic) {
			if ($topic != $this->getMainTopic()) {
				$menuItem =& new MenuItemLink(
					"$topic", 
					$harmoni->request->quickURL("help", "browse_help", array("topic" => $topic)), 
					(RequestContext::value("topic") == $topic)?TRUE:FALSE,
					2);
					
				$menu->add($menuItem, "100%", null, LEFT, CENTER);
			}
		}
		
		return $menu; 
	}
	
	/**
	 * Answer an array of the Help topics
	 * 
	 * @return array
	 * @access public
	 * @since 12/8/05
	 */
	function &getHelpTopics () {
		if (!isset($this->_topics)) {
			
			//replace this with config lines.
			$harmoni =& Harmoni::instance();
			if (isset($_SESSION['__help_dirs-'.$harmoni->config->get('programTitle')]) && is_array($_SESSION['__help_dirs-'.$harmoni->config->get('programTitle')]))
				$this->_dirs = $_SESSION['__help_dirs-'.$harmoni->config->get('programTitle')];
			else
				$this->_dirs = array();
			
			$this->_topics = array();
			
			$langMan =& Services::getService('LanguageManager');
			$lang = $langMan->getLanguage();
			
			foreach ($this->_dirs as $helpDir) {
				$dir = $helpDir."/".$lang;
				if (is_dir($dir) && $handle = opendir($dir)) {
					while (false !== ($file = readdir($handle))) {
						$filePath = $dir."/".$file;
						if (preg_match(
							"/<title>(.*)<\/title>/i", 
							file_get_contents($filePath),
							$matches))
						{
							$this->_topics[$filePath] = $matches[1];
						}
					}
					closedir($handle);
				} else if (is_dir($dir = $helpDir."/en_US") && $handle = opendir($dir)) {
					while (false !== ($file = readdir($handle))) {
						$filePath = $dir."/".$file;
						if (preg_match(
							"/<title>(.*)<\/title>/i", 
							file_get_contents($filePath),
							$matches))
						{
							$this->_topics[$filePath] = $matches[1];
						}
					}
					closedir($handle);
				}
			}
			
			asort($this->_topics);
		}
		return $this->_topics;
	}
	
	/**
	 * Register a directory of help topics
	 * This directory should contain sub-directories for each language code.
	 * The default sub-directory is en_US
	 * 
	 * @param string $directory
	 * @return void
	 * @access public
	 * @static
	 * @since 12/9/05
	 */
	function addHelpDirectory ( $directory ) {
		$harmoni =& Harmoni::instance();
		if (!isset($_SESSION['__help_dirs-'.$harmoni->config->get('programTitle')]) || !is_array($_SESSION['__help_dirs-'.$harmoni->config->get('programTitle')]))
			$_SESSION['__help_dirs-'.$harmoni->config->get('programTitle')] = array();
		
		if (is_dir($directory)) {
			if (!in_array($directory, $_SESSION['__help_dirs-'.$harmoni->config->get('programTitle')]))
				$_SESSION['__help_dirs-'.$harmoni->config->get('programTitle')][] = $directory;
		} else
			throwError(new Error("Invalid Help directory, '$directory'", "polyphony.help", true));
	}
	
	/**
	 * Answer the title of the index page
	 * 
	 * @return string
	 * @access public
	 * @since 12/9/05
	 */
	function getMainTopic () {
		if (!isset($this->_mainTopic)) {
			$topics =& $this->getHelpTopics();
		
			foreach ($topics as $file => $topic) {
				if (basename($file) == 'index.html')
					$this->_mainTopic = $topic;
			}
			
			if (!isset($this->_mainTopic))
				$this->_mainTopic = dgettext("polyphony", "Main");
		}
		
		return $this->_mainTopic;
	}
	
	/**
	 * Answer the current topic
	 * 
	 * @return string
	 * @access public
	 * @since 12/9/05
	 */
	function getTopic () {
		if ($topic = RequestContext::value("topic"))
			return $topic;
		else
			return $this->getMainTopic();
	}
	
	/**
	 * Answer the contents of the current topic
	 * 
	 * @param string $topic
	 * @return object Component
	 * @access public
	 * @since 12/9/05
	 */
	function &getTopicContents ($topic) {
		$topicContainer =& new Container(new YLayout, BLANK, 1);
		
		$document =& $this->getTopicXmlDocument($topic);
		
		$bodyElements =& $document->getElementsByPath("/html/body");
		$body =& $bodyElements->item(0);
		
		ob_start();
		for ($i = 0; $i < count($body->childNodes); $i++) {
			$element =& $body->childNodes[$i];
			switch ($element->getTagName()) {

				case 'h1':
					$heading =& new Heading($element->getText(), 1);
				case 'h2':
					if (!isset($heading))
						$heading =& new Heading($element->getText(), 2);
				case 'h3':
					if (!isset($heading))
						$heading =& new Heading($element->getText(), 3);
				case 'h4':
					if (!isset($heading))
						$heading =& new Heading($element->getText(), 4);
					
					$heading->setPreHTML(
						"<a name=\""
						.strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $element->getText()))
						."\"></a>");
				
				
					// Finish off our previous block if it had contents.
					$previousBlockText = ob_get_clean();
					if (strlen(trim($previousBlockText)))
						$topicContainer->add(
							new Block($this->linkify($previousBlockText), 
							STANDARD_BLOCK));
					
					// create our heading element
					$topicContainer->add($heading);
					unset($heading);
					
					// Start a new buffer for the next block contents.
					ob_start();
					break;

				default:
					print $element->toString()."\n";
						
			}
		}
		
		$topicContainer->add(new Block($this->linkify(ob_get_clean()), STANDARD_BLOCK));
		
		return $topicContainer;
	}
	
	/**
	 * Convert links of the form [[title]] or [[title#heading]] to html links
	 * of the form <a href='link'>title: heading</a>
	 * 
	 * @param string $inputText
	 * @return string
	 * @access public
	 * @since 1/6/06
	 */
	function linkify ( $inputText ) {
		// Find all link-holders
		$regex = 
'/
	\[\[
	([^\]\#]+)		# Match the title
	(?:
		\#
		([^\]]+)	# Match the heading
	)?
	\]\]
/ix';
		
		if (preg_match_all($regex, $inputText, $matches)) {
			$harmoni =& Harmoni::instance();
			for ($i = 0; $i < count($matches[0]); $i++) {
				ob_start();
				
				print '<a href="';
				print $harmoni->request->quickURL(
								"help", "browse_help", 
								array("topic" => $matches[1][$i]));
				print '#';
				print strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $matches[2][$i]));
				print '">';
				
				print $matches[1][$i];
				
				if ($matches[2][$i])
					print ': '.$matches[2][$i];
				
				print '</a>';
				
				$inputText = str_replace($matches[0][$i], ob_get_clean(), $inputText);
			}
		}
		
		return $inputText;
	}
	
	/**
	 * Answer a DOMIT XML Document for the given topic
	 * 
	 * @param string $topic
	 * @return object DOMIT_Document
	 * @access public
	 * @since 12/9/05
	 */
	function &getTopicXmlDocument ($topic) {
		$document =& new DOMIT_Document();
		
		$file = array_search($topic, $this->getHelpTopics());
		
		if (!$file || !file_exists($file)) {
			ob_start();
			print 	"<html>\n";
			print 	"	<head>\n";
			print 	"		<title>";
			print dgettext("polyphony", "Topic Not Found");
			print 			"</title>\n";
			print 	"	</head>\n";
			print 	"	<body>\n";
			print 	"		<h1>";
			print dgettext("polyphony", "Topic Not Found");
			print			"</h1>\n";
			print 	"		<p>";
			print dgettext("polyphony", "The topic that you requested was not found.");
			print			"</p>\n";
			print 	"	</body>\n";
			print 	"</html>\n";
			
			$document->parseXML(ob_get_contents());
			ob_end_clean();
		} else {
			$document->loadXML($file);
		}
			
		return $document;
	}
}

?>