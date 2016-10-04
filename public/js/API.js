import ServerActions from './actions/ServerActions';

let API = {
	fetchLinks(){
		console.log('1. IN API - fetchLinks');
		var req = new XMLHttpRequest();
        var parseReq = function(xhr) {
            var reqData;
            if (!xhr.responseType || xhr.responseType === "text") {
                reqData = xhr.responseText;
            } else if (xhr.responseType === "document") {
                reqData = xhr.responseXML;
            } else {
                reqData = xhr.response;
            }
            return reqData;
        };
        var makeReq = function() {
            req.open(
                'POST',
                'graphql?query={links{_id,title,url}}',
                true
            );
            req.send();
        };
        var reqListener = function() {
        	console.log("called reqListener");
        };
        req.timeout = 4000;
        req.addEventListener("load", reqListener);
        req.ontimeout = function() {
            console.log("Timed out");
        };
	    req.onreadystatechange = function() {
            if (req.readyState == 4 && req.status == 200) {
                var resp = JSON.parse(parseReq(req));
                console.log(resp);
                ServerActions.ReceiveLinks(resp.data.links);
            }
            if (req.readyState == 4 && req.status == 400) {
				console.log('Error');
            }
            if (req.readyState == 4 && req.status == 404) {
				console.log('Not found');
            }
	    };
	    makeReq();
	},

    buildIntrospectionSchema(){
        console.log('1. IN API - buildIntrospectionSchema');
        var req = new XMLHttpRequest();
        var makeReq = function() {
            req.open(
                'POST',
                'graphql_introspect',
                true
            );
            req.send();
        };
        makeReq();
    }

};

export default API;