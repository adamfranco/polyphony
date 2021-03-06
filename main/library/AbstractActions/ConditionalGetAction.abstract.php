<?php
/**
 * @since 5/13/08
 * @package polyphony.AbstractActions
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(dirname(__FILE__).'/Action.class.php');
require_once(dirname(__FILE__).'/ConditionalGetHelper.class.php');

/**
 * <##>
 * 
 * @since 5/13/08
 * @package polyphony.AbstractActions
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
abstract class ConditionalGetAction
	extends Action
{
		
	/**
	 * Execute this action.
	 * 
	 * @return mixed
	 * @access public
	 * @since 4/25/05
	 */
	final public function execute () {
		if (!$this->isAuthorizedToExecute())
			$this->getUnauthorizedMessage();
		
		$helper = new ConditionalGetHelper(	array($this, 'outputContent'),
											array($this, 'getModifiedDateAndTime'),
											array($this, 'getCacheDuration'));
		$helper->execute();
	}
	
	/**
	 * Output the content
	 * 
	 * @return null
	 * @access public
	 * @since 5/13/08
	 */
	abstract public function outputContent ();
	
	/**
	 * Answer the last-modified timestamp for this action/id.
	 * 
	 * @return object DateAndTime
	 * @access public
	 * @since 5/13/08
	 */
	abstract public function getModifiedDateAndTime ();
	
	/**
	 * Answer the delay (in seconds) that the modification time should be cached without
	 * checking the source again. 
	 * 
	 * @return object Duration
	 * @access public
	 * @since 5/13/08
	 */
	public function getCacheDuration () {
		// A default of 1 minute is used. Override this method to add longer
		// or shorter times.
		return Duration::withMinutes(1);
	}
}

?>