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

require_once(dirname(__FILE__)."/ForceAuthAction.class.php");
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
abstract class ForceAuthConditionalGetAction
	extends ForceAuthAction
{
		
	/**
	 * Execute this action.
	 * 
	 * @return mixed
	 * @access public
	 * @since 4/25/05
	 */
	final public function execute () {
		// Force Authentication piece
		if (!$this->isAuthorizedToExecute())
			$this->getUnauthorizedMessage();
		
		$helper = new ConditionalGetHelper(	array($this, 'outputContent'),
											array($this, 'getModifiedDateAndTime'),
											array($this, 'getCacheDuration'));
		try {
			$helper->execute();
		} catch (UnimplementedException $e) {
			// If this Action just doesn't support a getModifiedDateAndTime() method
			// allow us to just continue and output the content
			if ($e->getCode() == -304)
				$this->outputContent();
			else
				throw $e;
		}
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