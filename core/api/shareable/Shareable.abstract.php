<?php

require_once(POLYPHONY."core/api/shareable/Shareable.interface.php");

/**
 * The Shareable abstract sets some default functionality for a Shareable class.
 * 
 * The constructor should set all the private variables (excluding $_system) to the
 * appropriate values.
 * 
 * A Shareable is a special object used by applications in order to share data-parts
 * between programs. By storing a system, subsystem and ID, any program has access
 * to parts of another program, referenced by these fields.
 *
 * @package polyphony.interfaces.api.shareable
 * @version $Id: Shareable.abstract.php,v 1.1 2003/08/05 21:03:44 gabeschine Exp $
 * @copyright 2003 
 **/

class ShareableAbstract extends Shareable {
	/**
	 * @access private
	 * @var string $_displayName The display name.
	 **/
	var $_displayName;
	
	/**
	 * @access private
	 * @var string $_description The description of this Shareable.
	 **/
	var $_description;
	
	/**
	 * @access private
	 * @var string $_subsystem This Shareable's "subsystem".
	 **/
	var $_subsystem;
	
	/**
	 * @access private
	 * @var string $_system This Shareable's system. Set remotely.
	 **/
	var $_system;
	
	/**
	 * @access private
	 * @var string $_permittedParents An array of permitted parents. Format:
	 * [system]=>array("subsystem1",...),...
	 **/
	var $_permittedParents;
	
	/**
	 * Returns this Shareable's display name.
	 * @access public
	 * @return string
	 **/
	function getName() {
		return $this->_displayName;
	}
	
	/**
	 * Returns this Shareable's description.
	 * @access public
	 * @return string
	 **/
	function getDescription() {
		return $this->_description;
	}
	
	/**
	 * Returns this Shareable's subsystem-id. This is a string that describes
	 * this specific shareable's functionality. For example, for a Segue2 story text
	 * object, it might be "segue2story_text".
	 * @access public
	 * @return string
	 **/
	function getSubsystem() {
		return $this->_subsystem;
	}
	
	/**
	 * Returns this Shareable's system ID. This is a short string describing
	 * the specific system this shareable comes from. This will be set at
	 * system registration time.
	 * @access public
	 * @return string
	 **/
	function getSystem() {
		return $this->_system;
	}
	
	/**
	 * Sets this shareable object's system ID to $system. This is used when a 
	 * new shareable object is created, since the same classname can be used
	 * for multiple systems (two instances of the same application running
	 * on the same machine, registered under different system IDs).
	 * @access public
	 * @return void
	 **/
	function setSystem($system) {
		$this->_system = $system;
	}
	
	/**
	 * Returns an associative array of permitted parent system/subsystem types. This is used
	 * to restrict where an end user can add a specific shareable in the hierarchy.
	 * The array is of the format [system]=>array(subsystem1, subsystem2,...),...
	 * @access public
	 * @return array
	 **/
	function getPermittedParents() {
		return $this->_permittedParents;
	}
}

?>