<?php

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../server/config/config.php';

use Dotenv\Dotenv;
use App\Controllers\ProductGraphQLController;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Set CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Return only headers and no content for OPTIONS requests
    http_response_code(204);
    exit;
}

//file_put_contents(SHOPIFY_LOG_FILE, "index start\n", FILE_APPEND | LOCK_EX);

// Simple router
$request = $_SERVER['REQUEST_URI'];

if ($request === '/graphql' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new ProductGraphQLController();
    $controller->handleGraphQLRequest();
    exit;
}

// Route for getting a single product
if (preg_match('/^\/api\/product\/([^\/]+)$/', $request, $matches)) {
    $controller = new ProductGraphQLController();
    $controller->getProduct($matches[1]);
    exit;
}

// Route for getting a product and its Variant products
if (preg_match('/^\/api\/variant-product\/([^\/]+)$/', $request, $matches)) {
    $controller = new ProductGraphQLController();
    $controller->getProductAndVariant($matches[1]);
    exit;
}

// Route for getting bulk order products
if (preg_match('/^\/api\/bulk-variant-products\/([^\/]+)$/', $request, $matches)) {
    $controller = new ProductGraphQLController();
    $controller->getBulkOrderProductsByHandle($matches[1]);
    exit;
}

// Route for getting best-selling products
if (preg_match('/^\/api\/best-selling-products$/', $request)) {
    $controller = new ProductGraphQLController();
    $controller->getBestSellingProducts();
    exit;
}

// 404 Not Found for unmatched routes
http_response_code(404);
echo json_encode(['error' => 'Not Found']);
exit;