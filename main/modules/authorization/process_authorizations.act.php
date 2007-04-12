<?php

/**
 * process_authorizations.act.php
 * This action will create or delete authorizations as specified by edit_authorizations.act.php
 * 11/18/04 Ryan Richards
 *
 * @package polyphony.modules.authorization
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: process_authorizations.act.php,v 1.13 2007/04/12 15:37:35 adamfranco Exp $
 */

class process_authorizationsAction {
	function execute() {
		$harmoni =& Harmoni::instance();
		// Get services
		$idManager =& Services::getService("Id");
		$authZ =& Services::getService("AuthZ");

		$harmoni->request->startNamespace("polyphony-authorizations");

		// Get info passed to this action via the URL
		$operation = RequestContext::value("operation");
		$functionIdString = RequestContext::value("functionId");
		$qualifierIdString = RequestContext::value("qualifierId");

		$agentList = array();
		if (RequestContext::value("mult")) {
			$agentList = unserialize(urldecode(RequestContext::value("agents")));
		} else {
			$agentList = array(RequestContext::value("agentId"));
		}

		// Process authorizations
		if ($operation == 'create') {
			// Get Ids from these strings
			$functionId =& $idManager->getId($functionIdString);
			$qualifierId =& $idManager->getId($qualifierIdString);
						
			foreach ($agentList as $agentIdString) {
				$authZ->createAuthorization($idManager->getId($agentIdString), $functionId, $qualifierId);		
			}
		} else if ($operation == 'delete')  {
			// Get Ids from these strings
			$functionId =& $idManager->getId($functionIdString);
			$qualifierId =& $idManager->getId($qualifierIdString);
			
			foreach ($agentList as $agentIdString) {
				$authorizations =& $authZ->getExplicitAZs($idManager->getId($agentIdString), $functionId, $qualifierId, false);
				while ($authorizations->hasNext()) {
					$authorization =& $authorizations->next();
/*					$qualifier =& $authorization->getQualifier();
					$function =& $authorization->getFunction();
					$qualifierId =& $qualifier->getId();
					$functionId =& $function->getId();
					print "auth -> function: ".$functionId->getIdString().", qualifier: ".$qualifierId->getIdString()."<br/>";
*/					$authZ->deleteAuthorization($authorization);
				}
			}
		} else if ($operation == 'delete_all') {
			// clear all authorizations for the users selected
			foreach ($agentsList as $agentIdString) {
				$authorizations =& $authZ->getAllExplicitAZsForAgent($idManager->getId($agentIdString), false);
				
				while($authorizations->hasNext()) {
					$authorization =& $authorizations->next();
					$authZ->deleteAuthorization($authorization);
				}
			}
		}

		$harmoni->request->endNamespace();

		$harmoni->history->goBack("polyphony/agents/process_authorizations");		
	}
}
