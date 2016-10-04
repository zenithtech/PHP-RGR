import React from 'react';
import ReactDOM from 'react-dom';
import Relay from 'react-relay';
import GraphiQL from 'graphiql';
import fetch from 'isomorphic-fetch';
import Main from './components/Main';

Relay.injectNetworkLayer(
  new Relay.DefaultNetworkLayer('graphql', {
    fetchTimeout: 30000,   // Timeout after 30s.
    retryDelays: [5000],   // Only retry once after a 5s delay.
  })
);

if(document.getElementById('main')){

	class HomeRoute extends Relay.Route {
		static routeName = 'Home';
		static queries = {
			store: (Component) => Relay.QL`
				query MainQuery {
					store { ${Component.getFragment('store')} }
				}
			`
		}
	}

	ReactDOM.render(
		<Relay.RootContainer
			Component={Main}
			route={new HomeRoute()}
		/>,
	document.getElementById('main'));

}

if(document.getElementById('graphiql-container')){

	function graphQLFetcher(graphQLParams) {
	  return fetch('graphql', {
	    method: 'POST',
	    headers: { 'Content-Type': 'application/json' },
	    body: JSON.stringify(graphQLParams),
	  }).then(response =>
	  		response.json()
		);
	}

	ReactDOM.render(
		<GraphiQL
			fetcher={graphQLFetcher}
		/>,
	document.getElementById('graphiql-container'));

}
