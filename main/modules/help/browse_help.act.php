<?php
/**
 * @since 12/8/05
 * @package polyphony.help
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: browse_help.act.php,v 1.11 2007/10/10 22:58:57 adamfranco Exp $
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
 * @package polyphony.help
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: browse_help.act.php,v 1.11 2007/10/10 22:58:57 adamfranco Exp $
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
	function execute () {
		$actionRows = new Container(new YLayout, BLOCK, BACKGROUND_BLOCK);
		
		$heading = dgettext("polyphony", 'Help');
		if ($topic = $this->getTopic())
			$heading .= " - ".$topic;
		$actionRows->add(new Heading($heading, 1));
		
		$actionCols =$actionRows->add(new Container(new XLayout, BLANK, 1));
		
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
	function getHelpMenu () {
		$this->_menu = new Menu(new YLayout, 1);
		
		$toc =$this->getTableOfContents();
		$toc->acceptVisitor($this);
				
		return $this->_menu; 
	}
	
	/**
	 * Visit a table of contents part and add it to our menue
	 * 
	 * @param object $tocPart
	 * @return void
	 * @access public
	 * @since 5/31/06
	 */
	function visitTableOfContentsPart ($tocPart) {
		$harmoni = Harmoni::instance();
		
		if ($tocPart->topic == $harmoni->config->get('programTitle') && is_null($tocPart->heading)) {
			$menuItem = new MenuItemHeading($tocPart->topic, 1);
		}else if ($tocPart->topic == $this->getMainTopic() && is_null($tocPart->heading))
			$menuItem = new MenuItemLink(
				$this->getMainTopic(), 
				$harmoni->request->quickURL("help", "browse_help"), 
				(RequestContext::value("topic"))?FALSE:TRUE,
				$tocPart->level + 2);			
		else {
			
			$url = $harmoni->request->quickURL("help", "browse_help", array("topic" => $tocPart->topic, "heading" => $tocPart->heading));
			
			
			if ($tocPart->heading) {
				$url .= "#".strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', strip_tags($tocPart->heading)));
				$title = $tocPart->heading;
			} else {
				$title = $tocPart->topic;
			}
			
			$menuItem = new MenuItemLink(
				$title, 
				$url, 
				(RequestContext::value("topic") == $tocPart->topic && RequestContext::value("heading") == $tocPart->heading)?TRUE:FALSE,
				$tocPart->level + 2);
		}
		
		$this->_menu->add($menuItem, "100%", null, LEFT, CENTER);
		
		foreach (array_keys($tocPart->children) as $key)
			$tocPart->children[$key]->acceptVisitor($this);
		
		$null = null;
		return $null;
	}
	
	/**
	 * Answer an array of the Help topics
	 * 
	 * @return array
	 * @access public
	 * @since 12/8/05
	 */
	function getHelpTopics () {
		if (!isset($this->_topics)) {
			
			//replace this with config lines.
			$harmoni = Harmoni::instance();
			if (isset($_SESSION['__help_dirs-'.$harmoni->config->get('programTitle')]) && is_array($_SESSION['__help_dirs-'.$harmoni->config->get('programTitle')]))
				$this->_dirs = $_SESSION['__help_dirs-'.$harmoni->config->get('programTitle')];
			else
				$this->_dirs = array();
			
			$this->_topics = array();
			
			// Set up the table of contents
			$this->_tableOfContents = new TableofContentsPart;
			$this->_tableOfContents->topic = $harmoni->config->get('programTitle');
			$this->_tableOfContents->level = -1;
			
			
			// traverse through our directories.
			$langMan = Services::getService('LanguageManager');
			$lang = $langMan->getLanguage();
			
			foreach ($this->_dirs as $key => $dirArray) {
				$dir = $dirArray['directory']."/".$lang;
							
				if (is_dir($dir) && $handle = opendir($dir)) {
					$this->addTopicsFromDirectory($dir, $handle, $dirArray['urlPath']."/".$lang);
					closedir($handle);
				} else if (is_dir($dir = $dirArray['directory']."/en_US") && $handle = opendir($dir)) {
					$this->addTopicsFromDirectory($dir, $handle, $dirArray['urlPath']."/en_US/");
					closedir($handle);
				}
			}

			asort($this->_topics);
		}
		return $this->_topics;
	}
	
	/**
	 * Answer our table of contents object tree
	 * 
	 * @return object
	 * @access public
	 * @since 5/31/06
	 */
	function getTableOfContents () {
		if (!isset($this->_tableOfContents))
			$this->getHelpTopics();
		
		return $this->_tableOfContents;
	}
	
	/**
	 * Add the topics from a given file
	 * 
	 * @param string $dir
	 * @param handle $handle
	 * @return void
	 * @access public
	 * @since 5/31/06
	 */
	function addTopicsFromDirectory ($dir, $handle, $urlPath) {
		while (false !== ($file = readdir($handle))) {
			$filePath = $dir."/".$file;
			$contents = file_get_contents($filePath);
			if (preg_match(
				"/<title>(.*)<\/title>/i", 
				$contents,
				$matches))
			{
				$topic = $matches[1];
				$this->_topics[$filePath] = $topic;
				
				$part =$this->_tableOfContents->addChild($topic, null, 0, $filePath, $urlPath);
			
				if (preg_match_all(
					"/<h([1-6])>(.*)<\/h[1-6]>/i", 
					$contents,
					$matches))
				{
					for ($i = 0; $i < count($matches[1]); $i++) {
						$part->addChild($topic, $matches[2][$i], $matches[1][$i], $filePath, $urlPath);
					}
				}
			}
		}
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
	static function addHelpDirectory ( $directory, $urlPath ) {
		$harmoni = Harmoni::instance();
		if (!isset($_SESSION['__help_dirs-'.$harmoni->config->get('programTitle')]) || !is_array($_SESSION['__help_dirs-'.$harmoni->config->get('programTitle')]))
			$_SESSION['__help_dirs-'.$harmoni->config->get('programTitle')] = array();
		
		if (is_dir($directory)) {
			if (!array_key_exists($directory, $_SESSION['__help_dirs-'.$harmoni->config->get('programTitle')])) {
				$_SESSION['__help_dirs-'.$harmoni->config->get('programTitle')][$directory] = array('directory' => $directory, 'urlPath' => $urlPath);
			}
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
			$topics =$this->getHelpTopics();
		
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
	function getTopicContents ($topic) {
		$topicContainer = new Container(new YLayout, BLANK, 1);
		
		$tocPart = $this->_tableOfContents->getTableOfContentsPart($topic);
		
		$document =$this->getTopicXmlDocument($topic);
		
		$bodyElements =$document->getElementsByPath("/html/body");
		$body =$bodyElements->item(0);
				
		$this->updateSrcTags($document->documentElement, $tocPart->urlPath."/");
		
		// put custom style sheets in the page's head
		$headElements =$document->getElementsByPath("/html/head");
		$head =$headElements->item(0);
		$newHeadText = '';
		for ($i = 0; $i < count($head->childNodes); $i++) {
			$newHeadText .= $head->childNodes[$i]->toString()."\n\t\t";
		}
		
		$harmoni = Harmoni::instance();
		$outputHandler =$harmoni->getOutputHandler();
		$outputHandler->setHead($outputHandler->getHead().$newHeadText);
		
		
		ob_start();
		for ($i = 0; $i < count($body->childNodes); $i++) {
			$element =$body->childNodes[$i];
			switch ($element->getTagName()) {

				case 'h1':
					$heading = new Heading($element->getText(), 1);
				case 'h2':
					if (!isset($heading))
						$heading = new Heading($element->getText(), 2);
				case 'h3':
					if (!isset($heading))
						$heading = new Heading($element->getText(), 3);
				case 'h4':
					if (!isset($heading))
						$heading = new Heading($element->getText(), 4);
					
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
					print $element->toString(false, true)."\n";
						
			}
		}
		
		$topicContainer->add(new Block($this->linkify(ob_get_clean()), STANDARD_BLOCK));
		
		return $topicContainer;
	}
	
	/**
	 * Convert relative links in src tags to contain the full path needed to
	 * use them.
	 * 
	 * @param object 
	 * @return void
	 * @access public
	 * @since 5/31/06
	 */
	function updateSrcTags ($element, $path) {
		if (method_exists($element, 'hasAttribute')
			&& $element->hasAttribute('src') 
			&& !preg_match('/([a-z]+:\/\/.+)|(\/.+)/', $element->getAttribute('src')))
		{
			$element->setAttribute('src',
				$path.$element->getAttribute('src'));
		}
		
		if (method_exists($element, 'hasAttribute')
			&& $element->hasAttribute('href') 
			&& !preg_match('/([a-z]+:\/\/.+)|(\/.+)/', $element->getAttribute('href')))
		{
			$element->setAttribute('href',
				$path.$element->getAttribute('href'));
		}
		
		for ($i = 0; $i < count($element->childNodes); $i++)
			$this->updateSrcTags($element->childNodes[$i], $path);
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
			$harmoni = Harmoni::instance();
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
	function getTopicXmlDocument ($topic) {
		$document = new DOMIT_Document();
		
		$tocPart = $this->_tableOfContents->getTableOfContentsPart($topic);
		
		if (!$tocPart->file || !file_exists($tocPart->file)) {
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
			$document->loadXML($tocPart->file);
		}
			
		return $document;
	}
}


/**
 * <##>
 * 
 * @since 5/31/06
 * @package polyphony.help
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: browse_help.act.php,v 1.11 2007/10/10 22:58:57 adamfranco Exp $
 */
class TableOfContentsPart {
		
	/**
	 * Constructor
	 * 
	 * @param <##>
	 * @return <##>
	 * @access public
	 * @since 5/31/06
	 */
	function TableOfContentsPart () {
		$this->level = 0;
		$this->topic = null;
		$this->heading = null;
		$this->file = null;
		$this->urlPath = null;
		$this->children = array();
	}
	
	
	/**
	 * Add a child part to our children or ourselves, or return false
	 * if not possible (i.e. h1 being put under an h3)
	 * 
	 * @param string $topic
	 * @param string $heading
	 * @param integer $level
	 * @return void
	 * @access public
	 * @since 5/31/06
	 */
	function addChild ($topic, $heading, $level, $file, $urlPath) {
		$false = false;
		
		// if the level is greater or equal to ours, it is a sibling or uncle
		if ($level <= $this->level)
			return $false;
		
		// see if our last child can handle it (to append to that child)
		if (count($this->children))
		{
			$keys = array_keys($this->children);
			
			$part =$this->children[$keys[count($keys) - 1]]->addChild($topic, $heading, $level, $file, $urlPath);
			if ($part)
				return $part;
		}
		
		// if it couldn't be appended to our last child, add it as a new child
		$part = new TableofContentsPart;
		$part->topic = $topic;
		$part->heading = $heading;
		$part->level = $level;
		$part->file = $file;
		$part->urlPath = $urlPath;
		$this->children[$topic.$heading] =$part;
		if ($level == 0)
			ksort($this->children);
		return $part;
	}
	
	/**
	 * Accept a visitor
	 * 
	 * @param object $visitor
	 * @return mixed
	 * @access public
	 * @since 5/31/06
	 */
	function acceptVisitor ($visitor) {
		$result =$visitor->visitTableOfContentsPart($this);
		return $result;
	}
	
	/**
	 * Answer the table of contents part that matches the given topic/heading
	 * 
	 * @param string $topic
	 * @param string $heading
	 * @return object
	 * @access public
	 * @since 5/31/06
	 */
	function getTableOfContentsPart ($topic, $heading = null) {
		if ($topic == $this->topic && $heading == $this->heading)
			return $this;
		
		foreach (array_keys($this->children) as $key) {
			$result =$this->children[$key]->getTableOfContentsPart($topic, $heading);
			if ($result)
				return $result;
		}
		
		$false = false;
		return $false;			
	}
}

?>