<?php
use GraphQL\GraphQL;
require(dirname((__DIR__)).'/graphql/MySchema.php');

if (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] === 'application/json') {
    $rawBody = file_get_contents('php://input');
    $data = json_decode($rawBody ?: '', true);
} else {
    $data = $_POST;
}

$query = isset($data['query']) ? $data['query'] : null;
$operationName = isset($data['operationName']) ? $data['operationName'] : null;
$variables = isset($data['variables']) ? $data['variables'] : null;

try {
    $schema = StarWarsSchema::build();

    if($variables != []) {
        $result = GraphQL::execute(
            $schema,
            $query,
            null,
            json_decode($variables, true),
            json_decode($operationName)
        );
    } else {
        $result = GraphQL::execute(
            $schema,
            $query,
            json_decode($operationName)
        );
    }

} catch (Exception $exception) {
    $result = [
        'errors' => [
            ['message' => $exception->getMessage()]
        ]
    ];
}

header('Content-Type: application/json');
echo json_encode($result);
die();