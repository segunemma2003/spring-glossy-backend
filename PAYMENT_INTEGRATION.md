# 🏦 Payment Integration Guide

This document outlines the complete payment integration setup for the Spring Glossy Cosmetics ecommerce platform.

## 📋 **What Was Missing & What's Now Implemented**

### **Previously Missing:**

1. ❌ Payment webhooks for automatic confirmation
2. ❌ Payment verification endpoints
3. ❌ Moniepoint payment gateway
4. ❌ Frontend URL configuration
5. ❌ Admin email configuration
6. ❌ Payment status updates after successful payment
7. ❌ Manual payment verification tools

### **Now Implemented:**

1. ✅ **Paystack Integration** - Complete with webhooks
2. ✅ **Moniepoint Integration** - Complete with webhooks
3. ✅ **Bank Transfer** - With receipt upload to S3
4. ✅ **Payment Webhooks** - Automatic payment confirmation
5. ✅ **Payment Verification** - Manual and automatic verification
6. ✅ **Queue Jobs** - Asynchronous payment processing
7. ✅ **Admin Commands** - Manual payment verification
8. ✅ **Complete Configuration** - All necessary settings

---

## 🔧 **Environment Configuration**

Add these variables to your `.env` file:

```env
# Payment Gateways
PAYSTACK_PUBLIC_KEY=pk_test_your_paystack_public_key
PAYSTACK_SECRET_KEY=sk_test_your_paystack_secret_key

MONIEPOINT_PUBLIC_KEY=mpk_test_your_moniepoint_public_key
MONIEPOINT_SECRET_KEY=msk_test_your_moniepoint_secret_key
MONIEPOINT_MERCHANT_ID=your_merchant_id

# Application URLs
FRONTEND_URL=http://localhost:3000
ADMIN_EMAIL=admin@springglossy.com

# AWS S3 (for receipt uploads)
AWS_ACCESS_KEY_ID=your_aws_key
AWS_SECRET_ACCESS_KEY=your_aws_secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your_bucket_name
AWS_URL=https://your-bucket.s3.amazonaws.com
```

---

## 🚀 **New API Endpoints**

### **Public Endpoints (No Authentication)**

#### 1. **Get Available Payment Methods**

```http
GET /api/payment-methods
```

**Response:**

```json
{
    "payment_methods": [
        {
            "id": "paystack",
            "name": "Paystack",
            "description": "Pay with card, bank transfer, USSD, etc.",
            "logo": "https://paystack.com/static/img/logo-blue.svg"
        },
        {
            "id": "moniepoint",
            "name": "Moniepoint",
            "description": "Pay with Moniepoint wallet or card",
            "logo": "https://moniepoint.com/logo.png"
        },
        {
            "id": "transfer",
            "name": "Bank Transfer",
            "description": "Pay via bank transfer and upload receipt",
            "logo": null
        }
    ]
}
```

#### 2. **Paystack Webhook**

```http
POST /api/webhooks/paystack
```

**Headers:** `X-Paystack-Signature: {signature}`
**Body:** Paystack webhook payload

#### 3. **Moniepoint Webhook**

```http
POST /api/webhooks/moniepoint
```

**Headers:** `X-Moniepoint-Signature: {signature}`
**Body:** Moniepoint webhook payload

### **Protected Endpoints (Require Authentication)**

#### 4. **Verify Payment**

```http
POST /api/payments/verify
```

**Request Body:**

```json
{
    "reference": "SG20240001",
    "payment_method": "paystack"
}
```

---

## 💳 **Payment Flow**

### **1. Paystack Payment Flow**

```
1. User creates order → POST /api/orders
2. Paystack payment initialized
3. User redirected to Paystack payment page
4. User completes payment
5. Paystack sends webhook to /api/webhooks/paystack
6. Payment verified and order status updated
7. Confirmation emails sent
```

### **2. Moniepoint Payment Flow**

```
1. User creates order → POST /api/orders
2. Moniepoint payment initialized
3. User redirected to Moniepoint payment page
4. User completes payment
5. Moniepoint sends webhook to /api/webhooks/moniepoint
6. Payment verified and order status updated
7. Confirmation emails sent
```

### **3. Bank Transfer Flow**

```
1. User creates order → POST /api/orders
2. Receipt uploaded to S3
3. Bank details provided to user
4. User makes transfer manually
5. Admin verifies receipt manually
6. Order status updated manually
```

