<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Spring Glossy Cosmetics</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .api-section {
            margin: 20px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .api-section h2 {
            color: #007bff;
            margin-top: 0;
        }
        .endpoint {
            background: #e9ecef;
            padding: 10px;
            margin: 5px 0;
            border-radius: 3px;
            font-family: monospace;
        }
        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        .status.success {
            background: #d4edda;
            color: #155724;
        }
        .status.public {
            background: #d1ecf1;
            color: #0c5460;
        }
        .status.protected {
            background: #fff3cd;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Spring Glossy Cosmetics API</h1>

        <div class="api-section">
            <h2>Public Endpoints</h2>
            <div class="endpoint">
                <span class="status public">PUBLIC</span>
                POST /api/register - User registration
            </div>
            <div class="endpoint">
                <span class="status public">PUBLIC</span>
                POST /api/login - User login
            </div>
            <div class="endpoint">
                <span class="status public">PUBLIC</span>
                GET /api/products - List all products
            </div>
            <div class="endpoint">
                <span class="status public">PUBLIC</span>
                GET /api/products/featured - Featured products
            </div>
            <div class="endpoint">
                <span class="status public">PUBLIC</span>
                GET /api/products/{slug} - Product details
            </div>
            <div class="endpoint">
                <span class="status public">PUBLIC</span>
                GET /api/payment-methods - Available payment methods
            </div>
            <div class="endpoint">
                <span class="status public">PUBLIC</span>
                GET /api/settings - Application settings
            </div>
            <div class="endpoint">
                <span class="status public">PUBLIC</span>
                POST /api/contact - Contact form submission
            </div>
        </div>

        <div class="api-section">
            <h2>Protected Endpoints (Require Authentication)</h2>
            <div class="endpoint">
                <span class="status protected">PROTECTED</span>
                POST /api/logout - User logout
            </div>
            <div class="endpoint">
                <span class="status protected">PROTECTED</span>
                GET /api/me - Get current user
            </div>
            <div class="endpoint">
                <span class="status protected">PROTECTED</span>
                PUT /api/profile - Update user profile
            </div>
            <div class="endpoint">
                <span class="status protected">PROTECTED</span>
                GET /api/orders - User orders
            </div>
            <div class="endpoint">
                <span class="status protected">PROTECTED</span>
                POST /api/orders - Create new order
            </div>
            <div class="endpoint">
                <span class="status protected">PROTECTED</span>
                GET /api/orders/{id} - Order details
            </div>
            <div class="endpoint">
                <span class="status protected">PROTECTED</span>
                POST /api/payments/verify - Verify payment
            </div>
            <div class="endpoint">
                <span class="status protected">PROTECTED</span>
                GET /api/wishlist - User wishlist
            </div>
            <div class="endpoint">
                <span class="status protected">PROTECTED</span>
                POST /api/wishlist - Add to wishlist
            </div>
            <div class="endpoint">
                <span class="status protected">PROTECTED</span>
                DELETE /api/wishlist/{product} - Remove from wishlist
            </div>
        </div>

        <div class="api-section">
            <h2>Payment Webhooks</h2>
            <div class="endpoint">
                <span class="status public">PUBLIC</span>
                POST /api/webhooks/paystack - Paystack webhook
            </div>
        </div>

        <div class="api-section">
            <h2>Admin Panel</h2>
            <div class="endpoint">
                <span class="status success">ADMIN</span>
                <a href="/admin" style="color: #007bff; text-decoration: none;">/admin - Filament Admin Panel</a>
            </div>
        </div>

        <div class="api-section">
            <h2>System Status</h2>
            <div class="endpoint">
                <span class="status success">✓</span>
                Laravel Framework: Running
            </div>
            <div class="endpoint">
                <span class="status success">✓</span>
                API Endpoints: Available
            </div>
            <div class="endpoint">
                <span class="status success">✓</span>
                Database: Connected
            </div>
            <div class="endpoint">
                <span class="status success">✓</span>
                Redis: Configured with Predis
            </div>
        </div>
    </div>
</body>
</html>
