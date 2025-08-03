<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\AdminOrderNotification;
use App\Mail\OrderCreated;
use App\Models\Order;
use App\Models\Product;
use App\Services\PaystackService;
use App\Services\MonnifyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    protected $paystackService;
    protected $monnifyService;

    public function __construct(PaystackService $paystackService, MonnifyService $monnifyService)
    {
        $this->paystackService = $paystackService;
        $this->monnifyService = $monnifyService;
    }

    public function index(Request $request)
    {
        $orders = $request->user()->orders()
            ->with(['items.product'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'shipping_address' => 'required|array',
            'shipping_address.address' => 'required|string',
            'shipping_address.city' => 'required|string',
            'shipping_address.state' => 'required|string',
            'shipping_address.phone' => 'required|string',
            'payment_method' => 'required|in:paystack,moniepoint,transfer',
            'receipt' => 'required_if:payment_method,transfer|file|mimes:jpeg,png,jpg,pdf|max:5120', // 5MB max
        ]);

        return DB::transaction(function () use ($request) {
            // Calculate totals
            $subtotal = 0;
            $orderItems = [];

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['id']);

                // Check stock
                if ($product->stock_quantity < $item['quantity']) {
                    return response()->json([
                        'message' => "Insufficient stock for {$product->name}. Available: {$product->stock_quantity}",
                    ], 400);
                }

                $total = $product->price * $item['quantity'];
                $subtotal += $total;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'total' => $total,
                ];
            }

            $taxAmount = $subtotal * 0.075; // 7.5% tax
            $shippingFee = 2000; // Fixed shipping fee
            $totalAmount = $subtotal + $taxAmount + $shippingFee;

            // Create order
            $order = Order::create([
                'user_id' => $request->user()->id,
                'status' => Order::STATUS_PENDING,
                'payment_status' => Order::PAYMENT_STATUS_PENDING,
                'payment_method' => $request->payment_method,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'shipping_fee' => $shippingFee,
                'total_amount' => $totalAmount,
                'shipping_address' => $request->shipping_address,
            ]);

            // Create order items and update stock
            foreach ($orderItems as $item) {
                $order->items()->create($item);

                // Reduce stock quantity
                Product::where('id', $item['product_id'])
                    ->decrement('stock_quantity', $item['quantity']);
            }

            // Handle bank transfer receipt upload to S3
            if ($request->payment_method === 'transfer' && $request->hasFile('receipt')) {
                $file = $request->file('receipt');
                $fileName = 'receipts/' . $order->order_number . '_' . Str::uuid() . '.' . $file->getClientOriginalExtension();

                // Upload to S3
                $path = Storage::disk('s3')->putFileAs('', $file, $fileName, 'public');

                if ($path) {
                    $order->update(['payment_receipt_path' => $path]);
                }
            }

            // If payment method is Paystack or Monnify, initialize payment
            if (in_array($request->payment_method, ['paystack', 'monnify'])) {
                $service = $request->payment_method === 'paystack' ? $this->paystackService : $this->monnifyService;

                $paymentData = $service->initializePayment([
                    'email' => $request->user()->email,
                    'amount' => $request->payment_method === 'paystack' ? $totalAmount * 100 : $totalAmount, // Paystack expects kobo, Moniepoint expects naira
                    'reference' => $order->order_number,
                    'callback_url' => config('app.frontend_url') . '/checkout/success?orderId=' . $order->order_number,
                    'description' => 'Payment for order ' . $order->order_number,
                    'customer_name' => $request->user()->full_name,
                    'metadata' => [
                        'order_id' => $order->id,
                        'customer_name' => $request->user()->full_name,
                    ],
                ]);

                if ($paymentData['status']) {
                    $referenceField = $request->payment_method === 'paystack' ? 'paystack_reference' : 'monnify_reference';
                    $order->update([
                        $referenceField => $paymentData['data']['reference'],
                    ]);

                    return response()->json([
                        'order' => $order->load('items.product'),
                        'payment_url' => $paymentData['data']['authorization_url'] ?? $paymentData['data']['checkout_url'],
                        'payment_method' => $request->payment_method,
                    ], 201);
                } else {
                    // Restore stock if payment initialization fails
                    foreach ($orderItems as $item) {
                        Product::where('id', $item['product_id'])
                            ->increment('stock_quantity', $item['quantity']);
                    }

                    $order->delete();

                    return response()->json([
                        'message' => 'Failed to initialize payment',
                        'error' => $paymentData['message'] ?? 'Unknown error',
                    ], 400);
                }
            }

            // Send emails asynchronously
            Mail::to($request->user())->queue(new OrderCreated($order));
            Mail::to(config('mail.admin_email', 'admin@springglossy.com'))
                ->queue(new AdminOrderNotification($order));

            // For bank transfer, return success with bank details
            return response()->json([
                'order' => $order->load('items.product'),
                'message' => 'Order created successfully. Please proceed with bank transfer.',
                'bank_details' => [
                    'bank_name' => 'GTBank',
                    'account_number' => '0123456789',
                    'account_name' => 'Spring Glossy Cosmetics',
                    'amount' => $totalAmount,
                    'reference' => $order->order_number,
                ],
                'receipt_uploaded' => !empty($order->payment_receipt_path),
            ], 201);
        });
    }

    public function show(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return response()->json($order->load('items.product'));
    }
}
