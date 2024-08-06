<?php
// Load environment variables and autoload dependencies
require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Include the functions file
require __DIR__ . '/src/functions.php';

// Initialize the logger (uncomment if needed)
// $logger = initializeLogger(); 

// Fetch raw data from the request body
$requestBody = file_get_contents('php://input');

// Fetch all headers
$headers = getallheaders();

// Handle different webhook topics based on the header 'X-Shopify-Topic'
if (isset($headers['X-Shopify-Topic'])) {
    $eventType = $headers['X-Shopify-Topic'];
    $data = json_decode($requestBody, true);

    if ($eventType === 'orders/create') {        
        handleWebhookData($data, $eventType);
    } elseif ($eventType === 'refunds/create') {        
        handleRefundCreateWebhook($data);
    }
}

// Send response
header('Content-Type: application/json');
echo json_encode(['status' => 'success']);
