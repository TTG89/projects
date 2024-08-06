<?php

namespace App\Controllers;

use App\Services\ShopifyGraphQLService;

class ProductGraphQLController
{
    protected $shopifyGraphQLService;

    public function __construct()
    {
        $this->shopifyGraphQLService = new ShopifyGraphQLService();
    }

    public function handleGraphQLRequest()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $query = $input['query'] ?? '';
        $variables = $input['variables'] ?? [];

        $result = $this->shopifyGraphQLService->graphqlQuery($query, $variables);
        header('Content-Type: application/json');
        echo json_encode($result);
    }

    public function getProduct($handle)
    {
        $product = $this->shopifyGraphQLService->getProductByHandle($handle);
        header('Content-Type: application/json');
        echo json_encode($product);
    }

    public function getProductAndVariant($handle)
    {
        $handleWithPageInfo = $handle;
        if (isset($_GET['page_info'])) {
            $handleWithPageInfo .= '?page_info=' . $_GET['page_info'];
        }

        $productAndVariants = $this->shopifyGraphQLService->getProductAndVariant($handleWithPageInfo);
        header('Content-Type: application/json');
        echo json_encode($productAndVariants);
    }

    public function getBulkOrderProductsByHandle($handle)
    {
        // Clean up the handle to remove any query parameters
        $handleParts = explode('?', $handle);
        $cleanHandle = $handleParts[0];

        // Split the cleaned handle to extract the collection identifier
        $parts = explode('-', $cleanHandle);
        $collectionIdentifier = $parts[0];

        $pageInfo = $_GET['page_info'] ?? null; // Get page_info from the query parameter

        $bulkProducts = $this->shopifyGraphQLService->getBulkProductsPaginated($collectionIdentifier, $pageInfo);

        if (isset($bulkProducts['error'])) {
            $this->shopifyGraphQLService->log("Error fetching bulk products: " . $bulkProducts['error']);
            header('Content-Type: application/json', true, 404);
            echo json_encode(['error' => $bulkProducts['error']]);
            return;
        }

        // Log the response from the Shopify service
        $this->shopifyGraphQLService->log("Bulk Products Response: " . json_encode($bulkProducts));

        header('Content-Type: application/json');
        echo json_encode($bulkProducts);
    }

    public function getBestSellingProducts()
    {
        $bestSellingProducts = $this->shopifyGraphQLService->getBestSellingProducts();
        header('Content-Type: application/json');
        echo json_encode($bestSellingProducts);
    }
}
