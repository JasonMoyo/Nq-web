<?php
// api/create-checkout-session.php
require_once '../config.php';

header('Content-Type: application/json');

try {
    $booking_id = $_POST['booking_id'] ?? null;
    $amount = $_POST['amount'] ?? 500;
    $service_name = $_POST['service_name'] ?? 'NqobileQ Service';
    $customer_email = $_POST['email'] ?? null;

    $checkout_session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'customer_email' => $customer_email,
        'line_items' => [[
            'price_data' => [
                'currency' => 'usd',
                'product_data' => [
                    'name' => $service_name,
                    'description' => 'Booking #' . $booking_id,
                ],
                'unit_amount' => $amount,
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => SITE_URL . '/payment-success.php?session_id={CHECKOUT_SESSION_ID}&booking_id=' . $booking_id,
        'cancel_url' => SITE_URL . '/payment-cancel.php',
        'metadata' => [
            'booking_id' => $booking_id,
        ],
    ]);

    echo json_encode(['id' => $checkout_session->id, 'url' => $checkout_session->url]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>