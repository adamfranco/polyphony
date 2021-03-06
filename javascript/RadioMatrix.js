/**
 * @since 11/2/07
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: RadioMatrix.js,v 1.5 2008/03/20 13:09:30 adamfranco Exp $
 */

/**
* The RadioMatrix is an inteface element that presents the user with 
 * a matrix of RadioButtons that allow choosing from a list of options for each
 * of a number of fields.
 *
 * This class provides a Javascript Implementation of the RadioMatrix to do client
 * side validation in order to give users direct feedback over the interactions
 * between fields. Final validation is done server-side.
 * 
 * @since 11/2/07
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: RadioMatrix.js,v 1.5 2008/03/20 13:09:30 adamfranco Exp $
 */
function RadioMatrix ( options, fields ) {
	if ( arguments.length > 0 ) {
		this.init( options, fields );
	}
}

	/**
	 * Initialize the object
	 * 
	 * @param <##>
	 * @return <##>
	 * @access public
	 * @since 11/2/07
	 */
	RadioMatrix.prototype.init = function ( options, fields ) {
		this.options = options;
		this.fields = fields;
		
		this.validateState();
	}
	
	/**
	 * Answer the field index that matches the fieldname string given
	 * 
	 * @param string fieldname
	 * @return integer
	 * @access public
	 * @since 11/2/07
	 */
	RadioMatrix.prototype.getFieldIndexByName = function (fieldname) {
		for (var i = 0; i < this.fields.length; i++) {
			if (this.fields[i].fieldname == fieldname)
				return i;
		}
		
		throw ("Unknown fieldname '"+fieldname+"'.");
	}
	
	/**
	 * Set one of the fields to a given option
	 * 
	 * @param DOM_Element changedField
	 * @param event
	 * @return void
	 * @access public
	 * @since 11/2/07
	 */
	RadioMatrix.prototype.setField = function (changedField, event) {
		var initialState = this.getState();
		var fieldIndex = this.getFieldIndexByName(changedField.name);
		var optionIndex = changedField.value
		
		try {
			this.fields[fieldIndex].value = Number(optionIndex);
			
			// Apply the rules to previous fields
			for (var i = fieldIndex - 1; i >= 0; i--)
				this.applyRuleToAbove(this.fields[i + 1], this.fields[i]);
			
			// Apply the rules to previous fields
			for (i = fieldIndex + 1; i < this.fields.length; i++)
				this.applyRuleToBelow(this.fields[i], this.fields[i - 1]);
				
		} catch (e) {
			this.setState(initialState);
		}
		
		this.validateState();
		
		// Apply the new state to the form fields
		this.applyChangesToForm(changedField);
	}
	
	/**
	 * Apply changes to our internal field values to the form representation
	 * 
	 * @param DOM_Element changedField
	 * @return void
	 * @access public
	 * @since 11/15/07
	 */
	RadioMatrix.prototype.applyChangesToForm = function (changedField) {
		var form = changedField.form;
		for (var i = 0; i < this.fields.length; i++) {
			var fieldList = form[this.fields[i].fieldname];
			if (!fieldList) {
				// Continue to allow the case of non-displayed fields
				continue;
				throw ("RadioGroup '"+this.fields[i].fieldname+"' was not found in the form.");
			}
			if (this.fields[i].value < 0 || this.fields[i].value >= fieldList.length)
				throw ("Value " + this.fields[i].value + " is out of range for the radiogroup " + this.fields[i].fieldname);
				
			var field = fieldList[this.fields[i].value];
			field.checked = "checked";
		}
	}
	
	/**
	 * Validate the state of the fields. Throws an exception on failure.
	 *
	 * @return void
	 * @access public
	 * @since 11/2/07
	 */
	RadioMatrix.prototype.validateState = function () {
		for (i = 1; i < this.fields.length; i++) {
			if (!this.isRelationValid(this.fields[i], this.fields[i - 1]))
				throw ("Rule validation failed");
		}
	}
	
	/**
	 * Check a particular rule
	 * 
	 * @param object fieldBelow
	 * @param object fieldAbove
	 * @return boolean
	 * @access public
	 * @since 11/2/07
	 */
	RadioMatrix.prototype.isRelationValid = function (fieldBelow, fieldAbove) {
		switch (fieldBelow.rule) {
			case '<':
				return (fieldBelow.value < fieldAbove.value);
			case '<=':
				return (fieldBelow.value <= fieldAbove.value);
			case '==':
				return (fieldBelow.value == fieldAbove.value);
			case '>=':
				return (fieldBelow.value >= fieldAbove.value);
			case '>':
				return (fieldBelow.value > fieldAbove.value);
			case null:
				return true;
			default:
				throw ("Unknown rule, '" + fieldBelow.rule + "'.");
		}
	}
	
	/**
	 * Answer the current state
	 * 
	 * @return array
	 * @access public
	 * @since 11/2/07
	 */
	RadioMatrix.prototype.getState = function () {
		var state = new Array();
		for (var i = 0; i < this.fields.length; i++) {
			state.push(this.fields[i].value);
		}
		
		return state;
	}
	
	/**
	 * Set the current state
	 * 
	 * @param array
	 * @return void
	 * @access public
	 * @since 11/2/07
	 */
	RadioMatrix.prototype.setState = function (state) {
		// Check that the state has valid entries;
		for (var i = 0; i < this.fields.length; i++) {
			if (state[i] === null || state[i] < 0 || state[i] >= this.options.length)
				throw ("Invalid state " + state +".");
		}
		
		// Set the state
		for (i = 0; i < this.fields.length; i++) {
			this.fields[i].value = state[i];
		}
	}
	
	/**
	 * Apply a rule to a prior field
	 * 
	 * @param object fieldBelow
	 * @param object fieldAbove
	 * @return void
	 * @access public
	 * @since 11/2/07
	 */
	RadioMatrix.prototype.applyRuleToAbove = function (fieldBelow, fieldAbove) {
		if (!this.isRelationValid(fieldBelow, fieldAbove)) {
			switch (fieldBelow.rule) {
				case '<':
					if (fieldBelow.value >= (this.options.length - 1))
						throw ("Cannot set field "+fieldAbove.key+" to an option greater than "+(this.options.length - 1)+".");
						
					fieldAbove.value = fieldBelow.value + 1;
					return;
				case '<=':
				case '==':
				case '>=':
					fieldAbove.value = fieldBelow.value;
					return;
				case '>':
					if (fieldBelow.value <= 0)
						throw ("Cannot set field "+fieldAbove.key+" to an option less than 0.");
						
					fieldAbove.value = fieldBelow.value - 1;
					return;
				case null:
					return;
				default:
					throw ("Unknown rule, '"+fieldBelow.rule+"'.");
			}
		}
	}
	
	/**
	 * Apply a rule to a later field
	 * 
	 * @param object fieldBelow
	 * @param object fieldAbove
	 * @return void
	 * @access public
	 * @since 11/2/07
	 */
	RadioMatrix.prototype.applyRuleToBelow = function (fieldBelow, fieldAbove) {
		if (!this.isRelationValid(fieldBelow, fieldAbove)) {
			switch (fieldBelow.rule) {
				case '<':
					if (fieldAbove.value <= 0)
						throw ("Cannot set field "+fieldBelow.key+" to an option less than 0.");
					
						
					fieldBelow.value = fieldAbove.value - 1;
					return;
				case '<=':
				case '==':
				case '>=':
					fieldBelow.value = fieldAbove.value;
					return;
				case '>':
					if (fieldAbove.value >= (this.options.length - 1))
						throw ("Cannot set field "+fieldBelow.key+" to an option greater than "+(this.options.length - 1)+".");
						
					fieldBelow.value = fieldAbove.value + 1;
					return;
				case null:
					return;
				default:
					throw ("Unknown rule, '"+fieldBelow.rule+"'.");
			}
		}
	}
	
	/**
	 * Open a description window
	 * 
	 * @param DOM_Element link
	 * @param DOM_Element descArea
	 * @return void
	 * @access public
	 * @static
	 * @since 11/5/07
	 */
	RadioMatrix.openDescriptionWindow = function (link, descArea) {
		var descWindow = window.open('', 'RadioMatrixDescription', 'width=300,height=200,resizable=yes');
		descWindow.document.write("<h2>"+link.innerHTML+"<"+"/h2>\n<p>"+descArea.value+"<"+"/p>");
		descWindow.document.close();
		descWindow.focus();
	}


