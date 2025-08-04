#!/bin/bash

# Spring Glossy Cosmetics - Heroku Deployment Script
# This script automates the deployment process to Heroku

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}🚀 Spring Glossy Cosmetics - Heroku Deployment${NC}"
echo "=================================================="

# Get app name
read -p "Enter Heroku app name (or press Enter to generate one): " APP_NAME

if [ -z "$APP_NAME" ]; then
    echo -e "${YELLOW}Generating random app name...${NC}"
    APP_NAME="spring-glossy-$(date +%s)"
fi

echo -e "${GREEN}Using app name: $APP_NAME${NC}"

# Check if Heroku CLI is installed
if ! command -v heroku &> /dev/null; then
    echo -e "${RED}❌ Heroku CLI is not installed. Please install it first.${NC}"
    echo "Visit: https://devcenter.heroku.com/articles/heroku-cli"
    exit 1
fi

# Check if logged in to Heroku
if ! heroku auth:whoami &> /dev/null; then
    echo -e "${YELLOW}Please log in to Heroku...${NC}"
    heroku login
fi

# Create Heroku app if it doesn't exist
if ! heroku apps:info --app "$APP_NAME" &> /dev/null; then
    echo -e "${BLUE}Creating new Heroku app: $APP_NAME${NC}"
    heroku create "$APP_NAME"
else
    echo -e "${GREEN}App $APP_NAME already exists${NC}"
fi

# Add Heroku remote if not already added
if ! git remote | grep -q heroku; then
    echo -e "${BLUE}Adding Heroku remote...${NC}"
    heroku git:remote -a "$APP_NAME"
fi

# Set up PostgreSQL database
echo -e "${BLUE}🗄️ Setting up PostgreSQL database...${NC}"
heroku addons:create heroku-postgresql:essential-0 --app "$APP_NAME"

# Configure SSL for PostgreSQL
echo -e "${BLUE}🔒 Configuring SSL for PostgreSQL...${NC}"
heroku config:set DB_SSLMODE=require --app "$APP_NAME"

# Set basic Laravel configuration
echo -e "${BLUE}⚙️ Setting basic Laravel configuration...${NC}"
heroku config:set \
    APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stack \
    DB_CONNECTION=pgsql \
    CACHE_DRIVER=redis \
    SESSION_DRIVER=redis \
    QUEUE_CONNECTION=redis \
    --app "$APP_NAME"

# Set up Redis for caching and queues
echo -e "${BLUE}🔴 Setting up Redis...${NC}"
heroku addons:create heroku-redis:mini --app "$APP_NAME"

# Set Resend email configuration
echo "📧 Setting up Resend email configuration..."
read -p "Enter Resend API Key: " RESEND_API_KEY
heroku config:set \
    MAIL_MAILER=smtp \
    MAIL_HOST=smtp.resend.com \
    MAIL_PORT=587 \
    MAIL_USERNAME=resend \
    MAIL_PASSWORD="$RESEND_API_KEY" \
    MAIL_ENCRYPTION=tls \
    MAIL_FROM_ADDRESS=noreply@springglossy.com.ng \
    MAIL_FROM_NAME="Spring Glossy Cosmetics" \
    --app $APP_NAME

# Set AWS S3 configuration
echo "☁️ Setting up AWS S3 configuration..."
read -p "Enter AWS Access Key ID: " AWS_ACCESS_KEY_ID
read -p "Enter AWS Secret Access Key: " AWS_SECRET_ACCESS_KEY
read -p "Enter AWS Bucket Name: " AWS_BUCKET
read -p "Enter AWS Region (default: us-east-1): " AWS_REGION
AWS_REGION=${AWS_REGION:-us-east-1}

heroku config:set \
    AWS_ACCESS_KEY_ID="$AWS_ACCESS_KEY_ID" \
    AWS_SECRET_ACCESS_KEY="$AWS_SECRET_ACCESS_KEY" \
    AWS_DEFAULT_REGION="$AWS_REGION" \
    AWS_BUCKET="$AWS_BUCKET" \
    AWS_URL="https://$AWS_BUCKET.s3.$AWS_REGION.amazonaws.com" \
    AWS_USE_PATH_STYLE_ENDPOINT=true \
    --app $APP_NAME

