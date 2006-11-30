<?php
/**
 * @since 11/29/06
 * @package polyphony.modules.authorization
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AuthZXmlAction.class.php,v 1.2 2006/11/30 22:02:42 adamfranco Exp $
 */ 
 
require_once(POLYPHONY_DIR.'/main/library/AbstractActions/XmlAction.class.php');

/**
 * This class implements common authorization methods
 * 
 * @since 11/29/06
 * @package polyphony.modules.authorization
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AuthZXmlAction.class.php,v 1.2 2006/11/30 22:02:42 adamfranco Exp $
 */
class AuthZXmlAction
	extends XmlAction
{

	/**
	 * Execute this action
	 * 
	 * @return void
	 * @access public
	 * @since 11/29/06
	 */
	function execute () {
		if (!$this->isAuthorizedToExecute())
			$this->getUnauthorizedMessage();
		else
			$this->buildContent();		
	}
		
	/**
	 * Answer the Id of the qualifier
	 * 
	 * @return object Id
	 * @access public
	 * @since 11/29/06
	 */
	function &getQualifierId () {
		if (!isset($this->_qualifierId)) {
			$idManager =& Services::getService("Id");
			$harmoni =& Harmoni::instance();
			$harmoni->request->startNamespace("polyphony-authz");
			
			if (!RequestContext::value('qualifier_id'))
				$this->error("No Qualifier Id");
			
			$this->_qualifierId = $idManager->getId(
				RequestContext::value('qualifier_id'));
			
			$harmoni->request->endNamespace();
		}
		return $this->_qualifierId;
	}
	
	/**
	 * Answer the Id of the qualifier
	 * 
	 * @return object Id
	 * @access public
	 * @since 11/29/06
	 */
	function &getFunctionId () {
		if (!isset($this->_functionId)) {
			$idManager =& Services::getService("Id");
			$harmoni =& Harmoni::instance();
			$harmoni->request->startNamespace("polyphony-authz");
			
			if (!RequestContext::value('function_id'))
				$this->error("No Function Id");
			
			$this->_functionId = $idManager->getId(
				RequestContext::value('function_id'));
			
			$harmoni->request->endNamespace();
		}
		return $this->_functionId;
	}
	
	/**
	 * Answer the Id of the agent
	 * 
	 * @return object Id
	 * @access public
	 * @since 11/29/06
	 */
	function &getAgentId () {
		if (!isset($this->_agentId)) {
			$idManager =& Services::getService("Id");
			$harmoni =& Harmoni::instance();
			$harmoni->request->startNamespace("polyphony-authz");
			
			if (!RequestContext::value('agent_id'))
				$this->error("No Agent Id");
			
			$this->_agentId = $idManager->getId(
				RequestContext::value('agent_id'));
			
			$harmoni->request->endNamespace();
		}
		return $this->_agentId;
	}
	
}

?>