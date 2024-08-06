<?php

namespace App\Services;

use GuzzleHttp\Client;

class ShopifyService
{
    private $client;

    /**
     * Constructor to initialize the Shopify API client.
     */
    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://' . $_ENV['SHOPIFY_STORE_DOMAIN'] . '/',
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Shopify-Access-Token' => $_ENV['SHOPIFY_ACCESS_TOKEN']
            ]
        ]);
    }

    /**
     * Extracts prefix from a given product handle.
     *
     * @param string $handle The product handle.
     * @return string Returns the extracted prefix.
     */
    private function extractPrefixFromHandle($handle)
    {
        $parts = explode('-', $handle);
        return $parts[0];
    }

    /**
     * Retrieves a product by its handle.
     *
     * @param string $handle The handle of the product to retrieve.
     * @return array|null Returns the product array if found, null otherwise.
     */
    public function getProductByHandle($handle)
    {
        //$response = $this->client->request('GET', "admin/api/{$_ENV['SHOPIFY_API_VERSION']}/products.json?handle={$handle}&fields=id,title,handle,vendor,price");
        $response = $this->client->request('GET', "admin/api/{$_ENV['SHOPIFY_API_VERSION']}/products.json?handle={$handle}");
        $productData = json_decode($response->getBody()->getContents(), true);

        if (!empty($productData['products'])) {
            $product = $productData['products'][0];
            $product['metafields'] = $this->getMetafieldsForProduct($product['id']);
            return $product;
        }

        return null;
    }

    /**
     * Fetches the ID of a collection by its title.
     *
     * @param string $title The title of the collection.
     * @return int|null Returns the ID of the collection if found, null otherwise.
     * @throws \Exception Throws exception if the API call fails.
     */
    public function getSmartCollectionIdByTitle($title)
    {
        try {
            $url = "admin/api/{$_ENV['SHOPIFY_API_VERSION']}/smart_collections.json?title=" . urlencode($title);
            // Log the URL being requested
            file_put_contents(SHOPIFY_LOG_FILE, "getSmartCollectionIdByTitle: Requesting URL https://" . $_ENV['SHOPIFY_STORE_DOMAIN'] . "/{$url}\n", FILE_APPEND);

            $response = $this->client->request('GET', $url);
            $collectionsData = json_decode($response->getBody()->getContents(), true);

            if (!empty($collectionsData['smart_collections'])) {
                foreach ($collectionsData['smart_collections'] as $collection) {
                    if (strcasecmp($collection['title'], $title) == 0) {
                        // Found matching collection, logging for success
                        //file_put_contents(SHOPIFY_LOG_FILE, "getSmartCollectionIdByTitle: Found smart collection '{$title}' with ID {$collection['id']}\n", FILE_APPEND);
                        return $collection['id']; // Found matching collection
                    }
                }

                // No matching collection found, logging for information
                //file_put_contents(SHOPIFY_LOG_FILE, "getSmartCollectionIdByTitle: No matching smart collection found for '{$title}'\n", FILE_APPEND);
            } else {
                // No smart collections data found, logging for information
                //file_put_contents(SHOPIFY_LOG_FILE, "getSmartCollectionIdByTitle: No smart collections data found for '{$title}'. Response: " . print_r($collectionsData, true) . "\n", FILE_APPEND);
            }

            return null; // No matching collection found
        } catch (\Exception $e) {
            // Exception handling, logging for errors
            //file_put_contents(SHOPIFY_LOG_FILE, "getSmartCollectionIdByTitle: Error fetching smart collection by title '{$title}' with URL https://" . $_ENV['SHOPIFY_STORE_DOMAIN'] . "/{$url}: " . $e->getMessage() . "\n", FILE_APPEND);
            return null;
        }
    }

    /**
     * Retrieves metafields for a given product ID.
     *
     * @param int $productId The ID of the product.
     * @return array Returns an array of metafields for the product.
     */
    public function getMetafieldsForProduct($productId)
    {
        $response = $this->client->request('GET', "admin/api/{$_ENV['SHOPIFY_API_VERSION']}/products/{$productId}/metafields.json");
        $metafieldsData = json_decode($response->getBody()->getContents(), true);
        return $metafieldsData['metafields'] ?? [];
    }

    /**
     * Fetches product and its variants by prefix.
     *
     * @param string $handle The handle of the main product.
     * @return array|null Returns an array with main product and variant products if found, null otherwise.
     */
    public function getProductAndVariantByPrefix($handleWithPageInfo)
    {
        // Initial split to separate handle from potential pageInfo
        $parts = explode('?page_info=', $handleWithPageInfo, 2);
        $handle = $parts[0];
        $pageInfo = isset($parts[1]) ? $parts[1] : null;

        // Log the extracted handle and pageInfo for debugging
        //file_put_contents(SHOPIFY_LOG_FILE, "Extracted handle: {$handle}, PageInfo: {$pageInfo}\n", FILE_APPEND);

        try {
            $product = $this->getProductByHandle($handle);
            if (!$product) {
                file_put_contents(SHOPIFY_LOG_FILE, "getProductAndVariantByPrefix: No product found for handle {$handle}\n", FILE_APPEND);
                return ['error' => 'Product not found'];
            }

            $prefix = $this->extractPrefixFromHandle($handle);
            $collectionId = $this->getSmartCollectionIdByTitle($prefix);
            if (!$collectionId) {
                file_put_contents(SHOPIFY_LOG_FILE, "getProductAndVariantByPrefix: No collection found for prefix {$prefix}\n", FILE_APPEND);
                return ['error' => 'Collection not found'];
            }

            $variantProducts = $this->getProductsByPrefixInCollection($collectionId, $pageInfo);

            return [
                'mainProduct' => $product,
                'variantProducts' => $variantProducts
            ];
        } catch (\Exception $e) {
            file_put_contents(SHOPIFY_LOG_FILE, "getProductAndVariantByPrefix: Exception encountered: " . $e->getMessage() . "\n", FILE_APPEND);
            return ['error' => 'An error occurred fetching product details'];
        }
    }


    /**
     * Fetches products by prefix within a specified collection with pagination and sorting.
     *
     * @param int $collectionId The ID of the collection to filter products.
     * @param string|null $pageInfo Pagination identifier for fetching subsequent pages.
     * @param int $perPage The number of products per page.
     * @param string $sortOrder The order in which to sort the products.
     * @return array Returns an array with 'products' and 'nextPageInfo' for pagination.
     * @throws \Exception Throws exception if the API call fails.
     */
    public function getProductsByPrefixInCollection($collectionId, $pageInfo = null, $perPage = 39, $sortOrder = 'manual')
    {
        try {
            $baseURL = "admin/api/{$_ENV['SHOPIFY_API_VERSION']}/products.json";
            $queryParams = [
                'limit' => $perPage,
                'sort_order' => $sortOrder, // Add the sort_order parameter
            ];

            // Only add collection_id if pageInfo is not provided
            if (empty($pageInfo)) {
                $queryParams['collection_id'] = $collectionId;
            } else {
                // Ensure pageInfo is properly encoded to avoid URL issues
                $queryParams['page_info'] = urlencode($pageInfo);
            }

            // Build the final URL
            $url = $baseURL . '?' . http_build_query($queryParams);

            // Log the constructed URL for debugging
            //file_put_contents(SHOPIFY_LOG_FILE, "Constructed URL for request: {$url}\n", FILE_APPEND);

            $response = $this->client->request('GET', $url);
            $productsData = json_decode($response->getBody()->getContents(), true);

            $nextPageInfo = null;
            if ($response->hasHeader('Link')) {
                $linkHeader = $response->getHeader('Link')[0];
                preg_match('/<.*?page_info=([\w\-]+)[^>]*>; rel="next"/', $linkHeader, $matches);
                if (isset($matches[1])) {
                    $nextPageInfo = $matches[1];
                    // Additional logging for nextPageInfo
                    // file_put_contents(SHOPIFY_LOG_FILE, "Extracted nextPageInfo: {$nextPageInfo}\n", FILE_APPEND);
                }
            }

            $filteredProducts = array_map(function ($product) {
                $product['metafields'] = $this->getMetafieldsForProduct($product['id']);
                return $product;
            }, $productsData['products']);

            return [
                'products' => $filteredProducts,
                'nextPageInfo' => $nextPageInfo,
            ];
        } catch (\Exception $e) {
            // file_put_contents(SHOPIFY_LOG_FILE, "Error fetching products: " . $e->getMessage() . "\n", FILE_APPEND);
            return ['products' => [], 'nextPageInfo' => null];
        }
    }

    /**
     * Fetches bulk products from Shopify based on a given collection ID.
     * This function supports pagination through Shopify's REST API and allows sorting.
     *
     * @param int $collectionId The ID of the Shopify collection from which to fetch products.
     * @param string|null $pageInfo Pagination token for fetching subsequent pages (optional).
     * @param int $perPage The number of products to fetch per page (default is 250).
     * @param string $sortOrder The order in which to sort the products (default is 'manual').
     * @return array Returns an array containing 'products' and potentially a 'nextPageInfo' token for pagination.
     *               In case of failure, an empty array of 'products' and 'nextPageInfo' is returned.
     */
    public function getBulkProducts($collectionId, $pageInfo = null, $perPage = 250, $sortOrder = 'manual')
    {
        //file_put_contents(SHOPIFY_LOG_FILE, "Extracted handle bulk products: {$collectionId}\n", FILE_APPEND);
        try {
            $baseURL = "admin/api/{$_ENV['SHOPIFY_API_VERSION']}/products.json";
            $queryParams = [
                'limit' => $perPage,
                'sort_order' => $sortOrder, // Add the sort_order parameter
            ];

            // Only add collection_id if pageInfo is not provided
            if (empty($pageInfo)) {
                $queryParams['collection_id'] = $collectionId;
            } else {
                // Ensure pageInfo is properly encoded to avoid URL issues
                $queryParams['page_info'] = urlencode($pageInfo);
            }

            // Build the final URL
            $url = $baseURL . '?' . http_build_query($queryParams);

            // Log the constructed URL for debugging
            //file_put_contents(SHOPIFY_LOG_FILE, "Constructed URL for request bulk : {$url}\n", FILE_APPEND);

            $response = $this->client->request('GET', $url);
            $productsData = json_decode($response->getBody()->getContents(), true);

            $nextPageInfo = null;
            if ($response->hasHeader('Link')) {
                $linkHeader = $response->getHeader('Link')[0];
                preg_match('/<.*?page_info=([\w\-]+)[^>]*>; rel="next"/', $linkHeader, $matches);
                if (isset($matches[1])) {
                    $nextPageInfo = $matches[1];
                    // Additional logging for nextPageInfo
                    // file_put_contents(SHOPIFY_LOG_FILE, "Extracted nextPageInfo: {$nextPageInfo}\n", FILE_APPEND);
                }
            }

            $filteredProducts = array_map(function ($product) {
                $product['metafields'] = $this->getMetafieldsForProduct($product['id']);
                return $product;
            }, $productsData['products']);

            return [
                'products' => $filteredProducts,
                'nextPageInfo' => $nextPageInfo,
            ];
        } catch (\Exception $e) {
            //file_put_contents(SHOPIFY_LOG_FILE, "Error fetching products: " . $e->getMessage() . "\n", FILE_APPEND);
            return ['products' => [], 'nextPageInfo' => null];
        }
    }
}
