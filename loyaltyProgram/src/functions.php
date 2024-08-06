<?php
// Import any required dependencies or classes here
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use GuzzleHttp\Client;

/**
 * Calculate the spending of a customer.
 *
 * @param int $customer_id The ID of the customer.
 * @return array An array containing net spent and orders.
 * @throws Exception If customer ID is missing or data fetching/updating fails.
 */
function calculateCustomerSpending($customer_id)
{
    if (!$customer_id) {
        throw new Exception("customer_id is required");
    }

    $twelveMonthsAgo = date('c', strtotime('-12 months'));
    $api_url_orders = "https://" . $_ENV['SHOPIFY_DOMAIN'] . ".myshopify.com/admin/api/" . $_ENV['SHOPIFY_API_VERSION'] . "/orders.json?customer_id=" . $customer_id . "&created_at_min=" . $twelveMonthsAgo;
    $headers = [
        "Content-Type" => "application/json",
        "X-Shopify-Access-Token" => $_ENV['SHOPIFY_ACCESS_TOKEN'],
    ];

    try {
        $client = new Client();
        $response = $client->get($api_url_orders, ['headers' => $headers]);
        $orders = json_decode($response->getBody()->getContents())->orders;

        $total_spent_last_12_months = number_format(array_reduce($orders, function ($acc, $order) {
            if ($order->financial_status == "paid" && $order->fulfillment_status == "fulfilled") {
                return $acc + (float)($order->current_subtotal_price ?? 0);
            }
            return $acc;
        }, 0), 2, '.', '');

        $total_refunded_amount = total_refunded($orders);
        $total_spent_last_12_months -= $total_refunded_amount;

        $metadata = [
            "metafield" => [
                "namespace" => "custom",
                "key" => "loyalty_program_12_month_spend",
                "value" => $total_spent_last_12_months,
                "type" => "number_decimal",
            ],
        ];

        $fetchLikePayload = [
            "body" => json_encode($metadata),
            "method" => "POST",
            "headers" => [
                "X-Shopify-Access-Token" => $_ENV['SHOPIFY_ACCESS_TOKEN'],
                "Content-Type" => "application/json",
            ],
        ];

        $customerUpdateUrl = "https://" . $_ENV['SHOPIFY_DOMAIN'] . ".myshopify.com/admin/api/" . $_ENV['SHOPIFY_API_VERSION'] . "/customers/$customer_id/metafields.json";
        $client->post($customerUpdateUrl, $fetchLikePayload);

        return ["net_spent_last_12_months" => $total_spent_last_12_months, "orders" => $orders];
    } catch (Exception $error) {
        throw new Exception("Failed to fetch or update data: " . $error->getMessage());
    }
}

/**
 * Calculate total refunded amount for a set of orders.
 *
 * @param array $orders The orders to sum the refunds for.
 * @return float The total amount refunded across all orders.
 */
function total_refunded($orders)
{
    return array_reduce($orders, function ($acc, $order) {
        $refunds = $order->refunds ?? [];
        foreach ($refunds as $refund) {
            $order_adjustments = $refund->order_adjustments ?? [];
            foreach ($order_adjustments as $adjustment) {
                $acc -= (float)($adjustment->amount ?? 0);
            }
        }
        return $acc;
    }, 0);
}

/**
 * Update customer tags.
 *
 * @param int $customer_id The ID of the customer.
 * @throws Exception If an error occurs while updating the tags.
 */
