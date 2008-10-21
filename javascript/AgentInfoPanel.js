/**
 * @since 10/21/08
 * @package polyphony.agent
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

AgentInfoPanel.prototype = new Panel();
AgentInfoPanel.prototype.constructor = AgentInfoPanel;
AgentInfoPanel.superclass = Panel.prototype;

/**
 * A panel for displaying information about an agent or group.
 * 
 * @since 10/21/08
 * @package polyphony.agent
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
function AgentInfoPanel ( agentId, agentDisplayName, positionElement ) {
	if ( arguments.length > 0 ) {
		this.init( agentId, agentDisplayName, positionElement );
	}
}

	/**
	 * Initialize the object
	 * 
	 * @param string agentId
	 * @param string agentDisplayName
	 * @param DOMElement positionElement
	 * @return void
	 * @access public
	 * @since 10/21/08
	 */
	AgentInfoPanel.prototype.init = function ( agentId, agentDisplayName, positionElement ) {
		AgentInfoPanel.superclass.init.call(this, 
								agentDisplayName,
								15,
								400,
								positionElement);
		
		this.contentElement.innerHTML = "<div class='loading'>loading...</div>";
		this.agentId = agentId;
		this.loadInfo();
	}

	/**
	 * Initialize and run the AgentInfoPanel
	 * 
	 * @param string agentId
	 * @param string agentDisplayName
	 * @param object positionElement
	 * @return void
	 * @access public
	 * @since 11/27/06
	 */
	AgentInfoPanel.run = function (agentId, agentDisplayName, positionElement ) {
		if (positionElement.panel) {
			positionElement.panel.open();
		} else {
			var tmp = new AgentInfoPanel(agentId, agentDisplayName, positionElement );
		}
	}
	
	/**
	 * Load information about the agent.
	 * 
	 * @return void
	 * @access public
	 * @since 10/21/08
	 */
	AgentInfoPanel.prototype.loadInfo = function () {
		// Get the new tags and re-call this method with the tags
		var req = Harmoni.createRequest();
		var url = Harmoni.quickUrl('agents', 'agent_info', {'agent_id': this.agentId});
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
						authZViewer.writeInfo(req.responseXML);
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
	 * Write out our agent information.
	 * 
	 * @param DOMDocument xmldoc
	 * @return void
	 * @access public
	 * @since 10/21/08
	 */
	AgentInfoPanel.prototype.writeInfo = function (xmldoc) {
		var html = '';
		var errors = xmldoc.getElementsByTagName('error');
		if (errors.length) {
			for (var i = 0; i < errors.length; i++)
				html += "\n<strong>Error: </strong>" + errors[i].text + "<br/>";
			
			this.contentElement.innerHTML = html;
			return;
		}
		
		var response = xmldoc.documentElement;
		var agents = response.getChildrenByTagName('group');
		if (agents.length) {
			var agent = agents[0];
		} else {
			var agents = response.getChildrenByTagName('agent');
			if (agents.length) {
				var agent = agents[0];
			} else {
				throw "No Agents or Groups listed in the info.";
			}
		}
		
		this.contentElement.innerHTML = '';
		var dl = this.contentElement.appendChild(document.createElement('dl'));
		
		var term = dl.appendChild(document.createElement('dt'));
		term.innerHTML = "Display Name:";
		var datum = dl.appendChild(document.createElement('dd'));
		datum.innerHTML = this.getAgentDisplayName(agent); 
		
		var term = dl.appendChild(document.createElement('dt'));
		term.innerHTML = "ID:";
		var datum = dl.appendChild(document.createElement('dd'));
		datum.innerHTML = agent.getAttribute('id');
		
		if (agent.nodeName == 'group') {
			var term = dl.appendChild(document.createElement('dt'));
			term.innerHTML = "Description:";
			var datum = dl.appendChild(document.createElement('dd'));
			var nameElements = agent.getChildrenByTagName('description');
			datum.innerHTML = nameElements[0].firstChild.nodeValue;
		
			var term = dl.appendChild(document.createElement('dt'));
			term.innerHTML = "Members:";
			var datum = dl.appendChild(document.createElement('dd'));
			
			var groupsElements = agent.getChildrenByTagName('groups');
			if (groupsElements[0].getElementsByTagName('notice').length) {
				datum.innerHTML = groupsElements[0].getElementsByTagName('notice').item(0).firstChild.nodeValue;
			} else {
				var groupElements = groupsElements[0].getChildrenByTagName('group');
				for (var i = 0; i < groupElements.length; i++) 
					this.addAgent(datum, groupElements[i]);
				if (groupElements.length)
					var div = datum.appendChild(document.createElement('div'));
			}
			
			
			
			var groupsElements = agent.getChildrenByTagName('members');
			if (groupsElements[0].getElementsByTagName('notice').length) {
				datum.innerHTML = groupsElements[0].getElementsByTagName('notice').item(0).firstChild.nodeValue;
			} else {
				var groupElements = groupsElements[0].getChildrenByTagName('agent');
				for (var i = 0; i < groupElements.length; i++) 
					this.addAgent(datum, groupElements[i]);
			}
		}
	}
	
	/**
	 * Answer the displayName of an agent
	 * 
	 * @param Element agent
	 * @return string
	 * @access public
	 * @since 10/21/08
	 */
	AgentInfoPanel.prototype.getAgentDisplayName = function (agent) {
		var nameElements = agent.getChildrenByTagName('displayName');
		return nameElements[0].firstChild.nodeValue;
	}
	
	/**
	 * Add an agent div to an element
	 * 
	 * @param DOMElement container
	 * @param Element agent
	 * @return void
	 * @access public
	 * @since 10/21/08
	 */
	AgentInfoPanel.prototype.addAgent = function (container, agent) {
		var div = container.appendChild(document.createElement('div'));
		var link = div.appendChild(document.createElement('a'));
		var displayName = this.getAgentDisplayName(agent);
		link.onclick = function () {
			AgentInfoPanel.run(agent.getAttribute('id'), displayName, link);
			return false;
		}
		link.innerHTML = displayName;
	}