<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New Order Notification</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #f8f9fa; padding: 20px; text-align: center; border-radius: 5px; }
        .order-details { background: #fff; border: 1px solid #ddd; border-radius: 5px; padding: 20px; margin: 20px 0; }
        .customer-info { background: #e9ecef; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .product-item { border-bottom: 1px solid #eee; padding: 10px 0; }
        .total-section { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .status-badge { display: inline-block; padding: 5px 10px; border-radius: 15px; font-size: 12px; font-weight: bold; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-paid { background: #d4edda; color: #155724; }
        .status-processing { background: #cce5ff; color: #004085; }
        .payment-info { background: #e2e3e5; padding: 15px; border-radius: 5px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🛍️ New Order Received</h1>
            <p><strong>Order #{{ $order->order_number }}</strong></p>
        </div>

        <div class="order-details">
            <h2>📋 Order Information</h2>
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
            <p><strong>Payment Method:</strong> {{ ucfirst($order->payment_method) }}</p>
            @if($order->payment_reference)
                <p><strong>Payment Reference:</strong> {{ $order->payment_reference }}</p>
            @endif
            @if($order->paid_at)
                <p><strong>Paid At:</strong> {{ $order->paid_at->format('F j, Y \a\t g:i A') }}</p>
            @endif
        </div>

        <div class="customer-info">
            <h3>👤 Customer Information</h3>
            <p><strong>Name:</strong> {{ $order->user->name }}</p>
            <p><strong>Email:</strong> {{ $order->user->email }}</p>
            <p><strong>Phone:</strong> {{ $order->user->phone ?? 'Not provided' }}</p>
        </div>

        <div class="order-details">
            <h3>📍 Shipping Address</h3>
            @if($order->shipping_address)
                <p><strong>Address:</strong> {{ $order->shipping_address['address'] ?? 'Not provided' }}</p>
                <p><strong>City:</strong> {{ $order->shipping_address['city'] ?? 'Not provided' }}</p>
                <p><strong>State:</strong> {{ $order->shipping_address['state'] ?? 'Not provided' }}</p>
                <p><strong>Postal Code:</strong> {{ $order->shipping_address['postal_code'] ?? 'Not provided' }}</p>
                <p><strong>Country:</strong> {{ $order->shipping_address['country'] ?? 'Not provided' }}</p>
            @else
                <p>No shipping address provided</p>
            @endif
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
            <div class="payment-info">
                <h3>🏦 Bank Transfer Information</h3>
                <p><strong>Receipt Uploaded:</strong> {{ $order->payment_receipt_path ? 'Yes' : 'No' }}</p>
                @if($order->payment_receipt_path)
                    <p><strong>Receipt URL:</strong> <a href="{{ $order->payment_receipt_url }}">View Receipt</a></p>
                @endif
            </div>
        @endif

        @if($order->notes)
            <div class="order-details">
                <h3>📝 Customer Notes</h3>
                <p>{{ $order->notes }}</p>
            </div>
        @endif

        <div class="order-details">
            <h3>🔗 Admin Actions</h3>
            <p>To manage this order, please visit your admin panel:</p>
            <p><a href="{{ config('app.url') }}/admin/orders/{{ $order->id }}">View Order in Admin Panel</a></p>
        </div>

        <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 5px; text-align: center;">
            <p><strong>Spring Glossy Cosmetics</strong></p>
            <p>Thank you for your business!</p>
        </div>
    </div>
</body>
</html>
