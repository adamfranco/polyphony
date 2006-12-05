<?php
/**
 * @since 8/7/06
 * @package polyphony.modules.logging
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: browse_rss.act.php,v 1.3 2006/12/05 20:10:12 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/RSSAction.class.php");

/**
 * <##>
 * 
 * @since 8/7/06
 * @package polyphony.modules.logging
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: browse_rss.act.php,v 1.3 2006/12/05 20:10:12 adamfranco Exp $
 */
class browse_rssAction 
	extends RSSAction
{
		
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 8/8/06
	 */
	function isExecutionAuthorized () {
		// Check for authorization
 		$authZManager =& Services::getService("AuthZ");
 		$idManager =& Services::getService("IdManager");
 		if ($authZManager->isUserAuthorized(
 					$idManager->getId("edu.middlebury.authorization.view"),
 					$idManager->getId("edu.middlebury.authorization.root")))
 		{
			return TRUE;
 		} else {
 			
 			return FALSE;
		}
	}
	
	/**
	 * Answer the HTTP Authentication 'Relm' to present to the user for authentication.
	 * 
	 * @return mixed string or null
	 * @access public
	 * @since 8/7/06
	 */
	function getRelm () {
		return 'Concerto'; // Override for custom relm.
	}
	
	/**
	 * Build the rss feed
	 * 
	 * @return void
	 * @access public
	 * @since 8/8/06
	 */
	function buildFeed () {		
		$defaultTextDomain = textdomain("polyphony");
		
		$harmoni =& Harmoni::instance();		
		$harmoni->request->startNamespace("polyphony-logs");
		$harmoni->request->passthrough('log', 'priority',
			'startYear', 'startMonth', 'startDay', 'startHour',
			'endYear', 'endMonth', 'endDay', 'endHour',
			'agent_id', 'node_id', 'category');
			
		$agentManager =& Services::getService("Agent");
		$idManager = Services::getService("Id");
		$hierarchyManager = Services::getService("Hierarchy");
		$loggingManager =& Services::getService("Logging");
		
		$formatType =& new Type("logging", "edu.middlebury", "AgentsAndNodes",
						"A format in which the acting Agent[s] and the target nodes affected are specified.");
		
		if (RequestContext::value("log"))
			$currentLogName = RequestContext::value("log");
		else {
			$logNames =& $loggingManager->getLogNamesForReading();
			$currentLogName = $logNames->next();
		}
		
		
		// --- The Current log ---
		if (isset($currentLogName) && $currentLogName) {
			$log =& $loggingManager->getLogForReading($currentLogName);
			
			// Priority Type
			if (RequestContext::value("priority")) {
				$currentPriorityType =& Type::fromString(
											RequestContext::value("priority"));
			} else {
				$priorityTypes =& $loggingManager->getPriorityTypes();
				$currentPriorityType =& $priorityTypes->next();
			}
			
			// --- The Current log with Priority type ---
			if (isset($currentPriorityType) && $currentPriorityType) {
				// Do a search if needed
				if (RequestContext::value('agent_id')
					|| RequestContext::value('node_id')
					|| RequestContext::value('category'))
				{
					$criteria = array();
					$criteria['start'] =& $this->minDate();
					$criteria['end'] =& DateAndTime::tomorrow();
					if (RequestContext::value('agent_id'))
						$criteria['agent_id'] =& $idManager->getId(
													RequestContext::value('agent_id'));
					if (RequestContext::value('node_id'))
						$criteria['node_id'] =& $idManager->getId(
													RequestContext::value('node_id'));
					if (RequestContext::value('category'))
						$criteria['category'] = urldecode(RequestContext::value('category'));
					
					$searchType =& new Type("logging_search", "edu.middlebury", "Date-Range/Agent/Node");
					$entries =& $log->getEntriesBySearch($criteria, $searchType, 
											$formatType, $currentPriorityType);
					if ($entries->hasNext())
						$firstEntry =& $entries->next();
					$entries =& $log->getEntriesBySearch($criteria, $searchType, 
											$formatType, $currentPriorityType);
				} else {
					$entries =& $log->getEntries($formatType, $currentPriorityType);
					if ($entries->hasNext())
						$firstEntry =& $entries->next();
					$entries =& $log->getEntries($formatType, $currentPriorityType);
				}
			}
		}
		
		$this->setTitle($currentLogName." ".$currentPriorityType->getKeyword()." "._("Logs"));
		$this->setLink($harmoni->request->quickURL('logs', 'browse'));
		
		$this->addCategory("Logs");
		
		ob_start();
		print $currentLogName." "._("logs of priority,");
		print " ".$currentPriorityType->getKeyword();
		
		if (RequestContext::value('agent_id')) {
			print "\n<p style='text-indent: 0.5in'>";
			print _("Limited to agent: ");
			$id =& $idManager->getId(RequestContext::value('agent_id'));
			$agent =& $agentManager->getAgent($id);
			print $agent->getDisplayName();
			print "</p>";
		}
		if (RequestContext::value('node_id')) {
			print "\n<p style='text-indent: 0.5in'>";
			print _("Limited to node: ");
			$id =& $idManager->getId(RequestContext::value('node_id'));
			$node =& $hierarchyManager->getNode($id);
			if ($node->getDisplayName())
				print $node->getDisplayName();
			else
				print _("Id: ").$nodeId->getIdString();
			print "</p>";
		}
		if (RequestContext::value('category')) {
			print "\n<p style='text-indent: 0.5in'>";
			print _("Limited to category: ");
			print urldecode(RequestContext::value('category'));
			print "</p>";
		}
		$this->setDescription(ob_get_clean());
		
		$i = 0;
		while ($entries->hasNext() && $i < 30) {
			$this->addEntry($entries->next());
			$i++;
		}
		
		textdomain($defaultTextDomain);
	}
	
	/**
	 * Print out a log entry
	 * 
	 * @param object Entry $entry
	 * @return void
	 * @access public
	 * @since 8/7/06
	 */
	function addEntry ( &$entry ) {
		$rssItem =& $this->addItem(new RSSItem);
		$harmoni =& Harmoni::instance();
		$agentManager =& Services::getService("Agent");
		$hierarchyManager = Services::getService("Hierarchy");
		
		$timestamp =& $entry->getTimestamp();
		$timestamp =& $timestamp->asTimestamp();
		$item =& $entry->getItem();
		$desc =& HtmlString::fromString($item->getDescription());
		
		// a title
		$rssItem->setTitle($desc->stripTagsAndTrim(5));
		
		// Date of occurance
		$rssItem->setPubDate($timestamp);
		
		// A unique id...
		$rssItem->setGUID(
			md5($timestamp->asUnixTimeStamp()
				.$item->getDescription()
				.$item->getBacktrace()),
			false);
			
		// Category
		$rssItem->addCategory($item->getCategory());
		
		// Agent / 'author'
		$agentList = '';
		$agentIds =& $item->getAgentIds(true);
		while ($agentIds->hasNext()) {
			$agentId =& $agentIds->next();
			if ($agentManager->isAgent($agentId) || $agentManager->isGroup($agentId)) {
				$agent =& $agentManager->getAgent($agentId);
				$agentList .= $agent->getDisplayName();
			} else
				$agentList .= _("Id: ").$agentId->getIdString();
			if ($agentIds->hasNext())
				$agentList .= ", ";
		}
		$rssItem->setAuthor($agentList);
		
		// Agents with links
		ob_start();
		$agentIds =& $item->getAgentIds(true);
		$authorList = '';
		while ($agentIds->hasNext()) {
			$agentId =& $agentIds->next();
			if ($agentManager->isAgent($agentId) || $agentManager->isGroup($agentId)) {
				$agent =& $agentManager->getAgent($agentId);
				print "<a href='";
				print $harmoni->request->quickURL("logs", "browse",
						array(	"agent_id" => $agentId->getIdString()));
				print "'>";
				print $agent->getDisplayName();
				print "</a>";
				$authorList .= $agent->getDisplayName();
			} else {
				print _("Id: ").$agentId->getIdString();
				$authorList .= _("Id: ").$agentId->getIdString();
			}
			
			if ($agentIds->hasNext()) {
				print ", <br/>";
				$authorList .= ", ";
			}
		}
		$agentList = ob_get_clean();
		
		// Nodes
		ob_start();
		$nodeIds =& $item->getNodeIds(true);
		while ($nodeIds->hasNext()) {
			$nodeId =& $nodeIds->next();
			print "<a href='";
			print $harmoni->request->quickURL("logs", "browse",
					array(	"node_id" => $nodeId->getIdString()));
			print "'>";
			if ($hierarchyManager->nodeExists($nodeId)) {
				$node =& $hierarchyManager->getNode($nodeId);
				if ($node->getDisplayName())
					print $node->getDisplayName();
				else
					print _("Id: ").$nodeId->getIdString();
			} else {
				print _("Id: ").$nodeId->getIdString();
			}
			print "</a>";
			if ($nodeIds->hasNext())
				print ", <br/>";
		}
		$nodeList = ob_get_clean();
		
		
		// Description text
		ob_start();
		print "\n\t\t\t\t<dl>";
		
		print "\n\t\t\t\t\t<dt style='font-weight: bold;'>"._("Date: ")."</dt>";
		print "\n\t\t\t\t\t<dd style='margin-bottom: 20px;'>";
		print $timestamp->monthName()." ";
		print $timestamp->dayOfMonth().", ";
		print $timestamp->year()." ";
		print $timestamp->hmsString();
		print "</dd>";
		
		print "\n\t\t\t\t\t<dt style='font-weight: bold;'>"._("Category: ")."</dt>";
		print "\n\t\t\t\t\t<dd style='margin-bottom: 20px;'>".$item->getCategory()."</dd>";
		
		print "\n\t\t\t\t\t<dt style='font-weight: bold;'>"._("Description: ")."</dt>";
		$desc->clean();
		print "\n\t\t\t\t\t<dd style='margin-bottom: 20px;'>".$desc->asString()."</dd>";
		
		print "\n\t\t\t\t\t<dt style='font-weight: bold;'>"._("Agents: ")."</dt>";
		print "\n\t\t\t\t\t<dd style='margin-bottom: 20px;'>".$agentList."</dd>";
		
		print "\n\t\t\t\t\t<dt style='font-weight: bold;'>"._("Nodes: ")."</dt>";
		print "\n\t\t\t\t\t<dd style='margin-bottom: 20px;'>".$nodeList."</dd>";
		
		print "\n\t\t\t\t\t<dt style='font-weight: bold;'>"._("Backtrace: ")."</dt>";
		print "\n\t\t\t\t\t<dd style='margin-bottom: 20px;'>".$item->getBacktrace()."</dd>";
		
		print "\n\t\t\t\t</dl>";
		$rssItem->setDescription(ob_get_clean());
	}
	
	/**
	 * Answer the minumum date to display
	 * 
	 * @return object DateAndTime
	 * @access public
	 * @since 3/8/06
	 */
	function &minDate () {
		return DateAndTime::withYearDay(2000, 1);
	}
}

?>