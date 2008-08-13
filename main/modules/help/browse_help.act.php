<?php
/**
 * @since 12/8/05
 * @package polyphony.help
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: browse_help.act.php,v 1.15 2008/03/06 19:03:01 adamfranco Exp $
 */

require_once(HARMONI."utilities/Harmoni_DOMDocument.class.php"); 
require_once(POLYPHONY."/main/library/AbstractActions/Action.class.php");

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
 * @version $Id: browse_help.act.php,v 1.15 2008/03/06 19:03:01 adamfranco Exp $
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
		
		$actionCols->add($this->getHelpMenu(), "150px", null, null, TOP);
		
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
				return null;
				$url .= "#".strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', strip_tags($tocPart->heading)));
				$title = $tocPart->heading;
			} else {
				$title = $tocPart->topic;
			}
			
			$menuItem = new MenuItemLink(
				$title, 
				$url, 
				(strtolower(RequestContext::value("topic")) == strtolower($tocPart->topic) 
					&& strtolower(RequestContext::value("heading")) == strtolower($tocPart->heading))?TRUE:FALSE,
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
		
		try {
			$document =$this->getTopicXmlDocument($topic);
		} catch (DOMException $e) {
			$topicContainer->add(new Block(_("Could not load help topic:")."<br/><br/>".$e->getMessage(), STANDARD_BLOCK));
			return $topicContainer;
		}
		
		$xpath = new DOMXPath($document);
		$bodyElements = $xpath->query("/html/body");
		if (!$bodyElements->length) {
			$topicContainer->add(new Block(_("This topic has no information yet."), STANDARD_BLOCK));
			return $topicContainer;
		}
		$body = $bodyElements->item(0);
		
		if ($tocPart && !is_null($document->documentElement)) 
			$this->updateSrcTags($document->documentElement, $tocPart->urlPath."/");
		
		// put custom style sheets in the page's head
		$headElements = $xpath->query("/html/head");
		$head =$headElements->item(0);
		$newHeadText = '';
		foreach ($head->childNodes as $child) {
			$newHeadText .= $document->saveXML($child)."\n\t\t";
		}
		
		$harmoni = Harmoni::instance();
		$outputHandler =$harmoni->getOutputHandler();
		$outputHandler->setHead($outputHandler->getHead().$newHeadText);
		
		/*********************************************************
		 * Page TOC
		 *********************************************************/
		$currentLevel = 1;
		$toc = new TOC_Printer;
		foreach ($body->childNodes as $element) {
			unset($level);
			switch ($element->nodeName) {
				case 'h1':
					$level = 1;
				case 'h2':
					if (!isset($level))
						$level = 2;
				case 'h3':
					if (!isset($level))
						$level = 3;
				case 'h4':
					if (!isset($level))
						$level = 4;
					
					$heading = $element->textContent;
					$anchor = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $element->textContent));
						
					if ($level > $currentLevel) {
						while ($level > $currentLevel) {
							$toc = $toc->addLevel();
							$currentLevel++;
						}
					} else if ($level < $currentLevel) {
						while ($level < $currentLevel) {
							$toc = $toc->removeLevel();
							$currentLevel--;
						}
					}
					
					$toc->addHtml("<a href='#$anchor'>$heading</a>");
					
			}
		}
		
		$toc = $toc->getRoot();
		if ($toc->numChildren() > 1 || $toc->hasMoreLevels())
			$topicContainer->add(new Block($toc->getHtml(), STANDARD_BLOCK));
		
		
		/*********************************************************
		 * Content of the page
		 *********************************************************/
		
		ob_start();
		foreach ($body->childNodes as $element) {
			switch ($element->nodeName) {

				case 'h1':
					$heading = new Heading($element->textContent, 1);
				case 'h2':
					if (!isset($heading))
						$heading = new Heading($element->textContent, 2);
				case 'h3':
					if (!isset($heading))
						$heading = new Heading($element->textContent, 3);
				case 'h4':
					if (!isset($heading))
						$heading = new Heading($element->textContent, 4);
					
					$heading->setPreHTML(
						"<a name=\""
						.strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $element->textContent))
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
					print $document->saveXML($element)."\n";
						
			}
		}
		
		$topicContainer->add(new Block($this->linkify(ob_get_clean()), STANDARD_BLOCK));
		
		return $topicContainer;
	}
	
	/**
	 * Convert relative links in src tags to contain the full path needed to
	 * use them.
	 * 
	 * @param object DOMNode $element
	 * @param string $path
	 * @return void
	 * @access public
	 * @since 5/31/06
	 */
	function updateSrcTags (DOMNode $element, $path) {
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
		
		if ($element->nodeType == XML_ELEMENT_NODE) {
			foreach ($element->childNodes as $child)
				$this->updateSrcTags($child, $path);
		}
	}
	
	/**
	 * Convert links of the form [[title]] or [[title#heading]] to html links
	 * of the form <a href='link'>title: heading</a>
	 * 
	 * @param string $text
	 * @return string
	 * @access public
	 * @since 1/6/06
	 */
	function linkify ( $text ) {
		// loop through the text and look for wiki markup.
		self::mb_preg_match_all('/(<nowiki>)?(\[\[[^\]]+\]\])(<\/nowiki>)?/', $text, $matches,  PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
		
		$offsetDiff = 0;
		// for each wiki link replace it with the HTML link text
		foreach ($matches as $match) {
			$offset = $match[0][1] + $offsetDiff;
			$wikiText = $match[0][0];
			
			// Ignore markup surrounded by nowiki tags
			if (!strlen($match[1][0]) && (!isset($match[3]) || !strlen($match[3][0]))) {
				$output = $this->makeHtmlLink($wikiText);
				
				$offsetDiff = $offsetDiff + mb_strlen($output) - mb_strlen($wikiText);
				$text = substr_replace($text, $output, $offset, mb_strlen($wikiText));
			}
			// Remove the nowiki tag from the markup.
			else {
				$output = $match[2][0];
				
				$offsetDiff = $offsetDiff + mb_strlen($output) - mb_strlen($wikiText);
				$text = substr_replace($text, $output, $offset, mb_strlen($wikiText));
			}
		}
		
		return $text;
	}
	
	/**
	 * Make an HTML version of a link from wikitext
	 * 
	 * @param string $wikitext
	 * @return string
	 * @access protected
	 * @since 8/13/08
	 */
	protected function makeHtmlLink ($wikiText) {
		
		// Find all link-holders
		$regex = 
'/
	\[\[
	([^\]\#|]+)			# Match the title
	(?:	\#([^\]|]+)	)?			# Match the heading
	(?: \s*\|\s* ([^\]]+) )?	# The optional link-text to display instead of the title
	\]\]
/ix';
		
		if (preg_match($regex, $wikiText, $matches)) {
			$harmoni = Harmoni::instance();
			ob_start();
			
			print '<a href="';
			print $harmoni->request->quickURL(
							"help", "browse_help", 
							array("topic" => $matches[1]));
			
			if (isset($matches[2])) {
				print '#';
				print strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $matches[2]));
			}
			print '">';
			
			if (isset($matches[3]) && $matches[3]) {
				print $matches[3];
			} else {
				print $matches[1];
			
				if (isset($matches[2]) && $matches[2])
					print ': '.$matches[2];
			}
			
			print '</a>';
			
			return ob_get_clean();
		}
		
		return $wikiText;
	}
	
	/**
	 * Answer a DOM XML Document for the given topic
	 * 
	 * @param string $topic
	 * @return object DOMDocument
	 * @access public
	 * @since 12/9/05
	 */
	function getTopicXmlDocument ($topic) {
		$document = new Harmoni_DOMDocument();
		
		$tocPart = $this->_tableOfContents->getTableOfContentsPart($topic);
		
		if (!$tocPart || !$tocPart->file || !file_exists($tocPart->file)) {
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
			
			$document->loadXML(ob_get_clean());
		} else {
			$document->load($tocPart->file);
		}
			
		return $document;
	}
	
	/**
	 * This is a function to convert byte offsets into (UTF-8) character offsets 
	 * (this is reagardless of whether you use /u modifier:
	 *
	 * Posted by chuckie to php.net on 2006-12-06.
	 * 
	 * @param string $ps_pattern
	 * @param string $ps_subject
	 * @param array $pa_matches
	 * @param int $pn_flags
	 * @param int $pn_offset
	 * @param string $ps_encoding
	 * @return mixed int or false
	 * @access public
	 * @static
	 * @since 7/18/08
	 */
	public static function mb_preg_match_all($ps_pattern, $ps_subject, &$pa_matches, $pn_flags = PREG_PATTERN_ORDER, $pn_offset = 0, $ps_encoding = NULL) {
		// WARNING! - All this function does is to correct offsets, nothing else:
		//
		if (is_null($ps_encoding))
			$ps_encoding = mb_internal_encoding();
		
		$pn_offset = strlen(mb_substr($ps_subject, 0, $pn_offset, $ps_encoding));
		$ret = preg_match_all($ps_pattern, $ps_subject, $pa_matches, $pn_flags, $pn_offset);
		if ($ret && ($pn_flags & PREG_OFFSET_CAPTURE))
			foreach($pa_matches as &$ha_match)
				foreach($ha_match as &$ha_match) {
					if (is_array($ha_match) && !(strlen($ha_match[0]) == 0 && $ha_match[1] == -1)) {
						$ha_match[1] = mb_strlen(substr($ps_subject, 0, $ha_match[1]), $ps_encoding);
					}
				}
		
		// (code is independent of PREG_PATTER_ORDER / PREG_SET_ORDER)
		
		return $ret;
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
 * @version $Id: browse_help.act.php,v 1.15 2008/03/06 19:03:01 adamfranco Exp $
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
		if (strtolower($topic) == strtolower($this->topic) 
				&& strtolower($heading) == strtolower($this->heading))
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


/**
 * This class aids the building of an HTML table of contents (TOC)
 * 
 * @since 8/12/08
 * @package polyphony.help
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class TOC_Printer {
		
	/**
	 * Create a new Printer with the parent given.
	 * 
	 * @param optional object TOC_Printer $parent
	 * @return void
	 * @access public
	 * @since 8/12/08
	 */
	public function __construct (TOC_Printer $parent = null) {
		$this->parent = $parent;
		$this->children = array();
	}
	
	/**
	 * Add html text.
	 * 
	 * @param string $text
	 * @return void
	 * @access public
	 * @since 8/12/08
	 */
	public function addHtml ($text) {
		ArgumentValidator::validate($text, StringValidatorRule::getRule());
		$this->children[] = $text;
	}
	
	/**
	 * Add a new level to the table of contents and return it.
	 * 
	 * @return object TOC_Printer
	 * @access public
	 * @since 8/12/08
	 */
	public function addLevel () {
		$toc = new TOC_Printer($this);
		$this->children[] = $toc;
		return $toc;
	}
	
	/**
	 * Return our parent
	 * 
	 * @return object TOCPrinter
	 * @access public
	 * @since 8/12/08
	 */
	public function removeLevel () {
		return $this->getParent();
	}
	
	/**
	 * Answer the parent of this TOC if exists
	 * 
	 * @return object TOC_Printer or null;
	 * @access public
	 * @since 8/12/08
	 */
	public function getParent () {
		return $this->parent;
	}
	
	/**
	 * Answer the level of depth of this toc 0 is the top level
	 * 
	 * @return int
	 * @access public
	 * @since 8/12/08
	 */
	public function getLevel () {
		if (is_null($this->parent))
			return 0;
		else
			return $this->parent->getLevel() + 1;
	}
	
	/**
	 * Answer the root of the TOC tree.
	 * 
	 * @return object TOC_Printer
	 * @access public
	 * @since 8/12/08
	 */
	public function getRoot () {
		if (is_null($this->parent))
			return $this;
		else
			return $this->parent->getRoot();
	}
	
	/**
	 * Print out the TOC
	 * 
	 * @return string 
	 * @access public
	 * @since 8/12/08
	 */
	public function getHtml () {
		ob_start();
		print "\n".str_repeat("\t", $this->getLevel())."<ol>";
		
		$opened = false;
		foreach ($this->children as $i => $child) {
			if (!$opened) {
				print "\n".str_repeat("\t", $this->getLevel())."\t<li>";
				$opened = true;
			}
				
			if (is_string($child)) {
				print $child;
			} else {
				print $child->getHtml();
			}
			
			if (isset($this->children[$i + 1]) && !is_object($this->children[$i + 1])) {
				print "\n".str_repeat("\t", $this->getLevel())."\t</li>";
				$opened = false;
			}
		}
		
		if ($opened) 
			print "\n".str_repeat("\t", $this->getLevel())."\t</li>";
		
		print "\n".str_repeat("\t", $this->getLevel())."</ol>";
		return ob_get_clean();
	}
	
	/**
	 * Answer true if the TOC_Printer has child printers
	 * 
	 * @return boolean
	 * @access public
	 * @since 8/13/08
	 */
	public function hasMoreLevels () {
		foreach ($this->children as $child) {
			if (is_object($child))
				return true;
		}
		return false;
	}
	
	/**
	 * Answer the number of children
	 * 
	 * @return int
	 * @access public
	 * @since 8/13/08
	 */
	public function numChildren () {
		return count($this->children);
	}
}

