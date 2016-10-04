<?php

use GraphQL\Schema;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\FieldArgument;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Utils;

use GraphQLRelay\Connection\ArrayConnection;
use GraphQLRelay\Connection\Connection;
use GraphQLRelay\Mutation\Mutation;
use GraphQLRelay\Node\Node;
use GraphQLRelay\Relay;

class StarWarsSchema
{

    protected static $createLinkMutation;

    private static function readDB($filter, $options){

        // using cUrl
        // $curlURL = 'https://zenitht.com/pluralsight/BuildingData-drivenReactApplicationswithRelayGraphQLandFlux/rgrjs/public/data/links';
        // $ch = curl_init();
        // curl_setopt($ch, CURLOPT_URL, $curlURL);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        // curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        // $data = curl_exec($ch);
        // $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // curl_close($ch);
        // if ($httpcode>=200 && $httpcode<300) {
        //     return json_decode($data, true);
        // }

        // using MongoDB php driver
        $DB = new DB();

        if (!isset($filter)) {
            $filter = [];
        }
        if (!isset($options)) {
            $options = [];
        }

        $query = new MongoDB\Driver\Query($filter, $options);
        $exec = $DB->executeQuery('grgjs.links', $query);
        $execArr = iterator_to_array($exec);
        $bson = MongoDB\BSON\fromPHP($execArr);
        $json = MongoDB\BSON\toJSON($bson);
        $jsonArr = json_decode($json, true);
        return $jsonArr;
    }

    private static function writeDB($input){
        $bulk = new MongoDB\Driver\BulkWrite(['ordered' => true]);
        $bulk->insert(['title' => $input['title'], 'url' => $input['url']]);

        $DB = new DB();
        $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);
        $result = $DB->executeBulkWrite('grgjs.links', $bulk, $writeConcern);

        return $input;
    }

    public static function getFaction($id)
    {
        $data = self::readDB([], []);
        return $data['_id']['$id'];
    }

    public static function build()
    {
        /*
        $data = [
            ['counter' => 42],
            ['counter' => 43],
            ['counter' => 45]
        ];

        $counterType = new ObjectType([
            'name' => 'Counter',
            'fields' => [
                'counter' => [
                    'type' => Type::int()
                ]
            ]
        ]);

        $queryType = new ObjectType([
            'name' => 'Query',
            'fields' => [
                'data' => [
                    'type' => Type::listOf($counterType),
                    'resolve' => function () use (&$data){
                        return $data;
                    }
                ]
            ]
        ]);
        */


        $nodeDefinition = Relay::nodeDefinitions(
            // The ID fetcher definition
            function ($globalId) {
                $idComponents = Relay::fromGlobalId($globalId);
                if ($idComponents['type'] === $linkType){
                    return self::getFaction($idComponents['id']);
                } else {
                    return null;
                }
            },
            // Type resolver
            function ($object) {
                return $linkType;
            }
        );

        $linkType = new ObjectType([
            'name' => 'Link',
            'fields' => [
                'id' => [
                    'type' => Type::string(),
                    'resolve' => function($root, $args){
                        if (isset($root['clientMutationId'])){
                            return $root['clientMutationId'];
                        } else {
                            return $root['_id']['$oid'];
                        }
                    }
                ],
                'title' => [
                    'type' => Type::string()
                ],
                'url' => [
                    'type' => Type::string()
                ]
            ]
        ]);

        $linkConnection = Relay::connectionDefinitions([
            'name' => 'Link',
            'nodeType' => $linkType
        ]);

        $storeType = new ObjectType([
            'name' => 'Store',
            'fields' => function() use ($linkConnection, $linkType) {
                return [
                    'id' => Relay::globalIdField(),
                    'name' => [
                        'type' => Type::string()
                    ],
                    'linkConnection' => [
                        'type' => $linkConnection['connectionType'],
                        'args' => Relay::connectionArgs(),
                        'resolve' => function ($root, $args) {
                            $links = self::readDB([], ['limit'=> $args['first']]);
                            return Relay::connectionFromArray($links, $args);
                        }
                    ],
                    'links' => [
                        'type' => Type::listOf($linkType),
                        'resolve' => function () {
                            return self::readDB([], []);
                        }
                    ]
                ];
            }
        ]);

        $createLinkMutation = Relay::mutationWithClientMutationId([
            'name' => 'CreateLink',
            'inputFields' => [
                'title' => [
                    'type' => Type::nonNull(Type::string())
                ],
                'url' => [
                    'type' => Type::nonNull(Type::string())
                ]
            ],
            'mutateAndGetPayload' => function ($input) {
                $newLink = self::writeDB($input);
                return $newLink;
            },
            'outputFields' => [
                'link' => [
                    'type' => $linkType,
                    'resolve' => function ($payload) {
                        return $payload;
                    }
                ]
            ]
        ]);

        $mutationType = new ObjectType([
            'name' => 'Mutation',
            'fields' => [
                'createLink' => $createLinkMutation
            ]
        ]);

        $queryType = new ObjectType([
            'name' => 'Query',
            'fields' => function () use ($storeType, $nodeDefinition) {
                return [
                    'store' => [
                        'type' => $storeType,
                        'resolve' => function () {
                            return true;
                        }
                    ],
                    'node' => $nodeDefinition['nodeField']
                ];
            }
        ]);

        return new Schema($queryType, $mutationType);
    }
}
