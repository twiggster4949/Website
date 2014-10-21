define(["./page-data", "ga"], function(PageData, ga) {
	
	var enabled = PageData.get("gaEnabled");
	// myGa gets set to the google analytics function if it should be enabled, otherwise just a stub
	var myGa = enabled ? ga : function(){};
	
	if (enabled) {
		myGa('create', 'UA-43879336-5', 'auto');
		myGa('send', 'pageview');
		
		function sendHeartbeat() {
			myGa('send', 'event', 'Heartbeat', 'Beat', {'nonInteraction': 1});
			setTimeout(sendHeartbeat, 5*60*1000);
		}
		sendHeartbeat();
	}

	
	return {
		registerModulesLoadTime: function(site, timeTaken) {
			myGa('send', 'timing', site, 'RequireJS modules load time.', timeTaken);
		}
	};
	
});