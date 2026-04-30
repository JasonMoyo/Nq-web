<?php
// stripe-checkout.php
session_start();
require_once 'stripe-config.php';

$booking_id = $_GET['booking_id'] ?? null;
$amount = $_GET['amount'] ?? 500; // 500 cents = $5.00 USD
$service_name = $_GET['service'] ?? 'NqobileQ Service';
$customer_email = $_SESSION['user_email'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Secure Checkout - NqobileQ</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .checkout-container {
            max-width: 600px;
            margin: 100px auto;
            padding: 40px;
            background: var(--snd-bg-color);
            border-radius: 15px;
            border: 1px solid var(--main-color);
            text-align: center;
        }
        .amount {
            font-size: 3rem;
            color: var(--main-color);
            margin: 20px 0;
        }
        .amount small {
            font-size: 1rem;
            color: #888;
        }
        .booking-details {
            background: rgba(0,0,0,0.2);
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: left;
        }
        #checkout-button {
            background: #0055DE;
            color: white;
            padding: 15px 40px;
            font-size: 1.2rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        #checkout-button:hover {
            background: #0041b3;
            transform: scale(1.02);
        }
        .test-mode-badge {
            background: #ffc107;
            color: #000;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.7rem;
            display: inline-block;
            margin-bottom: 15px;
        }
        .secure-badge {
            margin-top: 20px;
            font-size: 0.8rem;
            color: #888;
        }
        .card-logos {
            margin: 20px 0;
            font-size: 1.1rem;
        }
    </style>
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body>
    <div class="checkout-container">
        <div class="test-mode-badge">🔧 TEST MODE - No real charges</div>
        <h1>🔒 Secure Checkout</h1>
        <div class="amount">
            $<?php echo number_format($amount / 100, 2); ?> USD
            <small>(approx ₹<?php echo round(($amount / 100) * 83); ?>)</small>
        </div>
        <div class="booking-details">
            <p><strong>Service:</strong> <?php echo htmlspecialchars($service_name); ?></p>
            <p><strong>Booking ID:</strong> #<?php echo $booking_id; ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($customer_email); ?></p>
        </div>
        <div class="card-logos">
            💳 Visa | Mastercard | Amex | Discover
        </div>
        <button id="checkout-button">Pay $<?php echo number_format($amount / 100, 2); ?> with Credit Card</button>
        <div class="secure-badge">
            🔐 Secure payment powered by Stripe<br>
            Your card details are never stored on our servers
        </div>
        <p style="margin-top: 15px; font-size: 0.7rem; color: #666;">
            Test Card: 4242 4242 4242 4242 | Any future expiry | Any CVC
        </p>
    </div>

    <script>
    const stripe = Stripe('<?php echo STRIPE_PUBLISHABLE_KEY; ?>');
    const checkoutButton = document.getElementById('checkout-button');

    checkoutButton.addEventListener('click', async () => {
        checkoutButton.disabled = true;
        checkoutButton.textContent = 'Processing...';
        
        const response = await fetch('api/create-checkout-session.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                booking_id: '<?php echo $booking_id; ?>',
                amount: '<?php echo $amount; ?>',
                service_name: '<?php echo htmlspecialchars($service_name); ?>',
                email: '<?php echo htmlspecialchars($customer_email); ?>'
            })
        });
        
        const session = await response.json();
        
        if (session.error) {
            alert('Error: ' + session.error);
            checkoutButton.disabled = false;
            checkoutButton.textContent = 'Pay Now';
            return;
        }
        
        // Redirect to Stripe Checkout
        window.location.href = session.url;
    });
    </script>
</body>
</html>