function updateCustomerTags($customer_id)
{
    $url = "https://" . $_ENV['SHOPIFY_DOMAIN'] . ".myshopify.com/admin/api/" . $_ENV['SHOPIFY_API_VERSION'] . "/customers/$customer_id.json";
    $headers = [
        "Content-Type" => "application/json",
        "X-Shopify-Access-Token" => $_ENV['SHOPIFY_ACCESS_TOKEN'],
    ];

    try {
        $client = new Client();
        $response = $client->get($url, ['headers' => $headers]);
        $customerData = json_decode($response->getBody()->getContents())->customer;
        $existingTags = isset($customerData->tags) ? explode(",", $customerData->tags) : [];

        $spendingData = calculateCustomerSpending($customer_id);
        $totalSpentInLast12Months = $spendingData['net_spent_last_12_months'];

        $newTag = "";
        $tagCategories = ["Platinum", "Gold", "Silver", "Bronze", "General", "Guest"];

        if ($totalSpentInLast12Months >= 10500) {
            $newTag = "Platinum";
        } elseif ($totalSpentInLast12Months >= 7500) {
            $newTag = "Gold";
        } elseif ($totalSpentInLast12Months >= 4500) {
            $newTag = "Silver";
        } elseif ($totalSpentInLast12Months >= 1500) {
            $newTag = "Bronze";
        } elseif ($totalSpentInLast12Months < 1499) {
            $newTag = "General";
        }

        $filteredTags = array_filter($existingTags, function ($tag) use ($tagCategories) {
            return !in_array($tag, $tagCategories);
        });

        if ($newTag) {
            $filteredTags[] = $newTag;
        }

        $updatedTagsString = implode(",", $filteredTags);

        $updatePayload = [
            "customer" => [
                "id" => $customer_id,
                "tags" => $updatedTagsString,
            ],
        ];

        $client->put("https://" . $_ENV['SHOPIFY_DOMAIN'] . ".myshopify.com/admin/api/" . $_ENV['SHOPIFY_API_VERSION'] . "/customers/$customer_id.json", [
            "json" => $updatePayload,
            "headers" => $headers,
        ]);

        $metadata = [
            "metafield" => [
                "namespace" => "custom",
                "key" => "loyalty_discount",
                "value" => $newTag,
                "type" => "single_line_text_field",
            ],
        ];

        $fetchLikePayload = [
            "body" => json_encode($metadata),
            "method" => "POST",
            "headers" => [
                "X-Shopify-Access-Token" => $_ENV['SHOPIFY_ACCESS_TOKEN'],
                "Content-Type" => "application/json",
            ],
        ];

        $customerUpdateUrl = "https://" . $_ENV['SHOPIFY_DOMAIN'] . ".myshopify.com/admin/api/" . $_ENV['SHOPIFY_API_VERSION'] . "/customers/$customer_id/metafields.json";
        $client->post($customerUpdateUrl, $fetchLikePayload);
    } catch (Exception $error) {
        throw new Exception("Error updating tags for customer ID: $customer_id - " . $error->getMessage());
    }
}

/**
 * Handle webhook data and trigger appropriate actions.
 *
 * @param array $data The data received from the webhook.
 * @param string $eventType The type of event received.
 */
function handleWebhookData($data, $eventType)
{
    $customer_id = $data['customer']['id'] ?? null;
    if ($customer_id) {
        calculateCustomerSpending($customer_id);
        updateCustomerTags($customer_id);
    }
}

/**
 * Handle refunds/create webhook event.
 *
 * @param array $data The data received from the webhook.
 */
function handleRefundCreateWebhook($data)
{
    if (isset($data['order_id'])) {
        $order_id = $data['order_id'];
        $order_details = fetchOrderDetails($order_id);
        if ($order_details && isset($order_details['customer_id'])) {
            $customer_id = $order_details['customer_id'];
            calculateCustomerSpending($customer_id);
            updateCustomerTags($customer_id);
        }
    }
}

/**
 * Fetch order details from Shopify based on the order ID.
 *
 * @param int $order_id The ID of the order to fetch.
 * @return array|null Order details if found, or null if not found.
 */
function fetchOrderDetails($order_id)
{
    $url = "https://" . $_ENV['SHOPIFY_DOMAIN'] . ".myshopify.com/admin/api/" . $_ENV['SHOPIFY_API_VERSION'] . "/orders/$order_id.json";
    $headers = [
        "Content-Type" => "application/json",
        "X-Shopify-Access-Token" => $_ENV['SHOPIFY_ACCESS_TOKEN'],
    ];

    try {
        $client = new Client();
        $response = $client->get($url, ['headers' => $headers]);
        $order = json_decode($response->getBody()->getContents())->order;

        if (isset($order->customer)) {
            return [
                'customer_id' => $order->customer->id,
            ];
        }

        return null;
    } catch (Exception $error) {
        return null;
    }
}