/**
 * @since 11/15/07
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: RadioMatrix.js,v 1.5 2008/03/20 13:09:30 adamfranco Exp $
 */

HierarchicalRadioMatrix.prototype = new RadioMatrix();
HierarchicalRadioMatrix.prototype.constructor = HierarchicalRadioMatrix;
HierarchicalRadioMatrix.superclass = RadioMatrix.prototype;

/**
 * <##>
 * 
 * @since 11/15/07
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: RadioMatrix.js,v 1.5 2008/03/20 13:09:30 adamfranco Exp $
 */
function HierarchicalRadioMatrix ( options, fields ) {
	if ( arguments.length > 0 ) {
		this.init( options, fields );
	}
}

	/**
	 * Validate the state of the fields. Throws an exception on failure.
	 *
	 * @return void
	 * @access public
	 * @since 11/2/07
	 */
	HierarchicalRadioMatrix.prototype.validateState = function () {
		for (var i = 0; i < this.fields.length; i++) {
			var field = this.fields[i];
			
			for (var j = 0; j < field.childFieldnames.length; j++) {
				var child = this.fields[this.getFieldIndexByName(field.childFieldnames[j])];
				if (!this.isRelationValid(child, field))
					throw ("Rule validation failed");
			}
		}
	}	
	
	/**
	 * Set one of the fields to a given option
	 * 
	 * @param DOM_Element changedField
	 * @param event
	 * @return void
	 * @access public
	 * @since 11/2/07
	 */
	HierarchicalRadioMatrix.prototype.setField = function (changedField, event) {
		var initialState = this.getState();
		var fieldIndex = this.getFieldIndexByName(changedField.name);
		var optionIndex = changedField.value
		
		
		try {
			this.fields[fieldIndex].value = Number(optionIndex);
			
			// if shift was held down, then set all child fields to the same option.
			if (event.shiftKey) {
				this.setFieldsDown(this.fields[fieldIndex], Number(optionIndex));
			}
			
			// Apply the rules to previous fields
			this.applyRulesUp(this.fields[fieldIndex]);
			
			// Apply the rules to child fields
			this.applyRulesDown(this.fields[fieldIndex]);
				
		} catch (e) {
			alert(e.message);
			this.setState(initialState);
		}
		
		this.validateState();
		
		this.applyChangesToForm(changedField);
	}
	
	/**
	 * Set the value of decendent fields to the same as the field passed
	 * 
	 * @param object field
	 * @param integer optionIndex
	 * @return void
	 * @access public
	 * @since 11/15/07
	 */
	HierarchicalRadioMatrix.prototype.setFieldsDown = function (field, optionIndex) {
		for (var j = 0; j < field.childFieldnames.length; j++) {
			var child = this.fields[this.getFieldIndexByName(field.childFieldnames[j])];
			for (var i = optionIndex; i < child.value; i++ ) {
				if (!child.disabledOptions[i]) {
					child.value = i;
				}
			}
			this.setFieldsDown(child, optionIndex);
		}
	}
	
	/**
	 * Apply Rules to parent fields
	 * 
	 * @param object field
	 * @return void
	 * @access public
	 * @since 11/15/07
	 */
	HierarchicalRadioMatrix.prototype.applyRulesUp = function (field) {
		if (!field.parentFieldname)
			return;
		
		var parent = this.fields[this.getFieldIndexByName(field.parentFieldname)];
		this.applyRuleToAbove(field, parent);
		this.applyRulesUp(parent);
		this.applyRulesDown(parent);
	}
	
	/**
	 * Apply Rules to parent fields
	 * 
	 * @param object field
	 * @return void
	 * @access public
	 * @since 11/15/07
	 */
	HierarchicalRadioMatrix.prototype.applyRulesDown = function (field) {
		for (var j = 0; j < field.childFieldnames.length; j++) {
			var child = this.fields[this.getFieldIndexByName(field.childFieldnames[j])];
			
			this.applyRuleToBelow(child, field);
			this.applyRulesDown(child);
		}
	}

