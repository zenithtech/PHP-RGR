import Relay from 'react-relay';

// mutation CreateLinkMutation($input: CreateLinkInput!) {
//   createLink(input: $input) {
//     link {
//       id
//       title
//       url
//     }
//   }
// }

// {
//   "input": 
//   {
//     "clientMutationId": "1",
//     "url": "test.com",
//     "title": "sometitle"
//   }
// }

class CreateLinkMutation extends Relay.Mutation {
    getMutation() {
        return Relay.QL`
            mutation {
                createLink
            }
        `;
    }
    getVariables(){
        return {
            title: this.props.title,
            url: this.props.url
        }
    }
    getFatQuery(){
        return Relay.QL`
            fragment on CreateLinkPayload {
                linkEdge,
                store { linkConnection }
            }
        `;
    }
    getConfigs(){
        return [{
          type: 'RANGE_ADD',
          parentName: 'store',
          parentID: this.props.store.id,
          connectionName: 'linkConnection',
          edgeName: 'linkEdge',
          rangeBehaviors: {
            '': 'append'
          }
        }]
    }
}

export default CreateLinkMutation;