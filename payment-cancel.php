<?php
// payment-cancel.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Cancelled - NqobileQ</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .cancel-container {
            max-width: 500px;
            margin: 100px auto;
            padding: 40px;
            background: var(--snd-bg-color);
            border-radius: 20px;
            border: 1px solid var(--main-color);
            text-align: center;
            animation: fadeInUp 0.5s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .cancel-icon {
            font-size: 5rem;
            margin-bottom: 20px;
            animation: scaleIn 0.3s ease-out 0.2s both;
        }
        
        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }
        
        h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            color: var(--text-color);
        }
        
        p {
            font-size: 1.6rem;
            color: #aaa;
            margin-bottom: 15px;
            line-height: 1.6;
        }
        
        .btn-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 30px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 28px;
            background: var(--main-color);
            color: var(--bg-color);
            text-decoration: none;
            border-radius: 8px;
            font-size: 1.6rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0, 184, 169, 0.3);
        }
        
        .btn-secondary {
            background: transparent;
            border: 1px solid var(--main-color);
            color: var(--main-color);
        }
        
        .btn-secondary:hover {
            background: var(--main-color);
            color: var(--bg-color);
        }
        
        .test-mode-badge {
            background: #ffc107;
            color: #000;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 1.2rem;
            display: inline-block;
            margin-bottom: 20px;
        }
        
        @media (max-width: 600px) {
            .cancel-container {
                margin: 50px 20px;
                padding: 25px;
            }
            
            .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="cancel-container">
        <div class="test-mode-badge">🔧 TEST MODE</div>
        
        <div class="cancel-icon">❌</div>
        
        <h1>Payment Cancelled</h1>
        
        <p>You cancelled the payment. No charges were made to your account.</p>
        <p>You can try again whenever you're ready.</p>
        
        <div class="btn-group">
            <a href="index.php" class="btn">🏠 Return Home</a>
            <a href="javascript:history.back()" class="btn btn-secondary">🔄 Try Again</a>
        </div>
        
        <p style="margin-top: 30px; font-size: 1.2rem; color: #666;">
            Need help? <a href="https://wa.me/27782280408" style="color: var(--main-color);">Contact us on WhatsApp</a>
        </p>
    </div>
</body>
</html>