# 🚀 Heroku Deployment Guide

This guide will walk you through deploying your Spring Glossy Cosmetics ecommerce application to Heroku.

## 📋 **Prerequisites**

1. **Heroku Account** - Sign up at [heroku.com](https://heroku.com)
2. **Heroku CLI** - Install from [devcenter.heroku.com/articles/heroku-cli](https://devcenter.heroku.com/articles/heroku-cli)
3. **Git** - Ensure your project is in a Git repository
4. **Payment Gateway Accounts** - Paystack and/or Moniepoint accounts
5. **AWS S3 Account** (optional) - For file storage

## 🔧 **Installation & Setup**

### **1. Install Heroku CLI**

```bash
# macOS
brew tap heroku/brew && brew install heroku

# Windows
# Download from https://devcenter.heroku.com/articles/heroku-cli

# Linux
curl https://cli-assets.heroku.com/install.sh | sh
```

### **2. Login to Heroku**

```bash
heroku login
```

### **3. Initialize Git (if not already done)**

```bash
git init
git add .
git commit -m "Initial commit"
```

## 🚀 **Quick Deployment**

### **Option 1: Automated Deployment Script**

```bash
# Run the automated deployment script
./deploy.sh
```

The script will:

-   Create a new Heroku app (or use existing)
-   Set up PostgreSQL database
-   Set up Redis for caching and queues
-   Set up Mailgun for email
-   Configure all environment variables
-   Deploy your application
-   Run migrations and optimizations

### **Option 2: Manual Deployment**

#### **Step 1: Create Heroku App**

```bash
# Create new app
heroku create your-app-name

# Or use existing app
heroku git:remote -a your-app-name
```

#### **Step 2: Add Required Add-ons**

```bash
# PostgreSQL database
heroku addons:create heroku-postgresql:mini

# Redis for caching and queues
heroku addons:create heroku-redis:mini

# Mailgun for email
heroku addons:create mailgun:starter
```

#### **Step 3: Configure Environment Variables**

```bash
# Basic Laravel configuration
heroku config:set \
    APP_NAME="Spring Glossy Cosmetics" \
    APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stack \
    DB_CONNECTION=pgsql \
    DB_SSLMODE=require \
    CACHE_DRIVER=redis \
    SESSION_DRIVER=redis \
    QUEUE_CONNECTION=redis \
    MAIL_MAILER=smtp \
    MAIL_HOST=smtp.mailgun.org \
    MAIL_PORT=587 \
    MAIL_ENCRYPTION=tls

# Get Mailgun credentials
MAILGUN_DOMAIN=$(heroku config:get MAILGUN_DOMAIN)
MAILGUN_SECRET=$(heroku config:get MAILGUN_SECRET)

# Set email credentials
heroku config:set \
    MAIL_USERNAME=postmaster@$MAILGUN_DOMAIN \
    MAIL_PASSWORD=$MAILGUN_SECRET
```

#### **Step 4: Configure Payment Gateways**

```bash
# Paystack
heroku config:set PAYSTACK_PUBLIC_KEY=your_paystack_public_key
heroku config:set PAYSTACK_SECRET_KEY=your_paystack_secret_key

# Moniepoint
heroku config:set MONIEPOINT_PUBLIC_KEY=your_moniepoint_public_key
heroku config:set MONIEPOINT_SECRET_KEY=your_moniepoint_secret_key
heroku config:set MONIEPOINT_MERCHANT_ID=your_merchant_id
```

#### **Step 5: Configure AWS S3 (Optional)**

```bash
heroku config:set \
    AWS_ACCESS_KEY_ID=your_aws_key \
    AWS_SECRET_ACCESS_KEY=your_aws_secret \
    AWS_DEFAULT_REGION=us-east-1 \
    AWS_BUCKET=your_bucket_name \
    AWS_URL=https://your-bucket.s3.amazonaws.com
```

#### **Step 6: Set Application URLs**

```bash
heroku config:set FRONTEND_URL=https://your-frontend-domain.com
heroku config:set ADMIN_EMAIL=admin@springglossy.com
```

#### **Step 7: Deploy Application**

```bash
# Deploy to Heroku
git add .
git commit -m "Deploy to Heroku"
git push heroku main

# Run migrations
heroku run php artisan migrate --force

# Create storage link
# Note: storage:link not needed for S3 storage

# Optimize application
heroku run php artisan config:cache
heroku run php artisan route:cache
heroku run php artisan view:cache

# Scale worker dyno for queues
heroku ps:scale worker=1
```

## 🔧 **Post-Deployment Configuration**

### **1. Configure Webhook URLs**

In your payment gateway dashboards, set these webhook URLs:

**Paystack Dashboard:**

-   Webhook URL: `https://your-app-name.herokuapp.com/api/webhooks/paystack`

**Moniepoint Dashboard:**

-   Webhook URL: `https://your-app-name.herokuapp.com/api/webhooks/moniepoint`

### **2. Set Up Custom Domain (Optional)**

```bash
heroku domains:add your-domain.com
```

### **3. Monitor Your Application**

```bash
# View logs
heroku logs --tail

# Check app status
heroku ps

# Monitor dyno usage
heroku ps:scale
```

## 📊 **Heroku Resources & Costs**

### **Free Tier (No Longer Available)**

-   Heroku no longer offers a free tier

### **Basic Plan ($7/month)**

-   1 web dyno
-   1 worker dyno
-   512MB RAM per dyno
-   10,000 rows PostgreSQL
-   25MB Redis

### **Standard Plan ($25/month)**

-   1 web dyno
-   1 worker dyno
-   1GB RAM per dyno
-   1M rows PostgreSQL
-   100MB Redis

### **Production Plan ($250/month)**

-   2 web dynos
-   2 worker dynos
-   2.5GB RAM per dyno
-   10M rows PostgreSQL
-   1GB Redis

## 🔍 **Troubleshooting**

### **Common Issues & Solutions**

#### **1. Build Fails**

```bash
# Check build logs
heroku logs --tail

# Common fixes:
# - Ensure all dependencies are in composer.json
# - Check PHP version compatibility
# - Verify Procfile syntax
```

#### **2. Database Connection Issues**

```bash
# Check database status
heroku pg:info

# Reset database (WARNING: This will delete all data)
heroku pg:reset DATABASE_URL
heroku run php artisan migrate --force
```

#### **3. Queue Workers Not Running**

```bash
# Check worker status
heroku ps

# Scale workers
heroku ps:scale worker=1

# Check queue logs
heroku logs --tail --dyno=worker
```

#### **4. Payment Webhooks Not Working**

```bash
# Check webhook logs
heroku logs --tail | grep webhook

# Test webhook endpoints
curl -X POST https://your-app-name.herokuapp.com/api/webhooks/paystack \
  -H "Content-Type: application/json" \
  -d '{"test": "data"}'
```

#### **5. Email Not Sending**

```bash
# Check Mailgun configuration
heroku config:get MAILGUN_DOMAIN
heroku config:get MAILGUN_SECRET

# Test email sending
heroku run php artisan tinker
# Then run: Mail::raw('Test email', function($msg) { $msg->to('test@example.com')->subject('Test'); });
```

#### **6. SSL Certificate Verification Failed**

```bash
# Quick fix using the provided script
./fix_ssl.sh

# Manual fix
heroku config:set DB_SSLMODE=require --app your-app-name
heroku run php artisan config:clear --app your-app-name
heroku run php artisan config:cache --app your-app-name
heroku restart --app your-app-name
```

## 🛠️ **Maintenance Commands**

### **Application Management**

```bash
# Restart application
heroku restart

# View configuration
heroku config

# Update environment variables
heroku config:set VARIABLE_NAME=value

# Run artisan commands
heroku run php artisan migrate
heroku run php artisan cache:clear
heroku run php artisan config:cache
```

### **Database Management**

```bash
# Access database
heroku pg:psql

# Backup database
heroku pg:backups:capture

# Download backup
heroku pg:backups:download

# Restore database
heroku pg:backups:restore b001 DATABASE_URL
```

### **Monitoring & Logs**

```bash
# View real-time logs
heroku logs --tail

# View specific dyno logs
heroku logs --tail --dyno=web.1
heroku logs --tail --dyno=worker.1

# Check application metrics
heroku addons:open papertrail
```

## 🔒 **Security Considerations**

### **1. Environment Variables**

-   Never commit sensitive data to Git
-   Use Heroku config vars for all secrets
-   Rotate API keys regularly

### **2. Database Security**

-   Use SSL connections
-   Regularly backup data
-   Monitor for suspicious activity

### **3. Application Security**

-   Keep Laravel updated
-   Use HTTPS in production
-   Implement rate limiting
-   Monitor for security vulnerabilities

## 📈 **Scaling Your Application**

### **Horizontal Scaling**

```bash
# Scale web dynos
heroku ps:scale web=2

# Scale worker dynos
heroku ps:scale worker=2
```

### **Vertical Scaling**

```bash
# Upgrade to larger dyno types
heroku ps:type standard-2x
```

### **Database Scaling**

```bash
# Upgrade PostgreSQL plan
heroku addons:upgrade heroku-postgresql:standard-0
```

## 🎯 **Performance Optimization**

### **1. Caching**

```bash
# Enable Redis caching
heroku config:set CACHE_DRIVER=redis

# Cache routes and config
heroku run php artisan route:cache
heroku run php artisan config:cache
```

### **2. CDN Setup**

-   Use Cloudflare or AWS CloudFront
-   Configure for static assets
-   Enable compression

### **3. Database Optimization**

```bash
# Analyze slow queries
heroku pg:psql
# Then run: EXPLAIN ANALYZE your_query;
```

## ✅ **Deployment Checklist**

-   [ ] Heroku CLI installed and logged in
-   [ ] Git repository initialized
-   [ ] Heroku app created
-   [ ] PostgreSQL add-on added
-   [ ] Redis add-on added
-   [ ] Mailgun add-on added
-   [ ] Environment variables configured
-   [ ] Payment gateway credentials set
-   [ ] AWS S3 credentials set (if using)
-   [ ] Application deployed
-   [ ] Database migrations run
-   [ ] Storage link created
-   [ ] Application optimized (cached)
-   [ ] Worker dyno scaled
-   [ ] Webhook URLs configured
-   [ ] Custom domain set (optional)
-   [ ] SSL certificate enabled
-   [ ] Monitoring set up
-   [ ] Backup strategy implemented

## 🎉 **Success!**

Your Spring Glossy Cosmetics ecommerce application is now live on Heroku!

**Your app URL:** `https://your-app-name.herokuapp.com`

**Admin Panel:** `https://your-app-name.herokuapp.com/admin`

**API Base URL:** `https://your-app-name.herokuapp.com/api`

Remember to:

1. Configure webhook URLs in payment gateways
2. Set up monitoring and alerts
3. Implement regular backups
4. Monitor performance and costs
5. Keep your application updated

Happy selling! 🛍️✨
