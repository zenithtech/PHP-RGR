import React from 'react';
import Relay from 'react-relay';
import API from '../API';
import Link from './Link';
import CreateLinkMutation from '../mutation/CreateLinkMutation';

// import LinkStore from '../stores/LinkStore';
// let _getAppState = function() {
// 	return {
// 		links: LinkStore.getAll()
// 	};
// };

class Main extends React.Component{

	// state = _getAppState();

	// componentDidMount(){
	// 	API.fetchLinks();
	// 	LinkStore.on('change', this.onChange);
	// }

	// componentWillUnmount(){
	// 	LinkStore.removeListener('change', this.onChange);
	// }

	// onChange = ()=>{
	// 	console.log('4. In the view');
	// 	this.setState(_getAppState());
	// }

	setLimit = (e)=> {
		let newLimit = Number(e.target.value);
		newLimit < 1 ? newLimit = 1 : null; 
		this.props.relay.setVariables({
			limit: newLimit
		});
	}

	handleSubmit = (e) => {
		e.preventDefault();

		var onSuccess = () => {
			console.log('Mutation successful!');
		};

		var onFailure = (transaction) => {
			var error = transaction.getError() || new Error('Mutation failed.');
			console.error(error);
		};

		var mutation = new CreateLinkMutation({
			title: this.refs.newTitle.value,
			url: this.refs.newUrl.value,
			store: this.props.store
		});

		Relay.Store.commitUpdate(mutation, {onFailure, onSuccess});

		this.refs.newTitle.value = '';
		this.refs.newUrl.value = '';
	}

	render(){
		let content = this.props.store.linkConnection.edges.map(edge => {
			return <Link key={edge.node.id} link={edge.node} />;
		});
		return (
			<div>
				<h1>Links</h1>

				<form onSubmit={this.handleSubmit}>
					<input type="text" placeholder="Title" ref="newTitle" />
					<input type="text" placeholder="URL" ref="newUrl" />
					<button type="submit">Add link</button>
				</form>

				<select
					onChange={this.setLimit}
					defaultValue={this.props.relay.variables.limit}
				>
					<option value="1">1</option>
					<option value="5">5</option>
					<option value="10">10</option>
					<option value="50">50</option>
					<option value="100">100</option>
					<option value="1000">1000</option>
				</select>
				&nbsp;or&nbsp;
				<input
					placeholder="enter number"
					onChange={this.setLimit}
					type="number"
					min="1"
				/>
				<ul>
					{content}
				</ul>
				<br />
				<button onClick={API.buildIntrospectionSchema}>Build introspection schema</button>
				<p>Or open <a href="graphql_introspect" target="_blank">graphql_introspect</a> in the browser to build the schema.</p>
			</div>
		);
	}
}

Main = Relay.createContainer(Main, {
	initialVariables: {
		limit: 1000
	},
	fragments: {
		store: ()=> Relay.QL`
		fragment on Store {
			id,
			linkConnection(first:$limit) {
				edges {
					node {
						id,
						${Link.getFragment('link')}
					}
				}
			}
		}`
	}
});

export default Main;