# Set application URLs
echo "🌐 Setting application URLs..."
heroku config:set \
    FRONTEND_URL=https://springglossy.com \
    ADMIN_EMAIL=kemisolajim2018@gmail.com \
    --app $APP_NAME

# Prompt for payment gateway credentials
echo "💳 Payment Gateway Configuration:"
read -p "Enter Paystack Public Key (or press Enter to skip): " PAYSTACK_PUBLIC
read -p "Enter Paystack Secret Key (or press Enter to skip): " PAYSTACK_SECRET
read -p "Enter Monnify Public Key (or press Enter to skip): " MONNIFY_PUBLIC
read -p "Enter Monnify Secret Key (or press Enter to skip): " MONNIFY_SECRET
read -p "Enter Monnify Merchant ID (or press Enter to skip): " MONNIFY_MERCHANT
read -p "Enter Monnify Contract Code (or press Enter to skip): " MONNIFY_CONTRACT

# Set payment gateway configs if provided
if [ ! -z "$PAYSTACK_PUBLIC" ]; then
    heroku config:set PAYSTACK_PUBLIC_KEY="$PAYSTACK_PUBLIC" --app $APP_NAME
fi

if [ ! -z "$PAYSTACK_SECRET" ]; then
    heroku config:set PAYSTACK_SECRET_KEY="$PAYSTACK_SECRET" --app $APP_NAME
fi

if [ ! -z "$MONNIFY_PUBLIC" ]; then
    heroku config:set MONNIFY_PUBLIC_KEY="$MONNIFY_PUBLIC" --app $APP_NAME
fi

if [ ! -z "$MONNIFY_SECRET" ]; then
    heroku config:set MONNIFY_SECRET_KEY="$MONNIFY_SECRET" --app $APP_NAME
fi

if [ ! -z "$MONNIFY_MERCHANT" ]; then
    heroku config:set MONNIFY_MERCHANT_ID="$MONNIFY_MERCHANT" --app $APP_NAME
fi

if [ ! -z "$MONNIFY_CONTRACT" ]; then
    heroku config:set MONNIFY_CONTRACT_CODE="$MONNIFY_CONTRACT" --app $APP_NAME
fi

# Deploy to Heroku
echo "🚀 Deploying to Heroku..."
git add .
git commit -m "Deploy to Heroku - Spring Glossy Cosmetics with S3 and Resend"
git push heroku main

# Run migrations
echo "🗄️ Running database migrations..."
heroku run php artisan migrate --force --app $APP_NAME

# Note: storage:link not needed for S3 storage
echo "📁 S3 storage configured - no storage link needed"

# Clear and cache config
echo "⚡ Optimizing application..."
heroku run php artisan config:cache --app $APP_NAME
heroku run php artisan route:cache --app $APP_NAME
heroku run php artisan view:cache --app $APP_NAME

# Set up worker dyno for queues
echo "👷 Setting up worker dyno for queues..."
heroku ps:scale worker=1 --app $APP_NAME

# Open the app
echo "🌐 Opening your application..."
heroku open --app $APP_NAME

echo "✅ Deployment completed successfully!"
echo "📊 Your app is now live at: https://$APP_NAME.herokuapp.com"
echo ""
echo "🔧 Next steps:"
echo "1. Configure webhook URLs in your payment gateway dashboards:"
echo "   - Paystack: https://$APP_NAME.herokuapp.com/api/webhooks/paystack"
echo "   - Monnify: https://$APP_NAME.herokuapp.com/api/webhooks/monnify"
echo ""
echo "2. Set up your domain (optional):"
echo "   heroku domains:add springglossy.com --app $APP_NAME"
echo ""
echo "3. Monitor your app:"
echo "   heroku logs --tail --app $APP_NAME"
echo ""
echo "4. Access admin panel:"
echo "   https://$APP_NAME.herokuapp.com/admin"
echo ""
echo "🎉 Happy coding!"
