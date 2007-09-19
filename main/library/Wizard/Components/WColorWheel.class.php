<?php
/**
 * @since Jul 21, 2005
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 * @license This is distributed under the BY-NC-SA licence(http://creativecommons.org/licenses/by-nc-sa/2.0/). License for commercial use is not possible
 *
 * @version $Id: WColorWheel.class.php,v 1.4 2007/09/19 14:04:51 adamfranco Exp $
 */ 

require_once(POLYPHONY.'/main/library/Wizard/WizardComponent.abstract.php');

/**
 * This class allows for the creation of a ColorWheel color management tool.
 * 
 * @since Jul 21, 2005
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *  @license This is distributed under the BY-NC-SA licence(http://creativecommons.org/licenses/by-nc-sa/2.0/). License for commercial use is not possible
 *
 * @version $Id: WColorWheel.class.php,v 1.4 2007/09/19 14:04:51 adamfranco Exp $
 */
class WColorWheel 
	extends WizardComponent 
{

	var $_value;
	var $_settings='';
	var $_style = null;
	
	
	/**
	 * Constructor
	 * @access public
	 * @return WSelectList
	 */
	function WColorWheel () {
		// do nothing
	}
	
	/**
	 * sets the CSS style for the labels of the radio buttons.
	 * @param string $style
	 * @access public
	 * @return void
	 */
	function setStyle ($style) {
		$this->_style = $style;
	}
	
	/**
	 * Sets the value of this ColorWheel object.
	 * The value is a string containing the hex 
	 * representation of the selected colors.
	 * Colors come in groups of 4:first is the base
	 * color and then follow three variations. Some 
	 * schemata give more than one group of colors.
	 * Thus, the string starts with the number of 
	 * color groups returned by the ColorWheel and
	 * then follow the colors themselves(in hex) all
	 * separated by a semicolon. So an example could
	 * be:
	 * 1;#007D48;#BFFFE4;#80FFC9;#00B366;
	 * where you are given 1 group of 4 colors.
	 * 
	 * @param string $value
	 * @access public
	 * @return void
	 */
	function setValue ($value) {
		$this->_value = $value;
	}
	
	
	/**
	 * Gets the current Settings of the ColorWheel
	 * object. The settings are returned as a string
	 * with the attributes separated by semicolons.
	 * The order is:
	 * mainColor;preset;slider-value;safecolor;shift1;shift2;shift3;shift4;schema;
	 * @access public
	 * @return string settings
	 */
	function getSettings(){
		return $this->_settings;
	}
	
	/**
	 * Tells the wizard component to update itself - this may include getting
	 * form post data or validation - whatever this particular component wants to
	 * do every pageload. 
	 * @param string $fieldName The field name to use when outputting form data or
	 * similar parameters/information.
	 * @access public
	 * @return boolean - TRUE if everything is OK
	 */
	function update ($fieldName) {
		$val = RequestContext::value($fieldName.'_colors');
		if ($val) $this->_value = $val;
		$settings = RequestContext::value($fieldName.'_settings');
		if($settings) $this->_settings = $settings;
	}
	
	/**
	 * Returns the values of wizard-components. Should return an array if children are involved,
	 * otherwise a whatever type of object is expected.
	 * @access public
	 * @return mixed
	 */
	function getAllValues () {
		return $this->_value;
	}
	
	/**
	 * Returns a block of XHTML-valid code that contains markup for this specific
	 * component. 
	 * @param string $fieldName The field name to use when outputting form data or
	 * similar parameters/information.
	 * @access public
	 * @return string
	 */
	function getMarkup ($fieldName) {
		$guimanager = Services::getService("GUIManager");
		$name = RequestContext::name($fieldName);
		$value = $this->_value;
		$settings = $this->_settings;
		$colorwheelurl = "http://slug.middlebury.edu/~nstamato/polyphony/main/library/Wizard/Components/WColorWheelFiles/";


		$style = '';
		
		if ($this->_style) $style = " style=\"".addslashes($this->_style)."\""; 
		
		$guimanager->setHead($guimanager->getHead()."\n<link rel=\"stylesheet\" 
		href='".$colorwheelurl."colorwheel.css' type=\"text/css\">".
		"\n<script type=\"text/javascript\" src='".$colorwheelurl."colorblind.js'></script>".
		"\n<script type=\"text/javascript\" src='".$colorwheelurl."colorwheel.js'></script>"
		);
		

		$m ="<br /><div id=\"maindiv\" style='position:relative; border:1px solid #000; height:500px; width:800px; font:11px/1.2 verdana,sans-serif;'>
		
		<div id=\"maincolorsample\"></div>
		<div id='image'>
			<div id=\"wheelarea\"></div>
			<div id=\"pointer0\"></div>
			<div id=\"pointer1\"></div>
			<div id=\"pointer2\"></div>
			<div id=\"pointer3\"></div>
		</div>
		<div id=\"maincolorhue\"></div>
		
		
		<div id='scheme-select'>
			<img id=\"previmg-mono\"  src=".$colorwheelurl."prev_mono.gif\" alt=\"\" width=\"41\" height=\"52\" onclick=\"selectScheme('mono')\">
			<img id=\"previmg-compl\" class='previmg' src=".$colorwheelurl."prev_compl.gif\" alt=\"\" width=\"41\" height=\"52\" onclick=\"selectScheme('compl')\">
			<img id=\"previmg-triad\" class='previmg' src=".$colorwheelurl."prev_triad.gif\" alt=\"\" width=\"41\" height=\"52\" onclick=\"selectScheme('triad')\">
			<img id=\"previmg-tetrad\" class='previmg' src=".$colorwheelurl."prev_tetrad.gif\" alt=\"\" width=\"41\" height=\"52\" onclick=\"selectScheme('tetrad')\">
			<img id=\"previmg-analog\" class='previmg' src=".$colorwheelurl."prev_analog.gif\" alt=\"\" width=\"41\" height=\"52\" onclick=\"selectScheme('analog')\">
		
		</div>
		
		<div id=\"scheme-slider\">
			<div id=\"pointer-slider\"></div>
		</div>
		
		<div id=\"scheme-addcompl\">
			<input type=\"checkbox\" id=\"analogCompl\" onchange=\"createScheme(false)\" onclick=\"createScheme(false)\"> <label for=\"analogCompl\">add the complement</label>
		</div>
		
		<div id=\"scheme-searchrgb\">
			<a href=\"#\" onclick=\"searchRGB(false)\">Enter RGB</a> (rough conversion)
		</div>
		
		<div id=\"colsample\">
		
			<div id=\"color0\" class=\"col\">
				<div id=\"color0-0\" class=\"col-0\" onclick=\"drawVar(0,0)\"></div>
				<div id=\"color0-1\" class=\"col-1\" onclick=\"drawVar(0,1)\"></div>
				<div id=\"color0-2\" class=\"col-2\" onclick=\"drawVar(0,2)\"></div>
				<div id=\"color0-3\" class=\"col-3\" onclick=\"drawVar(0,3)\"></div>
			</div>
		
			<div id=\"color1\" class=\"col\">
				<div id=\"color1-0\" class=\"col-0\" onclick=\"drawVar(1,0)\"></div>
				<div id=\"color1-1\" class=\"col-1\" onclick=\"drawVar(1,1)\"></div>
				<div id=\"color1-2\" class=\"col-2\" onclick=\"drawVar(1,2)\"></div>
				<div id=\"color1-3\" class=\"col-3\" onclick=\"drawVar(1,3)\"></div>
			</div>
		
			<div id=\"color2\" class=\"col\">
				<div id=\"color2-0\" class=\"col-0\" onclick=\"drawVar(2,0)\"></div>
				<div id=\"color2-1\" class=\"col-1\" onclick=\"drawVar(2,1)\"></div>
				<div id=\"color2-2\" class=\"col-2\" onclick=\"drawVar(2,2)\"></div>
				<div id=\"color2-3\" class=\"col-3\" onclick=\"drawVar(2,3)\"></div>
			</div>
		
			<div id=\"color3\" class=\"col\">
				<div id=\"color3-0\" class=\"col-0\" onclick=\"drawVar(3,0)\"></div>
				<div id=\"color3-1\" class=\"col-1\" onclick=\"drawVar(3,1)\"></div>
				<div id=\"color3-2\" class=\"col-2\" onclick=\"drawVar(3,2)\"></div>
				<div id=\"color3-3\" class=\"col-3\" onclick=\"drawVar(3,3)\"></div>
			</div>
		
		
			<div id=\"textBlack\">Lorem ipsum <strong>dolor sit amet</strong></div>
			<div id=\"textWhite\"><strong>Lorem ipsum</strong> dolor sit amet</div>
		</div>
		
		<div id=\"coltable\"></div>
		
		<div id=\"websnapswitch\">
			<input type=\"checkbox\" id=\"websnapper\" onclick=\"switchWebSnap(this.checked)\" onchange=\"switchWebSnap(this.checked)\"> <label for=\"websnapper\">Reduce to \"safe\" colors (WebColors)</label>
		</div>
		
		<div id=\"presetswitch\">
			Variations:
			<a href=\"\" id=\"preset-default\" class=\"btn\" onclick=\"switchPreset('default');return false\">Default</a>
			<a href=\"\" id=\"preset-pastel\" class=\"btn\" onclick=\"switchPreset('pastel');return false\">Pastel</a>
			<a href=\"\" id=\"preset-soft\" class=\"btn\" onclick=\"switchPreset('soft');return false\">Dark pastel</a>
			<a href=\"\" id=\"preset-light\" class=\"btn\" onclick=\"switchPreset('light');return false\">Light pastel</a>
			<a href=\"\" id=\"preset-hard\" class=\"btn\" onclick=\"switchPreset('hard');return false\">Contrast</a>
			<a href=\"\" id=\"preset-pale\" class=\"btn\" onclick=\"switchPreset('pale');return false\">Pale</a>
		</div>
		
		<a id=\"url\" href=\"index.html\">URL of this scheme</a>
		
		<div style='position:absolute; left:300px; top:385px; height:100; width: 450px; text-align: right;'>
		
		
			<select id=\"cbmodeswitcher\" onchange=\"switchBlindlessMode()\">
				<option value=\"\">Normal vision (cca 85,5 % of population)</option>
				<option value=\"1\">Protanopy (1 % of men)</option>
				<option value=\"2\">Deuteranopy (1 % of men)</option>
				<option value=\"3\">Tritanopy (cca 0,003 % of population)</option>
				<option value=\"4\">Protanomaly (1 % of men)</option>
				<option value=\"5\">Deuteranomaly (5 % of men, 0.4 % of women)</option>
				<option value=\"6\">Tritanomaly (almost 0 %)</option>
				<option value=\"7\">Full colorblindness (0,005% of population)</option>
				<option value=\"8\">Atypical monochromatism</option>
			</select>
		
		</div>
		<div id=\"colsamplevarsswitch\"><div class=\"bottomaligner\">
			<a href=\"\" class=\"btn\" id=\"cancelsamplevars\" onclick=\"drawSample();return false\">Back</a>
		</div></div>
		<div id=\"colsamplevars\"></div>
		
		<p id=\"info\">
			<a href=\"http://www.wellstyled.com\">Wellstyled.com</a> &rarr;
			<strong>Color schemes generator 2</strong> &bull;
			Please read <a id=\"helptext\" href=".$colorwheelurl."help.html>more info &amp; help</a><br>
			&bull; Copyright <a href=\"http://www.pixy.cz\">pixy</a> &copy; 2002, 2004
		</p></div>";
		
		$m.="<div id=\"display\">
		<input type='hidden' name='".$name."_colors' id='wizardColorWheelColors' value='$value' /></div>";
		
		$m.="<div id=\"settings\">
		<input type='hidden' name='".$name."_settings' id='settingsdata' value='$settings' /></div>";

		return $m;
	
	}
	
}

?>