---

## 🔄 **Updated Order Creation**

The order creation endpoint now supports all three payment methods:

```http
POST /api/orders
```

**Request Body:**

```json
{
    "items": [
        {
            "id": 1,
            "quantity": 2
        }
    ],
    "shipping_address": {
        "address": "123 Main Street",
        "city": "Lagos",
        "state": "Lagos",
        "phone": "+2348012345678"
    },
    "payment_method": "moniepoint" // "paystack", "moniepoint", or "transfer"
}
```

**Response (Moniepoint):**

```json
{
    "order": {
        "id": 1,
        "order_number": "SG20240001",
        "status": "pending",
        "payment_status": "pending",
        "payment_method": "moniepoint",
        "moniepoint_reference": "MP_REF_123456",
        "total_amount": 7375.00,
        "items": [...]
    },
    "payment_url": "https://checkout.moniepoint.com/abc123",
    "payment_method": "moniepoint"
}
```

---

## 🛠️ **Admin Tools**

### **1. Manual Payment Verification Command**

```bash
# Verify Paystack payment
php artisan payment:verify SG20240001 --method=paystack

# Verify Moniepoint payment
php artisan payment:verify SG20240001 --method=moniepoint

# Check bank transfer status
php artisan payment:verify SG20240001 --method=transfer
```

### **2. Queue Jobs**

Payment verification runs asynchronously:

```bash
# Process payment verification jobs
php artisan queue:work
```

---

## 🔐 **Webhook Security**

### **Paystack Webhook Verification**

-   Uses HMAC SHA512 signature verification
-   Signature header: `X-Paystack-Signature`
-   Secret key from `PAYSTACK_SECRET_KEY`

### **Moniepoint Webhook Verification**

-   Uses HMAC SHA256 signature verification
-   Signature header: `X-Moniepoint-Signature`
-   Secret key from `MONIEPOINT_SECRET_KEY`

---

## 📧 **Email Notifications**

### **Automatic Emails Sent:**

1. **Customer Confirmation** - Order details and payment confirmation
2. **Admin Notification** - New order with payment details
3. **Receipt Attachment** - For bank transfers, receipt is attached to admin email

---

## 🗄️ **Database Changes**

### **New Column Added:**

-   `moniepoint_reference` - Stores Moniepoint payment reference

### **Migration:**

```bash
php artisan migrate
```

---

## 🧪 **Testing**

### **1. Test Payment Methods**

```bash
# Get available payment methods
curl -X GET http://localhost:8000/api/payment-methods
```

### **2. Test Order Creation**

```bash
# Create order with Moniepoint
curl -X POST http://localhost:8000/api/orders \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "items": [{"id": 1, "quantity": 1}],
    "shipping_address": {
      "address": "123 Test St",
      "city": "Lagos",
      "state": "Lagos",
      "phone": "+2348012345678"
    },
    "payment_method": "moniepoint"
  }'
```

### **3. Test Payment Verification**

```bash
# Verify payment manually
curl -X POST http://localhost:8000/api/payments/verify \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "reference": "SG20240001",
    "payment_method": "moniepoint"
  }'
```

---

## 🚨 **Important Notes**

1. **Webhook URLs** - Configure these in your payment gateway dashboards:

    - Paystack: `https://your-domain.com/api/webhooks/paystack`
    - Moniepoint: `https://your-domain.com/api/webhooks/moniepoint`

2. **SSL Required** - Webhooks require HTTPS in production

3. **Queue Processing** - Ensure queue workers are running for payment verification

4. **Error Handling** - All payment failures are logged and handled gracefully

5. **Stock Management** - Stock is automatically reduced on order creation and restored on payment failure

---

## ✅ **Complete Payment Integration Checklist**

-   [x] Paystack integration with webhooks
-   [x] Moniepoint integration with webhooks
-   [x] Bank transfer with receipt upload
-   [x] Payment verification endpoints
-   [x] Automatic email notifications
-   [x] Queue jobs for async processing
-   [x] Admin commands for manual verification
-   [x] Complete error handling
-   [x] Security with signature verification
-   [x] Database migrations
-   [x] Configuration management
-   [x] Comprehensive documentation

The payment system is now **100% complete** and ready for production use! 🎉
