<?php


/**
 * @const integer ITEM_CONTENT 
 * @package polyphony.api.shareable
 **/
define("ITEM_CONTENT",1);

/**
 * @const integer ITEM_CONTENTPAGE 
 * @package polyphony.api.shareable
 **/
define("ITEM_CONTENTPAGE",2);

/**
 * @const integer ITEM_CONTENTCONTAINER 
 * @package polyphony.api.shareable
 **/
define("ITEM_CONTENTCONTAINER",4);

/**
 * @const integer ITEM_NAVIGATIONLEVEL 
 * @package polyphony.api.shareable
 **/
define("ITEM_NAVIGATIONLEVEL",8);

/**
 * The Shareable interface defines required methods for any Shareable class.
 * 
 * A Shareable is a special object used by applications in order to share data-parts
 * between programs. By storing a system, subsystem and ID, any program has access
 * to parts of another program, referenced by these fields.
 *
 * @package polyphony.interfaces.api.shareable
 * @version $Id: Shareable.interface.php,v 1.3 2003/08/08 22:06:55 gabeschine Exp $
 * @copyright 2003 
 **/

class Shareable {
	/**
	 * Tells the shareable to commit its data to whatever system it uses, if any.
	 * This allows the program taking advantage of this shareable to store more
	 * information pertaining to this specific object, such as display options.
	 * @param integer $dbIndex The ID of the DBHandler connection to use for database storage.
	 * @access public
	 * @return boolean Success/failure.
	 **/
	function commit($dbIndex) {
		die ("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class."); 
	}
	
	/**
	 * Tells the shareable to purge all information it has stored locally for
	 * the current ID. This is almost never used, as no content is ever deleted unless
	 * an administrator is doing cleaning.
	 * @param integer $dbIndex the ID of the DBHandler connection to use.
	 * @access public
	 * @return boolean Success/failure
	 **/
	function purge($dbIndex) {
		die ("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class."); 
	}
	
	/**
	 * Tells the Shareable to check the database to make sure that it has the necessary
	 * tables specific to this shareable to allow smooth operation. This is typically called
	 * on startup to install all the necessary tables in the DB, or when new shareables
	 * are made available to an already running system.
	 * @param integer $dbIndex The ID of the DBHandler connection to use.
	 * @access public
	 * @return void
	 **/
	function checkDBTables($dbIndex) {
		die ("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class."); 
	}
	
	/**
	 * Returns this Shareable's display name.
	 * @access public
	 * @return string
	 **/
	function getName() {
		die ("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class."); 
	}
	
	/**
	 * Returns this Shareable's description.
	 * @access public
	 * @return string
	 **/
	function getDescription() {
		die ("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class."); 
	}
	
	/**
	 * Returns this Shareable's subsystem-id. This is a string that describes
	 * this specific shareable's functionality. For example, for a Segue2 story text
	 * object, it might be "segue2story_text".
	 * @access public
	 * @return string
	 **/
	function getSubsystem() {
		die ("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class."); 
	}
	
	/**
	 * Returns this Shareable's system ID. This is a short string describing
	 * the specific system this shareable comes from. This will be set at
	 * system registration time.
	 * @access public
	 * @return string
	 **/
	function getSystem() {
		die ("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class."); 
	}
	
	/**
	 * Returns this Shareable's class. This can be a combination of: ITEM_CONTENT, ITEM_CONTENTPAGE,
	 * ITEM_CONTENTCONTAINER or ITEM_NAVIGATIONLEVEL. They can be joined using Bitwise-OR.
	 * @access public
	 * @return integer
	 **/
	function getClass() {
		die ("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class."); 
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
		die ("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class."); 
	}
	
	/**
	 * Returns this Shareable's unique ID.
	 * @access public
	 * @return integer The ID.
	 **/
	function getID() {
		die ("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class."); 
	}
	
	/**
	 * Handles HTTP data coming from the user. The object should use it to populate
	 * some type of private data storage for later use with the commit() method.
	 * @param object A FieldSet object containing the HTTP data.
	 * @access public
	 * @return void
	 **/
	function handleHTTPData($data) {
		die ("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class."); 
	}
	
	/**
	 * Takes a row from a database and populates the internal storage with
	 * values that are expected from the database.
	 * @param array $row A row of data from the database: [fieldname]=>value, ...
	 * @access public
	 * @return void
	 **/
	function handleDBRow($row) {
		die ("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class."); 
	}
	
	/**
	 * Modifies a SelectQuery so that Shareable-specific data is included with the
	 * query when data is fetched from the DB. This method may, for example, add
	 * tables to the select query with JOIN syntax and add columns (fields) to be
	 * fetch as well. The row will eventually be passed onto handleDBRow() if it
	 * corresponds to this type of shareable. 
	 * @param ref object A SelectQuery object.
	 * @access public
	 * @return void
	 **/
	function modifySelectQuery(&$query) {
		die ("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class."); 
	}
	
	/**
	 * 
	 * @access public
	 * @return void
	 **/
	function buildLayout($expecting) {
		die ("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class."); 
	}
	
	// @todo -cShareable Implement Shareable.buildLayout
	// @todo -cShareable Implement Shareable.modifyWizard
}

?>