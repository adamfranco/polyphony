<?php

/**
 * @package polyphony.modules.logging
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: browse.act.php,v 1.6 2006/03/09 19:47:51 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(HARMONI."GUIManager/Components/Blank.class.php");

/**
 * This action will allow for the modification of group Membership.
 *
 * @since 11/10/04 
 * 
 * @package polyphony.modules.agents
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: browse.act.php,v 1.6 2006/03/09 19:47:51 adamfranco Exp $
 */
class browseAction 
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
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return dgettext("polyphony", "Browse Logs");
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
		
		$actionRows =& $this->getActionRows();
		$harmoni =& Harmoni::instance();
		
		$harmoni->request->startNamespace("polyphony-logs");
		$harmoni->request->passthrough('log', 'priority',
			'startYear', 'startMonth', 'startDay',
			'endYear', 'endMonth', 'endDay', 
			'agent_id', 'node_id');

		$agentManager =& Services::getService("Agent");
		$idManager = Services::getService("Id");
		$hierarchyManager = Services::getService("Hierarchy");
		
		
		
		/*********************************************************
		 * the log search form
		 *********************************************************/
		// Log header
		$actionRows->add(new Heading(_("Logs"), 2), "100%", null, LEFT, CENTER);
		
		
		$loggingManager =& Services::getService("Logging");
		
		$log =& $loggingManager->getLogForWriting("test_log");
		$formatType =& new Type("logging", "edu.middlebury", "AgentsAndNodes",
						"A format in which the acting Agent[s] and the target nodes affected are specified.");
		$priorityType =& new Type("logging", "edu.middlebury", "normal",
						"An action which involves reading.");
		
		
		// Links to other logs
		$logNames =& $loggingManager->getLogNamesForReading();
		ob_start();
		
		if (RequestContext::value("log"))
			$currentLogName = RequestContext::value("log");
		
		while ($logNames->hasNext()) {
			$logName = $logNames->next();
			
			if (!isset($currentLogName))
				$currentLogName = $logName;
			
			if ($logName != $currentLogName) {
				print "\n<a href='";
				print $harmoni->request->quickURL("logs", "browse",
						array(	"log" => $logName));
				print "'>".$logName."</a>";
			} else
				print $logName;
			
			if ($logNames->hasNext())
				print " | ";
		
		}
		
		print " 
	<table border='0'>
		<tr>
			<th valign='top'>"._("Date Range: ")."</th>
			<td>

";
		
		$startDate =& $this->getStartDate();
		$endDate =& $this->getEndDate();
		$this->printDateRangeForm($startDate, $endDate);

		print "

			</td>
		</tr>
