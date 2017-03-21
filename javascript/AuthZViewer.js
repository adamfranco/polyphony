/**
 * @since 11/29/06
 * @package polyphony.javascript
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AuthZViewer.js,v 1.3 2006/12/04 21:55:32 adamfranco Exp $
 */

AuthZViewer.prototype = new Panel();
AuthZViewer.prototype.constructor = AuthZViewer;
AuthZViewer.superclass = Panel.prototype;

/**
 * <##>
 * 
 * @since 11/29/06
 * @package polyphony.javascript
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AuthZViewer.js,v 1.3 2006/12/04 21:55:32 adamfranco Exp $
 */
function AuthZViewer ( qualifierId, positionElement ) {
	if ( arguments.length > 0 ) {
		this.init( qualifierId, positionElement );
	}
}

	/**
	 * Initialize this object
	 * 
	 * @param string qualifierId
	 * @return void
	 * @access public
	 * @since 11/29/06
	 */
	AuthZViewer.prototype.init = function ( qualifierId, positionElement ) {
		AuthZViewer.superclass.init.call(this, 
								"Authorizations",
								15,
								300,
								positionElement);
		
		this.contentElement.innerHTML = "<div class='loading'>loading...</div>";
		this.qualifierId = qualifierId;
		this.loadAZs();
	}
	
	/**
	 * Initialize and run the AuthZViewer
	 * 
	 * @param string qualifierId
	 * @param object positionElement
	 * @return void
	 * @access public
	 * @since 11/27/06
	 */
	AuthZViewer.run = function (qualifierId, positionElement ) {
		if (positionElement.panel) {
			positionElement.panel.open();
		} else {
			var tmp = new AuthZViewer(qualifierId, positionElement );
		}
	}
	
	/**
	 * Asynchronously load the AZs for this node
	 * 
	 * @return void
	 * @access public
	 * @since 11/29/06
	 */
	AuthZViewer.prototype.loadAZs = function () {
		// Get the new tags and re-call this method with the tags
		var req = Harmoni.createRequest();
		var url = Harmoni.quickUrl('authorization', 'getWhoCanDo', 
						{'qualifier_id': this.qualifierId, 'function_id': 'edu.middlebury.authorization.view'}, 
						'polyphony-authz');
// 		var newWindow = window.open(url);

		if (req) {
			// Define a variable to point at this object that will be in the
			// scope of the request-processing function, since 'this' will (at that
			// point) be that function.
			var authZViewer = this;

			req.onreadystatechange = function () {
				// only if req shows "loaded"
				if (req.readyState == 4) {
					// only if we get a good load should we continue.
					if (req.status == 200) {
// 						alert(req.responseText);
						authZViewer.writeAZs(req.responseXML);
					} else {
						alert("There was a problem retrieving the XML data:\n" +
							req.statusText);
					}
				}
			} 
			
			req.open("GET", url, true);
			req.send(null);
			
		} else {
			alert("Error: Unable to execute AJAX request. \nPlease upgrade your browser.");
		}	
	}
	
	/**
	 * Write an HTML display of the authzs based on the given XML document
	 * 
	 * @param xmldoc
	 * @return void
	 * @access public
	 * @since 11/29/06
	 */
	AuthZViewer.prototype.writeAZs = function (xmldoc) {
		var html = '';
		var errors = xmldoc.getElementsByTagName('error');
		if (errors.length) {
			for (var i = 0; i < errors.length; i++)
				html += "\n<strong>Error: </strong>" + errors[i].text + "<br/>";
			
			this.contentElement.innerHTML = html;
			return;
		}
		
		var agents = xmldoc.getElementsByTagName('agent');
		if (agents.length) {
			
			var everyoneString = '';
			var usersString = '';
			var groupStrings = new Array();
			var agentStrings = new Array();
			
			for (var i = 0; i < agents.length; i++) {
				html = "<tr>";
				html += "<td style='font-weight: bold; border-top: 1px dotted;'>"
				html += "<a href='#' onclick='AgentInfoPanel.run(\"" + agents[i].getAttribute('id') + "\", \"" + agents[i].getAttribute('displayName') + "\", this); return false;'>"
				html += agents[i].getAttribute('displayName')
				html += "</a>"
				html += "</td>";
				html += "<td style='border-top: 1px dotted;'>";
				var azs = agents[i].getElementsByTagName('authorization');
				var azStrings = new Array();
				for (var j = 0; j < azs.length; j++) {
					var azFunctions = azs[j].getElementsByTagName('function');
					var azFunction = azFunctions[0];
					
					var azFunctionTypes = azFunction.getElementsByTagName('type');
					var azFunctionType = azFunctionTypes[0];
					var azFunctionTypeDomains = azFunctionType.getElementsByTagName('domain');
					var azFunctionTypeDomain = azFunctionTypeDomains[0].firstChild.nodeValue;
					var azFunctionTypeKeywords = azFunctionType.getElementsByTagName('keyword');
					var azFunctionTypeKeyword = azFunctionTypeKeywords[0].firstChild.nodeValue;
					
					if (azFunctionTypeDomain == 'Authorization' 
						&& (azFunctionTypeKeyword == 'View/Use' || 
							azFunctionTypeKeyword == 'Editing')
						&& !azStrings.elementExists(azFunction.getAttribute('referenceName')))
					{
						azStrings.push(azFunction.getAttribute('referenceName'));
					}	
				}
				azStrings.sort();
				html += "<div>";
				html += azStrings.join("</div><div>");
				html += "</div>";
				
				html += "</td>";
				html += "</tr>";
				
				if (agents[i].getAttribute('id') == 'edu.middlebury.agents.everyone'
					|| agents[i].getAttribute('id') == 'edu.middlebury.agents.anonymous')
				{
					everyoneString = html;
				} else if (agents[i].getAttribute('id') == 'edu.middlebury.agents.users') {
					usersString = html;
				} else if (agents[i].getAttribute('agentOrGroup') == 'group') {
					groupStrings.push({'value': agents[i].getAttribute('displayName'), 'string': html});
				} else {
					agentStrings.push({'value': agents[i].getAttribute('displayName'), 'string': html});
				}
			}
			
			quick_sortValue(groupStrings);
			quick_sortValue(agentStrings);
			
			html = "<table border='0'>"
			html += everyoneString;
			html += usersString;
			for (var i = 0; i < groupStrings.length; i++)
				html += groupStrings[i].string;
			for (var i = 0; i < agentStrings.length; i++)
				html += agentStrings[i].string;
			html += "</table>";
		} else {
			html += "No one can view this item.";
		}
		
		this.contentElement.innerHTML = html;
	}
	