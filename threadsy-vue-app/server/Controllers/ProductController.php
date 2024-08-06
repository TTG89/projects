<?php

namespace App\Controllers;

use App\Services\ShopifyService;

/**
 * Handles product-related requests, interfacing with Shopify's API through the ShopifyService.
 */
class ProductController {
    /**
     * @var ShopifyService Instance of the Shopify service for API calls.
     */
    protected $shopifyService;

    /**
     * ProductController constructor.
     * Initializes the ShopifyService instance.
     */
    public function __construct() {
        $this->shopifyService = new ShopifyService();
    }

    /**
     * Fetches a single product by its handle and returns its details as JSON.
     * 
     * @param string $handle The unique handle of the Shopify product.
     */
    public function getProduct($handle) {
        $product = $this->shopifyService->getProductByHandle($handle);
        header('Content-Type: application/json');
        echo json_encode($product);
    }

    /**
     * Fetches a product by its handle along with its variant products by prefix
     * and returns them as JSON. This is useful for products with multiple variants.
     * 
     * @param string $handle The unique handle of the Shopify product.
     */
    public function getProductAndVariant($handle) {
        $variantProducts = $this->shopifyService->getProductAndVariantByPrefix($handle);
        header('Content-Type: application/json');
        echo json_encode($variantProducts);
    }  

    public function getBulkOrderProductsByHandle($handle) {
        $logPrefix = "[getBulkOrderProductsByHandle]";
        file_put_contents(SHOPIFY_LOG_FILE, "{$logPrefix} Entered with handle: {$handle}\n", FILE_APPEND | LOCK_EX);
    
        try {
            $collectionId = $this->shopifyService->getSmartCollectionIdByTitle($handle);
            if (!$collectionId) {
                file_put_contents(SHOPIFY_LOG_FILE, "{$logPrefix} Collection ID not found for given handle: {$handle}\n", FILE_APPEND | LOCK_EX);
                header('Content-Type: application/json', true, 404);
                echo json_encode(['error' => 'Collection not found for the given handle.']);
                return;
            }
    
            file_put_contents(SHOPIFY_LOG_FILE, "{$logPrefix} SmartCollectionIdByTitle returned: {$collectionId}\n", FILE_APPEND | LOCK_EX);
            
            $bulkOrderProducts = $this->shopifyService->getBulkProducts($collectionId);
            file_put_contents(SHOPIFY_LOG_FILE, "{$logPrefix} Bulk products fetched successfully for collection ID: {$collectionId}\n", FILE_APPEND | LOCK_EX);
    
            header('Content-Type: application/json');
            echo json_encode($bulkOrderProducts);
        } catch (\Exception $e) { // Note the backslash before Exception
            file_put_contents(SHOPIFY_LOG_FILE, "{$logPrefix} Exception encountered: {$e->getMessage()}\n", FILE_APPEND | LOCK_EX);
            header('Content-Type: application/json', true, 500);
            echo json_encode(['error' => 'An error occurred while processing your request.']);
        }
    
        file_put_contents(SHOPIFY_LOG_FILE, "{$logPrefix} Exiting\n", FILE_APPEND | LOCK_EX);
    }

}