";
		
		if (RequestContext::value('agent_id') || RequestContext::value('node_id')) {
			print "
		<tr>
			<th>"._("Filters:")."</th>
			<td>
";		
	
			if (RequestContext::value('agent_id')) {
				print "\n\t\t\t";
				$id =& $idManager->getId(RequestContext::value('agent_id'));
				$url = $harmoni->request->quickURL("logs", "browse",
								array(	"agent_id" => ''));
				$agent =& $agentManager->getAgent($id);
				print $agent->getDisplayName();
				print "\n\t\t\t\t<input type='button' onclick='window.location=\"";
				print str_replace('&amp;', '&', $url);
				print "\"' value='X'/>";
				
			}
			
			if (RequestContext::value('agent_id') && RequestContext::value('node_id'))
				print "\n\t\t\t &nbsp; &nbsp; &nbsp; &nbsp; ";
			
			if (RequestContext::value('node_id')) {
				print "\n\t\t\t";
				$id =& $idManager->getId(RequestContext::value('node_id'));
				$url = $harmoni->request->quickURL("logs", "browse",
								array(	"node_id" => ''));
				$node =& $hierarchyManager->getNode($id);
				print $node->getDisplayName();
				print "\n\t\t\t\t<input type='button' onclick='window.location=\"";
				print str_replace('&amp;', '&', $url);
				print "\"' value='X'/>";
			}
		}
		
		print "\n\t</table>";
		
		$actionRows->add(new Block(ob_get_clean(), STANDARD_BLOCK), "100%", null, LEFT, TOP);
		
		
		// --- The Current log ---
		if (isset($currentLogName)) {
			$log =& $loggingManager->getLogForReading($currentLogName);
			$actionRows->add(new Heading($log->getDisplayName(), 3), "100%", null, LEFT, CENTER);
			ob_start();
			
			
			// Links to other priorities
			print "<strong>"._("Priority: ")."</strong>";
			if (RequestContext::value("priority"))
				$currentPriorityType =& Type::stringToType(
											RequestContext::value("priority"));
				
			$priorityTypes =& $loggingManager->getPriorityTypes();
			while ($priorityTypes->hasNext()) {
				$priorityType =& $priorityTypes->next();
								
				if (!isset($currentPriorityType))
					$currentPriorityType =& $priorityType;
				
				if (!$priorityType->isEqual($currentPriorityType)) {
					print "\n<a href='";
					print $harmoni->request->quickURL("logs", "browse",
							array(	"log" => RequestContext::value("log"),
									"priority" => Type::typeToString($priorityType)));
					print "'>".$priorityType->getKeyword()."</a>";
				} else
					print $priorityType->getKeyword();
				
				if ($priorityTypes->hasNext())
					print " | ";
			}
			
			
			// Entries
			print<<<END
	
	<script type='text/javascript'>
		/* <![CDATA[ */
	
		function showTrace(buttonElement) {
			newWindow = window.open("", "traceWindow", 'toolbar=no,width=600,height=500,resizable=yes,scrollbars=yes,status=no')
			// the next sibling is text, the one after that is our hidden div
			newWindow.document.write(buttonElement.nextSibling.nextSibling.innerHTML)
			newWindow.document.bgColor="lightpink"
			newWindow.document.close() 
		}
	
		/* ]]> */
	</script>
	
	<table border='1'>
	<tr>
		<th>timestamp</th>
		<th>category</th>
		<th>description</th>
		<th>trace</th>
		<th>agents</th>
		<th>nodes</th>
	</tr>
	
END;
			// Do a search if needed
			if (!$startDate->isEqualTo($this->minDate())
				|| !$endDate->isEqualTo(DateAndTime::tomorrow())
				|| RequestContext::value('agent_id')
				|| RequestContext::value('node_id'))
			{
				$criteria = array();
				$criteria['start'] =& $startDate;
				$criteria['end'] =& $endDate;
				if (RequestContext::value('agent_id'))
					$criteria['agent_id'] =& $idManager->getId(
												RequestContext::value('agent_id'));
				if (RequestContext::value('node_id'))
					$criteria['node_id'] =& $idManager->getId(
												RequestContext::value('node_id'));
				$searchType =& new Type("logging_search", "edu.middlebury", "Date-Range/Agent/Node");
				$entries =& $log->getEntriesBySearch($criteria, $searchType, 
										$formatType, $currentPriorityType);
			} else {
				$entries =& $log->getEntries($formatType, $currentPriorityType);
			}
			$i = $entries->count();
			while ($entries->hasNext()) {
				$entry =& $entries->next();
				print "\n\t<tr>";
				
// 				print "\n\t\t<td style='text-align: right'>$i</td>";
// 				$i--;
				
				$timestamp =& $entry->getTimestamp();
				print "\n\t\t<td>".$timestamp->asString()."</td>";
				
				$item =& $entry->getItem();
				
				print "\n\t\t<td>".$item->getCategory()."</td>";
				
				print "\n\t\t<td>".$item->getDescription()."</td>";
				
				print "\n\t\t<td>";
				if ($trace = $item->getBacktrace()) {
					print "\n\t\t\t<input type='button' value='"._("Show Trace")."' onclick='showTrace(this)'/>";
					print "\n\t\t\t<div style='display: none'>".$trace."</div>";
				}
				print "</td>";
				
				print "\n\t\t<td>";
				$agentIds =& $item->getAgentIds(true);
				while ($agentIds->hasNext()) {
					$agentId =& $agentIds->next();
					$agent =& $agentManager->getAgent($agentId);
					print "<a href='";
					print $harmoni->request->quickURL("logs", "browse",
							array(	"agent_id" => $agentId->getIdString()));
					print "'>";
					print $agent->getDisplayName();
					print "</a>";
					if ($agentIds->hasNext())
						print ", ";
				}
				print "\n\t\t</td>";
				
				print "\n\t\t<td>";
				$nodeIds =& $item->getNodeIds(true);
				while ($nodeIds->hasNext()) {
					$nodeId =& $nodeIds->next();
					print "<a href='";
					print $harmoni->request->quickURL("logs", "browse",
							array(	"node_id" => $nodeId->getIdString()));
					print "'>";
					if ($hierarchyManager->nodeExists($nodeId)) {
						$node =& $hierarchyManager->getNode($nodeId);
						print $node->getDisplayName();
					} else {
						print $nodeId->getIdString();
					}
					print "</a>";
					if ($nodeIds->hasNext())
						print ", <br/>";
				}
				print "\n\t\t</td>";
				
				print "\n\t</tr>";
			}
			
			print "\n</table>";
			$actionRows->add(new Block(ob_get_clean(), STANDARD_BLOCK), "100%", null, LEFT, TOP);
		}
 		
 		textdomain($defaultTextDomain);
	}
	
	/**
	 * Answer the current starting date
	 * 
	 * @return object DateAndTime
	 * @access public
	 * @since 3/8/06
	 */
	function &getStartDate () {
		if (RequestContext::value("startYear"))
			return DateAndTime::withYearMonthDay(
								RequestContext::value("startYear"),
								RequestContext::value("startMonth"),
								RequestContext::value("startDay"));
		else
			return $this->minDate();
	}
	
	/**
	 * Answer the current end date
	 * 
	 * @return object DateAndTime
	 * @access public
	 * @since 3/8/06
	 */
	function &getEndDate () {
		if (RequestContext::value("endYear"))
			return DateAndTime::withYearMonthDay(
								RequestContext::value("endYear"),
								RequestContext::value("endMonth"),
								RequestContext::value("endDay"));
		else
			return DateAndTime::tomorrow();
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
	
	/**
	 * Print the dateRange form
	 * 
	 * @param object DateAndTime $startDate
	 * @param object DateAndTime $endDate
	 * @return void
	 * @access public
	 * @since 3/8/06
	 */
	function printDateRangeForm( &$startDate, &$endDate ) {
		$min =& $this->minDate();
		$max =& DateAndTime::tomorrow();
		$harmoni =& Harmoni::instance();
		
		print "\n<form action='";
		print $harmoni->request->quickURL('logs', 'browse'); 
		print "' method='post'>";
		
		print "\n\t<select name='".RequestContext::name("startMonth")."'>";
		$month = 1;
		while ($month <= 12) {
			print "\n\t\t<option value='".$month."'";
			print (($month == $startDate->month())?" selected='selected'":"");
			print ">".Month::nameOfMonth($month)."</option>";
			$month++;
		}
		print "\n\t</select>";
		
		print "\n\t<select name='".RequestContext::name("startDay")."'>";
		$day = 1;
		while ($day <= 31) {
			print "\n\t\t<option value='".$day."'";
			print (($day == $startDate->dayOfMonth())?" selected='selected'":"");
			print ">".$day."</option>";
			$day++;
		}
		print "\n\t</select>";
		
		print "\n\t<select name='".RequestContext::name("startYear")."'>";
		$year = $max->year();
		$minYear = $min->year();
		while ($year >= $minYear) {
			print "\n\t\t<option value='".$year."'";
			print (($year == $startDate->year())?" selected='selected'":"");
			print ">$year</option>";
			$year--;
		}
		print "\n\t</select>";
		
		print "\n\t<strong> to: </strong>";
		
		print "\n\t<select name='".RequestContext::name("endMonth")."'>";
		$month = 1;
		while ($month <= 12) {
			print "\n\t\t<option value='".$month."'";
			print (($month == $endDate->month())?" selected='selected'":"");
			print ">".Month::nameOfMonth($month)."</option>";
			$month++;
		}
		print "\n\t</select>";
		
		print "\n\t<select name='".RequestContext::name("endDay")."'>";
		$day = 1;
		while ($day <= 31) {
			print "\n\t\t<option value='".$day."'";
			print (($day == $endDate->dayOfMonth())?" selected='selected'":"");
			print ">".$day."</option>";
			$day++;
		}
		print "\n\t</select>";
		
		print "\n\t<select name='".RequestContext::name("endYear")."'>";
		$year = $max->year();
		$minYear = $min->year();
		while ($year >= $minYear) {
			print "\n\t\t<option value='".$year."'";
			print (($year == $endDate->year())?" selected='selected'":"");
			print ">$year</option>";
			$year--;
		}
		print "\n\t</select>";
		
		print "\n\t<input type='submit' value='Submit'/>";
		print "\n</form>";
	}
}