import AppDispatcher from '../AppDispatcher';
import {ActionTypes} from '../Constants';

let ServerActions = {
	ReceiveLinks(links) {
		console.log('2. in ServerActions - ReceiveLinks');
		AppDispatcher.dispatch({
			actionType: ActionTypes.RECEIVE_LINKS,
			links
		});
	}
};

export default ServerActions;