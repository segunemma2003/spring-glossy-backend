<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order Confirmation</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #f8f9fa; padding: 20px; text-align: center; border-radius: 5px; }
        .order-details { background: #fff; border: 1px solid #ddd; border-radius: 5px; padding: 20px; margin: 20px 0; }
        .product-item { border-bottom: 1px solid #eee; padding: 10px 0; }
        .total-section { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .status-badge { display: inline-block; padding: 5px 10px; border-radius: 15px; font-size: 12px; font-weight: bold; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-paid { background: #d4edda; color: #155724; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Order Confirmation</h1>
            <p>Thank you for your order!</p>
        </div>

        <div class="order-details">
            <h2>📋 Order Information</h2>
            <p><strong>Order Number:</strong> {{ $order->order_number }}</p>
            <p><strong>Order Date:</strong> {{ $order->created_at->format('F j, Y \a\t g:i A') }}</p>
            <p><strong>Order Status:</strong>
                <span class="status-badge status-{{ $order->status }}">
                    {{ ucfirst($order->status) }}
                </span>
            </p>
            <p><strong>Payment Status:</strong>
                <span class="status-badge status-{{ $order->payment_status }}">
                    {{ ucfirst($order->payment_status) }}
                </span>
            </p>
        </div>

        <div class="order-details">
            <h3>🛒 Order Items</h3>
            @foreach($order->items as $item)
                <div class="product-item">
                    <p><strong>{{ $item->product->name }}</strong></p>
                    <p>Quantity: {{ $item->quantity }}</p>
                    <p>Price: ₦{{ number_format($item->price, 2) }}</p>
                    <p>Total: ₦{{ number_format($item->total, 2) }}</p>
                </div>
            @endforeach
        </div>

        <div class="total-section">
            <h3>💰 Order Summary</h3>
            <p><strong>Subtotal:</strong> ₦{{ number_format($order->subtotal, 2) }}</p>
            <p><strong>Tax:</strong> ₦{{ number_format($order->tax_amount, 2) }}</p>
            <p><strong>Shipping Fee:</strong> ₦{{ number_format($order->shipping_fee, 2) }}</p>
            <p><strong>Total Amount:</strong> ₦{{ number_format($order->total_amount, 2) }}</p>
        </div>

        @if($order->payment_method === 'transfer')
            <div class="order-details">
                <h3>🏦 Bank Transfer Instructions</h3>
                <p>Please complete your payment via bank transfer:</p>
                <p><strong>Bank:</strong> GTBank</p>
                <p><strong>Account Number:</strong> 0123456789</p>
                <p><strong>Account Name:</strong> Spring Glossy Cosmetics</p>
                <p><strong>Amount:</strong> ₦{{ number_format($order->total_amount, 2) }}</p>
                <p><strong>Reference:</strong> {{ $order->order_number }}</p>
                <p>After payment, please upload your receipt in your order history.</p>
            </div>
        @endif

        <div class="order-details">
            <h3>📍 Shipping Address</h3>
            @if($order->shipping_address)
                <p>{{ $order->shipping_address['address'] ?? '' }}</p>
                <p>{{ $order->shipping_address['city'] ?? '' }}, {{ $order->shipping_address['state'] ?? '' }}</p>
                <p>{{ $order->shipping_address['postal_code'] ?? '' }}, {{ $order->shipping_address['country'] ?? '' }}</p>
            @else
                <p>No shipping address provided</p>
            @endif
        </div>

        <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 5px; text-align: center;">
            <p><strong>Spring Glossy Cosmetics</strong></p>
            <p>We'll notify you when your order ships!</p>
            <p>If you have any questions, please contact us at support@springglossy.com</p>
        </div>
    </div>
</body>
</html>
