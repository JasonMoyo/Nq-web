<?php
require_once '../stripe-config.php';
header('Content-Type: application/json');

try {
    // Your checkout session code here
    $checkout_session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'usd',
                'product_data' => [
                    'name' => $_POST['service_name'] ?? 'NqobileQ Service',
                ],
                'unit_amount' => intval($_POST['amount'] ?? 500),
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => 'http://your-domain/payment-success.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => 'http://your-domain/payment-cancel.php',
        'customer_email' => $_POST['email'] ?? '',
    ]);
    
    echo json_encode(['id' => $checkout_session->id, 'url' => $checkout_session->url]);
} catch(Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}