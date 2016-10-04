<?php
use GraphQL\GraphQL;
require(dirname((__DIR__)).'/graphql/MySchema.php');

// https://github.com/graphql/graphql-js/blob/v0.4.12/src/utilities/introspectionQuery.js#L11-L89
$IntrospectionQuery = '
query IntrospectionQuery {
  __schema {
    queryType {
      name
    }
    mutationType {
      name
    }
    subscriptionType {
      name
    }
    types {
      ...FullType
    }
    directives {
      name
      description
      args {
        ...InputValue
      }
      onOperation
      onFragment
      onField
    }
  }
}

fragment FullType on __Type {
  kind
  name
  description
  fields(includeDeprecated: true) {
    name
    description
    args {
      ...InputValue
    }
    type {
      ...TypeRef
    }
    isDeprecated
    deprecationReason
  }
  inputFields {
    ...InputValue
  }
  interfaces {
    ...TypeRef
  }
  enumValues(includeDeprecated: true) {
    name
    description
    isDeprecated
    deprecationReason
  }
  possibleTypes {
    ...TypeRef
  }
}

fragment InputValue on __InputValue {
  name
  description
  type {
    ...TypeRef
  }
  defaultValue
}

fragment TypeRef on __Type {
  kind
  name
  ofType {
    kind
    name
    ofType {
      kind
      name
      ofType {
        kind
        name
      }
    }
  }
}
';
try {
    $schema = StarWarsSchema::build();

    $introspectionBuild = GraphQL::execute(
        $schema,
        $IntrospectionQuery
    );

    file_put_contents(
        '../public/js/data/schema.json',
        json_encode($introspectionBuild)
    );
    echo 'Schema built in <a href="js/data/schema.json" target="_self">js/data/schema.json</a>';

} catch (Exception $exception) {
    $result = [
        'errors' => [
            ['message' => $exception->getMessage()]
        ]
    ];
    echo 'There was an error: ' . $result;
}

die();