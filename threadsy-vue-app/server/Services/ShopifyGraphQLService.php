<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class ShopifyGraphQLService
{
    private $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://' . $_ENV['SHOPIFY_STORE_DOMAIN'] . '/admin/api/' . $_ENV['SHOPIFY_API_VERSION'] . '/graphql.json',
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Shopify-Access-Token' => $_ENV['SHOPIFY_ACCESS_TOKEN']
            ]
        ]);
    }

    public function log($message)
    {
        file_put_contents(SHOPIFY_LOG_FILE, $message . "\n", FILE_APPEND);
     }

    public function graphqlQuery($query, $variables = [])
    {
       // $this->log("GraphQL Query:\n" . $query);
        //$this->log("Variables:\n" . print_r($variables, true));

        try {
            $response = $this->client->post('', [
                'json' => ['query' => $query, 'variables' => $variables]
            ]);
            $result = json_decode($response->getBody()->getContents(), true);
            //$this->log("GraphQL Query Result:\n" . print_r($result, true));
            return $result;
        } catch (GuzzleException $e) {
            //$this->log("GraphQL Query Error: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    private function extractPrefixFromHandle($handle)
    {
        $parts = explode('-', $handle);
        return $parts[0];
    }

    public function getProductByHandle($handle)
    {
        $query = <<<GRAPHQL
        query getProductByHandle(\$handle: String!) {
            productByHandle(handle: \$handle) {
                id
                title
                handle
                vendor
                description
                displayProductTitle: metafield(namespace: "custom", key: "displayproducttitle") {
                    key
                    value
                    id
                }
                productStyle: metafield(namespace: "custom", key: "productstyle") {
                    key
                    value
                    id
                }
                tierLevel: metafield(namespace: "custom", key: "tier_level") {
                    key
                    value
                    id
                }
                reviewsAverage: metafield(namespace: "yotpo", key: "reviews_average") {
                    key
                    value
                    id
                }
                reviewsCount: metafield(namespace: "yotpo", key: "reviews_count") {
                    key
                    value
                    id
                }
                variants(first: 1) {
                    edges {
                        node {
                            id
                            title
                            price
                            inventoryQuantity
                            productColor: metafield(namespace: "custom", key: "productcolor") {
                                key
                                value
                                id
                            }
                            productStyle: metafield(namespace: "custom", key: "productstyle") {
                                key
                                value
                                id
                            }
                            swatchColorCodes: metafield(namespace: "custom", key: "swatchcolorcodes") {
                                key
                                value
                                id
                            }
                            tierLevel: metafield(namespace: "custom", key: "tier_level") {
                                key
                                value
                                id
                            }
                            reviewsAverage: metafield(namespace: "yotpo", key: "reviews_average") {
                                key
                                value
                                id
                            }
                            reviewsCount: metafield(namespace: "yotpo", key: "reviews_count") {
                                key
                                value
                                id
                            }
                        }
                    }
                }
            }
        }
        GRAPHQL;

        $variables = ['handle' => $handle];
        return $this->graphqlQuery($query, $variables);
    }

    public function getSmartCollectionIdByTitle($title)
    {
        $query = <<<GRAPHQL
        query getCollectionByTitle(\$title: String!) {
            collections(first: 1, query: \$title) {
                edges {
                    node {
                        id
                        title
                    }
                }
            }
        }
        GRAPHQL;

        $variables = ['title' => $title];
        $result = $this->graphqlQuery($query, $variables);

        $this->log("GraphQL response for collection ID fetch: " . print_r($result, true));

        if (isset($result['data']['collections']['edges'][0]['node']['id'])) {
            return $result['data']['collections']['edges'][0]['node']['id'];
        }

        return null;
    }

    public function getProductsByPrefixInCollection($collectionId, $pageInfo = null, $perPage = 40)
    {
        $query = <<<GRAPHQL
        query getProductsByCollection(\$collectionId: ID!, \$first: Int!, \$after: String) {
            collection(id: \$collectionId) {
                products(first: \$first, after: \$after) {
                    edges {
                        node {
                            id
                            title
                            handle
                            vendor
                            images(first: 3) {
                                edges {
                                    node {
                                        id
                                        originalSrc
                                    }
                                }
                            }
                            variants(first: 10) {
                                edges {
                                    node {
                                        id
                                        title
                                        price
                                        inventoryQuantity
                                    }
                                }
                            }
                            productcolor: metafield(namespace: "custom", key: "productcolor") {
                                key
                                value
                                id
                            }
                            productstyle: metafield(namespace: "custom", key: "productstyle") {
                                key
                                value
                                id
                            }
                            swatchcolorcodes: metafield(namespace: "custom", key: "swatchcolorcodes") {
                                key
                                value
                                id
                            }
                            tierLevel: metafield(namespace: "custom", key: "tier_level") {
                                key
                                value
                                id
                            }
                            reviews_average: metafield(namespace: "yotpo", key: "reviews_average") {
                                key
                                value
                                id
                            }
                            reviews_count: metafield(namespace: "yotpo", key: "reviews_count") {
                                key
                                value
                                id
                            }
                        }
                    }
                    pageInfo {
                        hasNextPage
                        endCursor
                    }
                }
            }
        }
        GRAPHQL;
    
        $variables = [
            'collectionId' => $collectionId,
            'first' => $perPage,
            'after' => $pageInfo
        ];
    
        return $this->graphqlQuery($query, $variables);
    }
    
    

    public function getBulkProductsPaginated($collectionIdentifier, $pageInfo = null, $perPage = 10)
{
    $collectionId = $this->getSmartCollectionIdByTitle($collectionIdentifier);
    if (!$collectionId) {
        return ['error' => 'Collection not found'];
    }

    $query = <<<GRAPHQL
    query getBulkProductsByCollection(\$collectionId: ID!, \$first: Int!, \$after: String) {
        collection(id: \$collectionId) {
            products(first: \$first, after: \$after) {
                edges {
                    node {
                        id
                        title
                        handle
                        vendor
                        images(first: 3) {
                            edges {
                                node {
                                    id
                                    originalSrc
                                }
                            }
                        }
                        variants(first: 10) {
                            edges {
                                node {
                                    id
                                    title
                                    price
                                    inventoryQuantity
                                }
                            }
                        }
                        productcolor: metafield(namespace: "custom", key: "productcolor") {
                            key
                            value
                            id
                        }
                        productstyle: metafield(namespace: "custom", key: "productstyle") {
                            key
                            value
                            id
                        }
                        swatchcolorcodes: metafield(namespace: "custom", key: "swatchcolorcodes") {
                            key
                            value
                            id
                        }
                        tierLevel: metafield(namespace: "custom", key: "tier_level") {
                            key
                            value
                            id
                        }
                        reviews_average: metafield(namespace: "yotpo", key: "reviews_average") {
                            key
                            value
                            id
                        }
                        reviews_count: metafield(namespace: "yotpo", key: "reviews_count") {
                            key
                            value
                            id
                        }
                    }
                }
                pageInfo {
                    hasNextPage
                    endCursor
                }
            }
        }
    }
    GRAPHQL;

    $this->log("Fetching bulk products with Collection ID: $collectionId, Page Info: $pageInfo");

    $variables = [
        'collectionId' => $collectionId,
        'first' => $perPage,
        'after' => $pageInfo
    ];

    $response = $this->graphqlQuery($query, $variables);

    $pageInfoLog = isset($response['data']['collection']['products']['pageInfo']) ?
        print_r($response['data']['collection']['products']['pageInfo'], true) : 'No pageInfo found';

    $this->log("GraphQL Response Page Info:\n" . $pageInfoLog);

    return $response;
}

    

    public function getProductAndVariant($handleWithPageInfo)
    {
        $parts = explode('?page_info=', $handleWithPageInfo, 2);
        $handle = $parts[0];
        $pageInfo = isset($parts[1]) ? $parts[1] : null;

        //$this->log("Getting product and variant for handle: $handle with pageInfo: $pageInfo");

        $productResult = $this->getProductByHandle($handle);

        if (isset($productResult['data']['productByHandle'])) {
            $product = $productResult['data']['productByHandle'];
            $prefix = $this->extractPrefixFromHandle($handle);
            $collectionId = $this->getSmartCollectionIdByTitle($prefix);

            if ($collectionId) {
                $variantProductsResult = $this->getProductsByPrefixInCollection($collectionId, $pageInfo);

                $result = [
                    'mainProduct' => [
                        'id' => $product['id'],
                        'title' => $product['title'],
                        'handle' => $product['handle'],
                        'vendor' => $product['vendor'],
                        'description' => $product['description'],
                        'displayProductTitle' => $product['displayProductTitle']['value'] ?? null,
                        'productStyle' => $product['productStyle']['value'] ?? null,
                        'tierLevel' => $product['tierLevel']['value'] ?? null,
                        'reviewsAverage' => $product['reviewsAverage']['value'] ?? null,
                        'reviewsCount' => $product['reviewsCount']['value'] ?? null,
                        'variants' => isset($product['variants']['edges']) ? array_map(function ($variantEdge) {
                            $variant = $variantEdge['node'];
                            return [
                                'id' => $variant['id'],
                                'title' => $variant['title'],
                                'price' => $variant['price'],
                                'inventoryQuantity' => $variant['inventoryQuantity'],
                                'productColor' => $variant['productColor']['value'] ?? null,
                                'productStyle' => $variant['productStyle']['value'] ?? null,
                                'swatchColorCodes' => $variant['swatchColorCodes']['value'] ?? null,
                                'reviewsAverage' => $variant['reviewsAverage']['value'] ?? null,
                                'reviewsCount' => $variant['reviewsCount']['value'] ?? null,
                                'tierLevel' => $variant['tierLevel']['value'] ?? null,
                            ];
                        }, $product['variants']['edges']) : []
                    ],
                    'variantProducts' => isset($variantProductsResult['data']['collection']['products']['edges']) ? array_map(function ($productEdge) {
                        $product = $productEdge['node'];
                        return [
                            'id' => $product['id'],
                            'title' => $product['title'],
                            'handle' => $product['handle'],
                            'vendor' => $product['vendor'],
                            'images' => array_map(function ($imageEdge) {
                                return $imageEdge['node']['originalSrc'];
                            }, $product['images']['edges']),
                            'variants' => isset($product['variants']['edges']) ? array_map(function ($variantEdge) {
                                $variant = $variantEdge['node'];
                                return [
                                    'id' => $variant['id'],
                                    'title' => $variant['title'],
                                    'price' => $variant['price'],
                                    'inventoryQuantity' => $variant['inventoryQuantity'],
                                    'tierLevel' => $variant['tierLevel']['value'] ?? null,
                                ];
                            }, $product['variants']['edges']) : [],
                            'productColor' => $product['productcolor']['value'] ?? null,
                            'productStyle' => $product['productstyle']['value'] ?? null,
                            'swatchColorCodes' => $product['swatchcolorcodes']['value'] ?? null,
                            'reviewsAverage' => $product['reviews_average']['value'] ?? null,
                            'reviewsCount' => $product['reviews_count']['value'] ?? null,
                            'tierLevel' => $product['tierLevel']['value'] ?? null,
                        ];
                    }, $variantProductsResult['data']['collection']['products']['edges']) : [],
                    'nextPageInfo' => $variantProductsResult['data']['collection']['products']['pageInfo']['endCursor'] ?? null
                ];

                //$this->log("Processed Product and Variant Data:\n" . print_r($result, true));

                return $result;
            } else {
                return ['error' => 'Collection not found'];
            }
        }

       // $this->log("Product not found for handle: $handle");
        return ['error' => 'Product not found'];
    }

    public function getBestSellingProducts()
    {
        $query = <<<GRAPHQL
        query {
            products(first: 10, sortKey: BEST_SELLING) {
                edges {
                    node {
                        id
                        title
                        handle
                        vendor
                        images(first: 1) {
                            edges {
                                node {
                                    id
                                    originalSrc
                                }
                            }
                        }
                        variants(first: 1) {
                            edges {
                                node {
                                    id
                                    title
                                    price
                                }
                            }
                        }
                    }
                }
            }
        }
        GRAPHQL;

        return $this->graphqlQuery($query);
    }
}
