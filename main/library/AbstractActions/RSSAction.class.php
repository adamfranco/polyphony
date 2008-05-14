<?php
/**
 * @since 8/7/06
 * @package polyphony.AbstractActions
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: RSSAction.class.php,v 1.7 2008/03/11 17:33:21 achapin Exp $
 */ 
 
require_once(POLYPHONY."/main/library/AbstractActions/ForceAuthConditionalGetAction.class.php");
require_once(HARMONI."/Primitives/Collections-Text/HtmlString.class.php");

/**
 * <##>
 * 
 * @since 8/7/06
 * @package polyphony.AbstractActions
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: RSSAction.class.php,v 1.7 2008/03/11 17:33:21 achapin Exp $
 */
abstract class RSSAction
	extends ForceAuthConditionalGetAction
{
	
	/**
	 * @var array $_items; Items in the feed 
	 * @access private
	 * @since 8/7/06
	 */
	var $_items = array();
	
	/**
	 * @var string $_title;  
	 * @access private
	 * @since 8/7/06
	 */
	var $_title = 'Untitled';
	
	/**
	 * @var string $_link;  
	 * @access private
	 * @since 8/7/06
	 */
	var $_link = 'http://www.example.net/';
	
	/**
	 * @var string $_description;  
	 * @access private
	 * @since 8/7/06
	 */
	var $_description;
	
	/**
	 * @var array $_categories;  
	 * @access private
	 * @since 8/8/06
	 */
	var $_categories = array();
	
	/**
	 * @var string $_generator;  
	 * @access private
	 * @since 8/7/06
	 */
	var $_generator = 'Harmoni Application Framework';
	
	/**
	 * @var array $_skipHours;  
	 * @access private
	 * @since 8/8/06
	 */
	var $_skipHours = array();
	
	/**
	 * @var array $_skipDays;  
	 * @access private
	 * @since 8/8/06
	 */
	var $_skipDays = array();
	
	/**
	 * Answer the unauthorized message
	 * 
	 * @return null
	 * @access public
	 * @since 5/13/08
	 */
	public function getUnauthorizedMessage () {
		header('HTTP/1.0 401 Unauthorized');
		$this->setTitle(_("Unauthorized"));
		$this->setDescription(_("You are not authorized to view this feed."));
		$this->write();
		exit;
	}
	
	/**
	 * Answer the modification date to use with caching. Override this to return
	 * a DateAndTime object if your feed can determine that without doing all of
	 * the work of building.
	 * 
	 * @return object DateAndTime
	 * @access public
	 * @since 5/13/08
	 */
	public function getModifiedDateAndTime () {
		throw new UnimplementedException("This action does not support conditional GET.", -304);
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	public final function outputContent () {
		
		$this->buildFeed();
		$this->write();
		exit;
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	abstract public function buildFeed ();
	
	/**
	 * Add an item to this feed
	 * 
	 * @param object RSSItem $item
	 * @return object RSSItem The item added.
	 * @access public
	 * @since 8/7/06
	 */
	function addItem ($item) {
		$this->_items[] =$item;
		return $item;
	}
	
/*********************************************************
 * Required Channel elements	
 *********************************************************/
 
	/**
	 * Required: Set the title
	 * 
	 * @param string $title
	 * @return void
	 * @access public
	 * @since 8/7/06
	 */
	function setTitle ($title) {
		ArgumentValidator::validate($title, StringValidatorRule::getRule());
		
		$tmp = HtmlString::fromString(str_replace("&nbsp;", "&#160;", $title));
		$this->_title = $tmp->stripTagsAndTrim(20);
	}
	
	/**
	 * Required: Set the link, if it is the GUID, then the GUID will be set to the link.
	 * 
	 * @param string $link
	 * @param optional boolean $isGUID
	 * @return void
	 * @access public
	 * @since 8/7/06
	 */
	function setLink ($link, $isGUID = FALSE) {
		ArgumentValidator::validate($link, StringValidatorRule::getRule());
		ArgumentValidator::validate($isGUID, BooleanValidatorRule::getRule());
		
		$this->_link = $link;
		
		if ($isGUID)
			$this->setGUID($link, true);
	}
	
	/**
	 * Required: Set the description
	 * 
	 * @param string $description
	 * @return void
	 * @access public
	 * @since 8/7/06
	 */
	function setDescription ($description) {
		ArgumentValidator::validate($description, StringValidatorRule::getRule());
		
		$this->_description = HtmlString::fromString(
								str_replace("&nbsp;", "&#160;", $description));
		$this->_description->clean();
	}
	
/*********************************************************
 * Optional Channel elements	
 *********************************************************/
	
	/**
	 * Optional: Set the publish date
	 * 
	 * @param object DateAndTime $pubDate
	 * @return void
	 * @access public
	 * @since 8/7/06
	 */
	function setPubDate ( $pubDate) {
		ArgumentValidator::validate($pubDate, HasMethodsValidatorRule::getRule("asDateAndTime"));
		
		$this->_pubDate =$pubDate->asDateAndTime();
	}
	
	/**
	 * Optional: Set the lastBuildDate
	 * 
	 * @param object DateAndTime $lastBuildDate
	 * @return void
	 * @access public
	 * @since 8/7/06
	 */
	function setLastBuildDate ( $lastBuildDate) {
		ArgumentValidator::validate($pubDate, HasMethodsValidatorRule::getRule("asDateAndTime"));
		
		$this->_lastBuildDate =$lastBuildDate->asDateAndTime();
	}
	
	/**
	 * Optional: Set the managingEditor name and email
	 * 
	 * @param string $name
	 * @param optional string $email
	 * @return void
	 * @access public
	 * @since 8/7/06
	 */
	function setManagingEditor ($name, $email='nobody@example.net') {
		ArgumentValidator::validate($name, StringValidatorRule::getRule());
		ArgumentValidator::validate($email, StringValidatorRule::getRule());
		
		$this->_managingEditorName = $name;
		$this->_managingEditorEmail = $email;
	}
	
	/**
	 * Optional: Set the copyright notice
	 * 
	 * @param string $copyright
	 * @return void
	 * @access public
	 * @since 8/7/06
	 */
	function setCopyright ($copyright) {
		ArgumentValidator::validate($copyright, StringValidatorRule::getRule());
		
		$this->_copyright = $copyright;
	}
	
	/**
	 * Optional: Set the category
	 * 
	 * @param string category
	 * @return void
	 * @access public
	 * @since 8/7/06
	 */
	function addCategory ($category, $domain = null) {
		ArgumentValidator::validate($category, StringValidatorRule::getRule());
		ArgumentValidator::validate($domain, OptionalRule::getRule(StringValidatorRule::getRule()));
		
		if ($domain) {
			if (!isset($this->_categories[$domain]))
				$this->_categories[$domain] = array();
			$this->_categories[$domain][] = $category;
		} else {
			if (!isset($this->_categories['_no_domain_']))
				$this->_categories['_no_domain_'] = array();
			$this->_categories['_no_domain_'][] = $category;
		}
	}
	
	/**
	 * Optional: Set the time to live, It's a number of minutes that indicates how long a 
	 * channel can be cached before refreshing from the source.
	 * 
	 * @param integer $minutes
	 * @return void
	 * @access public
	 * @since 8/7/06
	 */
	function setTTL ($minutes) {
		ArgumentValidator::validate($minutes, IntegerValidatorRule::getRule());
		ArgumentValidator::validate($minutes, IntegerRangeValidatorRule::getRule(1, pow(2, 30)));
		
		$this->_ttl = $minutes;
	}
	
	/**
	 * Optional: Specifies a GIF, JPEG or PNG image that can be displayed with the channel.
	 * 
	 * @param string $imageURL
	 * @param string $imageTitle
	 * @return void
	 * @access public
	 * @since 8/7/06
	 */
	function setImage ($imageURL, $imageHeight = null, $imageWidth = null,
		$imageTitle = null, $imageLink = null, $imageDescription = null) 
	{
		ArgumentValidator::validate($imageURL, StringValidatorRule::getRule());
		ArgumentValidator::validate($imageHeight, OptionalRule::getRule(IntegerValidatorRule::getRule()));
		ArgumentValidator::validate($imageHeight, 
			OptionalRule::getRule(IntegerRangeValidatorRule::getRule(1, 400)));
		ArgumentValidator::validate($imageWidth, OptionalRule::getRule(IntegerValidatorRule::getRule()));
		ArgumentValidator::validate($imageWidth, 
			OptionalRule::getRule(IntegerRangeValidatorRule::getRule(1, 144)));
		ArgumentValidator::validate($imageLink, OptionalRule::getRule(StringValidatorRule::getRule()));
		ArgumentValidator::validate($imageDescription, OptionalRule::getRule(StringValidatorRule::getRule()));
		
		$this->_imageURL = $imageURL;
		if ($imageTitle)
			$this->_imageTitle = $imageTitle;
		if ($imageLink)
			$this->_imageLink = $imageLink;
		if ($imageDescription)
			$this->_imageDescription = $imageDescription;
		if ($imageHeight)
			$this->_imageHeight = $imageHeight;
		if ($imageWidth)
			$this->_imageWidth = $imageWidth;
	}
	
	
/*********************************************************
 * Output
 *********************************************************/
	
	/**
	 * Send the Content headers and write the feed, then exit.
	 * 
	 * @return void
	 * @access public
	 * @since 8/7/06
	 */
	function write () {
		header("Content-Type: text/xml; charset=utf-8");
		print<<<END
<?xml version="1.0" encoding="utf-8" ?>
<rss version="2.0">
	<channel>
END;
		print "\n\t\t<title>".htmlentities($this->_title)."</title>";
		
		print "\n\t\t<link>".$this->_link."</link>";
		
		if (isset($this->_description))
			print "\n\t\t<description>".htmlentities($this->_description->asString())."</description>";
		else
			print "\n\t\t<description></description>";
		
		if (isset($this->_language))
			print "\n\t\t<language>".$this->_language."</language>";
		
		if (isset($this->_copyright))
			print "\n\t\t<copyright>".$this->_copyright."</copyright>";
		
		if (isset($this->_managingEditor))
			print "\n\t\t<managingEditor>".$this->_managingEditor."</managingEditor>";
		
		if (isset($this->_webMaster))
			print "\n\t\t<webMaster>".$this->_webMaster."</webMaster>";
			
		if (isset($this->_pubDate) && is_object($this->_pubDate)) {
			print "\n\t\t<pubDate>";
			RSSAction::printRSSTimestamp($this->_pubDate);
			print "</pubDate>";
		}
		
		if (!isset($this->_lastBuildDate) || !is_object($this->_lastBuildDate))
			$this->_lastBuildDate =$this->getLatestItemDate();
			
		if (isset($this->_lastBuildDate) && is_object($this->_lastBuildDate)) {
			print "\n\t\t<lastBuildDate>";
			RSSAction::printRSSTimestamp($this->_lastBuildDate);
			print "</lastBuildDate>";
		}
		
		if (count($this->_categories)) {
			foreach ($this->_categories as $domain => $categories) {
				foreach ($categories as $category) {
					if ($domain == '_no_domain_')
						print "\n\t\t<category>";
					else
						print "\n\t\t<category domain='".$domain."'>";
					
					print $category."</category>";
				}
			}
		}
		
		print "\n\t\t<generator>".$this->_generator."</generator>";
		
		print "\n\t\t<docs>http://blogs.law.harvard.edu/tech/rss</docs>";
		
		if (isset($this->_ttl))
			print "\n\t\t<ttl>".$this->_ttl."</ttl>";
		
		
		if (isset($this->_imageURL)) {
			print "\n\t\t<image>";
			print "\n\t\t\t<url>".$this->_imageURL."</url>";
		
			if (isset($this->_imageTitle))
				print "\n\t\t\t<title>".htmlentities($this->_imageTitle)."</title>";
			else
				print "\n\t\t\t<title>".htmlentities($this->_title)."</title>";
		
			if (isset($this->_imageLink))
				print "\n\t\t\t<link>".$this->_imageLink."</link>";
			else
				print "\n\t\t\t<link>".$this->_link."</link>";
		
			if (isset($this->_imageDescription)) {
				print "\n\t\t\t<description>";
				print htmlentities($this->_imageDescription);
				print "</description>";
			} else if (isset($this->_description) && is_object($this->_description)) {
				print "\n\t\t\t<description>";
				print htmlentities($this->_description->stripTagsAndTrim(20));
				print "</description>";
			}
			
			if (isset($this->_imageWidth))
				print "\n\t\t\t<width>".$this->_imageWidth."</width>";
			
			if (isset($this->_imageHeight))
				print "\n\t\t\t<height>".$this->_imageWidth."</height>";
			
			print "\n\t\t</image>";
		}
			
		if (isset($this->_copyright))
			print "\n\t\t<copyright>".htmlentities($this->_copyright)."</copyright>";
			
		if (count($this->_skipHours)) {
			print "\n\t\t<skipHours>";
			foreach($this->_skipHours as $hour)
				print "\n\t\t\t<hour>".$hour."</hour>";
			print "\n\t\t</skipHours>";
		}
		
		if (count($this->_skipDays)) {
			print "\n\t\t<skipDays>";
			foreach($this->_skipDays as $day)
				print "\n\t\t\t<day>".$hour."</day>";
			print "\n\t\t</skipDays>";
		}
		
		foreach ($this->_items as $key => $copy) {
			$this->_items[$key]->write();
		}
		
		print<<<END

	</channel>
</rss>

END;
	}
	
	/**
	 * Get the latested Item-date
	 * 
	 * @return mixed object or null
	 * @access public
	 * @since 8/8/06
	 */
	function getLatestItemDate () {
		$latestDate = null;
		
		foreach (array_keys($this->_items) as $key) {
			$itemDate =$this->_items[$key]->getPubDate();
			if (!is_object($itemDate))
				continue;
			
			if (is_null($latestDate)) {
				$latestDate =$itemDate;
				continue;
			}
			
			if ($itemDate->isGreaterThan($latestDate))
				$latestDate =$itemDate;
		}
		
		return $latestDate;
	}
	
	/**
	 * Print a timestamp in RSS form
	 * 
	 * @param object DateAndTime $timestamp
	 * @return void
	 * @access public
	 * @since 8/7/06
	 * @static
	 */
	static function printRSSTimestamp ($timestamp) {
		print $timestamp->dayOfWeekAbbreviation().", ";
		print str_pad($timestamp->dayOfMonth(), 2, '0', STR_PAD_LEFT)." ";
		print $timestamp->monthAbbreviation()." ";
		print $timestamp->year()." ";	
		print $timestamp->hmsString()." ";
		$tzOffset =$timestamp->offset();
		print (($tzOffset->isPositive())?"+":"-");
		print str_pad(abs($tzOffset->hours()), 2, '0', STR_PAD_LEFT)."";
		print str_pad(abs($tzOffset->minutes()), 2, '0', STR_PAD_LEFT);
	}
	
}

/**
 * This class represents an item in an RSS feed
 * 
 * @since 8/7/06
 * @package polyphony.AbstractActions
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: RSSAction.class.php,v 1.7 2008/03/11 17:33:21 achapin Exp $
 */
class RSSItem {
	
	/**
	 * @var array $_categories;  
	 * @access private
	 * @since 8/8/06
	 */
	var $_categories = array();
	
	/**
	 * @var array $_enclosures;  
	 * @access private
	 * @since 8/9/06
	 */
	var $_enclosures;
	
	
/*********************************************************
 * Required Item Elements
 *
 * Title OR Description are required, but both are not required
 *********************************************************/
 
 	/**
	 * Required: Set the title
	 * 
	 * @param string $title
	 * @return void
	 * @access public
	 * @since 8/7/06
	 */
	function setTitle ($title) {
		ArgumentValidator::validate($title, StringValidatorRule::getRule());
		
		$tmp = HtmlString::fromString(str_replace("&nbsp;", "&#160;", $title));
		$this->_title = $tmp->stripTagsAndTrim(20);
	}
 	
 	/**
	 * Required: Set the description
	 * 
	 * @param string $description
	 * @return void
	 * @access public
	 * @since 8/7/06
	 */
	function setDescription ($description) {
		ArgumentValidator::validate($description, StringValidatorRule::getRule());
		
		$this->_description = HtmlString::fromString(
								str_replace("&nbsp;", "&#160;", $description));
		$this->_description->clean();
	}
	
	/**
	 * Answer the description
	 * 
	 * @return string
	 * @access public
	 * @since 8/9/06
	 */
	function getDescription () {
		return $this->_description->asString();
	}
	
/*********************************************************
 * Optional Item Elements
 *********************************************************/
	
	/**
	 * Optional: Set the link, if it is the GUID, then the GUID will be set to the link.
	 * 
	 * @param string $link
	 * @param optional boolean $isGUID
	 * @return void
	 * @access public
	 * @since 8/7/06
	 */
	function setLink ($link, $isGUID = true) {
		ArgumentValidator::validate($link, StringValidatorRule::getRule());
		ArgumentValidator::validate($isGUID, BooleanValidatorRule::getRule());
		
		$this->_link = $link;
		
		if ($isGUID)
			$this->setGUID($link, true);
	}
	
	/**
	 * Optional: Set the publish date
	 * 
	 * @param object DateAndTime $pubDate
	 * @return void
	 * @access public
	 * @since 8/7/06
	 */
	function setPubDate ( $pubDate) {
		ArgumentValidator::validate($pubDate, HasMethodsValidatorRule::getRule("asDateAndTime"));
		
		$this->_pubDate =$pubDate->asDateAndTime();
	}
	
	/**
	 * Answer the PubDate
	 * 
	 * @return object or null
	 * @access public
	 * @since 8/8/06
	 */
	function getPubDate () {
		if (isset($this->_pubDate) && is_object($this->_pubDate))
			return $this->_pubDate;
		else {
			$null = null;
			return $null;
		}
	}
	
	/**
	 * Optional: Set the author name and email
	 * 
	 * @param string $name
	 * @param optional string $email
	 * @return void
	 * @access public
	 * @since 8/7/06
	 */
	function setAuthor ($name, $email='nobody@example.net') {
		ArgumentValidator::validate($name, StringValidatorRule::getRule());
		ArgumentValidator::validate($email, StringValidatorRule::getRule());
		
		$this->_authorName = $name;
		$this->_authorEmail = $email;
	}
	
	/**
	 * Optional: Set the GUID and isPermaLink
	 * 
	 * @param string $guid
	 * @param optional boolean $isPermaLink
	 * @return void
	 * @access public
	 * @since 8/7/06
	 */
	function setGUID ($guid, $isPermaLink=true) {
		ArgumentValidator::validate($guid, StringValidatorRule::getRule());
		ArgumentValidator::validate($isPermaLink, BooleanValidatorRule::getRule());
		
		$this->_guid = $guid;
		$this->_isPermaLink = $isPermaLink;
	}
	
	/**
	 * Optional: Add a category category
	 * 
	 * @param string category
	 * @return void
	 * @access public
	 * @since 8/7/06
	 */
	function addCategory ($category, $domain = '_no_domain_') {
		ArgumentValidator::validate($category, StringValidatorRule::getRule());
		ArgumentValidator::validate($domain, StringValidatorRule::getRule());
		
		$this->_categories[] = array('category' => $category, 'domain' => $domain);
	}
	
	/**
	 * Optional: Add a category to the beginning of the list
	 * 
	 * @param string category
	 * @return void
	 * @access public
	 * @since 8/9/06
	 */
	function prependCategory ($category, $domain = '_no_domain_') {
		ArgumentValidator::validate($category, StringValidatorRule::getRule());
		ArgumentValidator::validate($domain, StringValidatorRule::getRule());
		
		array_unshift($this->_categories, array('category' => $category, 'domain' => $domain));
	}
	
	/**
	 * Optional: Set comments URL
	 * 
	 * @param string $commentsLink
	 * @return void
	 * @access public
	 * @since 8/7/06
	 */
	function setCommentsLink ($commentsLink) {
		ArgumentValidator::validate($commentsLink, StringValidatorRule::getRule());
		
		$this->_commentsLink = $commentsLink;
	}
	
	/**
	 * Optional: Set source
	 * 
	 * @param string $sourceTitle
	 * @param string $sourceLink
	 * @return void
	 * @access public
	 * @since 8/7/06
	 */
	function setSource ($sourceTitle, $sourceLink) {
		ArgumentValidator::validate($sourceTitle, StringValidatorRule::getRule());
		ArgumentValidator::validate($sourceLink, StringValidatorRule::getRule());
		
		$this->_sourceTitle = $sourceTitle;
		$this->_sourceLink = $sourceLink;
	}
	
	/**
	 * Optional: Add an enclosure. As of this writing the RSS 2.0 spec does not
	 * allow multiple enclosures. Some aggregators however, support them anyway
	 * and the one's I've (Adam) tested in simply ignore the extra enclosures.
	 * Add multiple enclosures at your own risk.
	 * 
	 * @param string $url
	 * @param integer $length
	 * @param string $mimeType
	 * @return void
	 * @access public
	 * @since 8/7/06
	 */
	function addEnclosure ($url, $length, $mimeType) {
		ArgumentValidator::validate($url, StringValidatorRule::getRule());
		ArgumentValidator::validate($length, IntegerValidatorRule::getRule());
		ArgumentValidator::validate($mimeType, RegexValidatorRule::getRule(
			'^(text|image|audio|video|application)/.+$'));
		
		$this->_enclosures[] = array(
			'url' => $url,
			'length' => $length,
			'mimeType' => $mimeType);
	}
	
/*********************************************************
 * Output methods
 *********************************************************/
	
	/**
	 * print out the item XML
	 * 
	 * @return void
	 * @access public
	 * @since 8/7/06
	 */
	function write () {
		print "\n\t\t<item>";
		
		if (isset($this->_title))
			print "\n\t\t<title>".htmlentities($this->_title)."</title>";
		else if (!isset($this->_description))
			print "\n\t\t<title>"._("Untitled")."</title>";
		
		if (isset($this->_link))
			print "\n\t\t<link>".$this->_link."</link>";
		
		if (isset($this->_description) && is_object($this->_description)) {
			print "\n\t\t<description>";
			print htmlentities($this->_description->asString());
			print "</description>";
		}
		
		if (isset($this->_authorName) || isset($this->_authorEmail)) {
			print "\n\t\t<author>";
			print $this->_authorName;
			if ($this->_authorEmail && $this->_authorName)
				print ' - '.$this->_authorEmail;
			else if ($this->_authorEmail)
				print $this->_authorEmail;
			print "</author>";
		}
		
		if (count($this->_categories)) {
			foreach ($this->_categories as $categoryArray) {
				$category = $categoryArray['category'];
				$domain = $categoryArray['domain'];
				if ($domain == '_no_domain_')
					print "\n\t\t<category>";
				else
					print "\n\t\t<category domain='".$domain."'>";
				
				print $category."</category>";
			}
		}
		
		if (isset($this->_commentsLink))
			print "\n\t\t<comments>".$this->_commentsLink."</comments>";
		
		if (count($this->_enclosures)) {
			foreach ($this->_enclosures as $enclosure) {
				print "\n\t\t<enclosure ";
				print " url='".$enclosure['url']."'";
				print " length='".$enclosure['length']."'";
				print " type='".$enclosure['mimeType']."'";			
				print " />";
			}
		}
			
		if (isset($this->_guid)) {
			print "\n\t\t<guid isPermaLink='".(($this->_isPermaLink)?"true":"false")."'>";
			print $this->_guid."</guid>";
		}
		
		if (isset($this->_pubDate) && is_object($this->_pubDate)) {
			print "\n\t\t<pubDate>";
			RSSAction::printRSSTimestamp($this->_pubDate);
			print "</pubDate>";
		}
		
		if (isset($this->_sourceLink)) {
			print "\n\t\t<source url='".$this->_sourceLink."'>";
			if (isset($this->_sourceTitle))
				print htmlentities($this->_sourceTitle);
			else
				print _("Untitled Source");
			print "</source>";
		}
		print "\n\t\t</item>";
	}
}


?>