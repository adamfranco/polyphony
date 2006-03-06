<?php

/**
 * @package polyphony.modules.logging
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: browse.act.php,v 1.2 2006/03/06 19:18:49 adamfranco Exp $
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
 * @version $Id: browse.act.php,v 1.2 2006/03/06 19:18:49 adamfranco Exp $
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
// 		$log->appendLogWithTypes(new AgentNodeEntryItem("View Logs"), $formatType, $priorityType);
		
		
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
		$actionRows->add(new Block(ob_get_clean(), STANDARD_BLOCK), "100%", null, LEFT, TOP);
		
		
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
	<th></th>
	<th>timestamp</th>
	<th>priority</th>
	<th>description</th>
	<th>trace</th>
	<th>agents</th>
	<th>nodes</th>
</tr>

END;
	
		$entries =& $log->getEntries($formatType, $currentPriorityType);
		$i = $entries->count();
		while ($entries->hasNext()) {
			$entry =& $entries->next();
			print "\n\t<tr>";
			
			print "\n\t\t<td style='text-align: right'>$i</td>";
			$i--;
			
			$timestamp =& $entry->getTimestamp();
			print "\n\t\t<td>".$timestamp->asString()."</td>";
			
			$priority =& $entry->getPriorityType();
			print "\n\t\t<td>".$priority->getKeyword()."</td>";
			
			$item =& $entry->getItem();
			
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
				$agent =& $agentManager->getAgent($agentIds->next());
				print $agent->getDisplayName();
				if ($agentIds->hasNext())
					print ", ";
			}
			print "\n\t\t</td>";
			
			print "\n\t\t<td>";
			$nodeIds =& $item->getNodeIds(true);
			while ($nodeIds->hasNext()) {
				$node =& $hierarchyManager->getNode($nodeIds->next());
				print $node->getDisplayName();
				if ($nodeIds->hasNext())
					print ", ";
			}
			print "\n\t\t</td>";
			
			print "\n\t</tr>";
		}
		
		print "\n</table>";
		$actionRows->add(new Block(ob_get_clean(), STANDARD_BLOCK), "100%", null, LEFT, TOP);
 		
 		textdomain($defaultTextDomain);
	}
}