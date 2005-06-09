<?

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
 * @version $Id: process_authorizations.act.php,v 1.11 2005/06/09 21:31:33 adamfranco Exp $
 */

// Get services
$idManager =& Services::getService("Id");
$authZ =& Services::getService("AuthZ");

$harmoni->request->startNamespace("polyphony-agents");

// Get info passed to this action via the URL
$createOrDelete = RequestContext::value("operation");
$agentIdString = RequestContext::value("agentId");
$functionIdString = RequestContext::value("functionId");
$qualifierIdString = RequestContext::value("qualifierId");

// Get Ids from these strings
$agentId =& $idManager->getId($agentIdString);
$functionId =& $idManager->getId($functionIdString);
$qualifierId =& $idManager->getId($qualifierIdString);


// Process authorizations
if ($createOrDelete == 'create') {
	$authZ->createAuthorization($agentId, $functionId, $qualifierId);

} else {
	$authorizations =& $authZ->getExplicitAZs($agentId, $functionId, $qualifierId, false);
	while ($authorizations->hasNext()) {
		$authorization =& $authorizations->next();
		$authZ->deleteAuthorization($authorization);

	}
}

$harmoni->request->endNamespace();


$harmoni->history->goBack("polyphony/agents/process_authorizations");