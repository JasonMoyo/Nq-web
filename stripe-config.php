<?php
// stripe-config.php
require_once 'config.php';

// Stripe API keys - should be in your .env file
define('STRIPE_PUBLISHABLE_KEY', getenv('STRIPE_PUBLISHABLE_KEY') ?: 'pk_test_YOUR_KEY');
define('STRIPE_SECRET_KEY', getenv('STRIPE_SECRET_KEY') ?: 'sk_test_YOUR_KEY');

// Initialize Stripe
require_once __DIR__ . '/vendor/autoload.php';
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);