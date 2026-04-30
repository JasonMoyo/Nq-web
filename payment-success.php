<?php
// payment-success.php
require_once 'config.php';  // Stripe is already initialized!

$session_id = $_GET['session_id'] ?? null;
$booking_id = $_GET['booking_id'] ?? null;

if (!$session_id || !$booking_id) {
    header('Location: index.php');
    exit();
}

try {
    $session = \Stripe\Checkout\Session::retrieve($session_id);
    
    if ($session->payment_status === 'paid') {
        $conn = getDB();
        $stmt = $conn->prepare("UPDATE service_bookings 
                                SET payment_status = 'paid', 
                                    payment_intent_id = ?, 
                                    amount_paid = ? 
                                WHERE id = ?");
        $stmt->bind_param("sdi", $session->payment_intent, $session->amount_total / 100, $booking_id);
        $stmt->execute();
        $stmt->close();
        $conn->close();
        
        $success = true;
        $amount = $session->amount_total / 100;
        $payment_intent = $session->payment_intent;
    } else {
        $success = false;
    }
    
} catch (Exception $e) {
    $success = false;
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Status - NqobileQ</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .status-container {
            max-width: 500px;
            margin: 100px auto;
            padding: 40px;
            background: var(--snd-bg-color);
            border-radius: 15px;
            border: 1px solid var(--main-color);
            text-align: center;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: var(--main-color);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin: 10px;
        }
    </style>
</head>
<body>
    <div class="status-container">
        <?php if (isset($success) && $success): ?>
            <div style="font-size: 4rem; color: #28a745;">✅</div>
            <h1>Payment Successful!</h1>
            <p>Your payment of $<?php echo number_format($amount, 2); ?> was successful.</p>
            <p>Booking ID: #<?php echo $booking_id; ?></p>
            <p>Payment ID: <?php echo $payment_intent; ?></p>
            <a href="index.php" class="btn">Return Home</a>
        <?php else: ?>
            <div style="font-size: 4rem; color: #dc3545;">❌</div>
            <h1>Payment Failed</h1>
            <p>There was an issue processing your payment.</p>
            <?php if (isset($error)): ?>
                <p style="color: #ff8888;">Error: <?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <a href="javascript:history.back()" class="btn">Try Again</a>
        <?php endif; ?>
    </div>
</body>
</html>