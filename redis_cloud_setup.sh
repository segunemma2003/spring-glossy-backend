#!/bin/bash

# 🔄 Redis Cloud Setup Script
# Spring Glossy Cosmetics Laravel Application

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${BLUE}🔄 Redis Cloud Setup - Alternative Redis Server${NC}"
echo "======================================================"

# Get app name
read -p "Enter your Heroku app name: " APP_NAME

if [ -z "$APP_NAME" ]; then
    echo -e "${RED}❌ App name is required${NC}"
    exit 1
fi

echo -e "${BLUE}📋 Setting up Redis Cloud for app: ${APP_NAME}${NC}"

echo -e "${YELLOW}🔧 Step 1: Redis Cloud Configuration${NC}"
echo -e "${BLUE}Please provide your Redis Cloud credentials:${NC}"

read -p "Enter Redis Cloud Host: " REDIS_HOST
read -p "Enter Redis Cloud Port (usually 6379 or 6380): " REDIS_PORT
read -p "Enter Redis Cloud Username: " REDIS_USERNAME
read -p "Enter Redis Cloud Password: " REDIS_PASSWORD
read -p "Enter Redis Cloud Database (0-15): " REDIS_DB

# Validate inputs
if [ -z "$REDIS_HOST" ] || [ -z "$REDIS_PORT" ] || [ -z "$REDIS_USERNAME" ] || [ -z "$REDIS_PASSWORD" ]; then
    echo -e "${RED}❌ All Redis Cloud credentials are required${NC}"
    exit 1
fi

# Set Redis Cloud environment variables
echo -e "${YELLOW}🔧 Step 2: Setting Redis Cloud environment variables...${NC}"
heroku config:set \
    REDIS_HOST="$REDIS_HOST" \
    REDIS_PORT="$REDIS_PORT" \
    REDIS_USERNAME="$REDIS_USERNAME" \
    REDIS_PASSWORD="$REDIS_PASSWORD" \
    REDIS_DB="$REDIS_DB" \
    REDIS_CACHE_DB="$REDIS_DB" \
    REDIS_SCHEME="tcp" \
    REDIS_SSL_VERIFY_PEER="false" \
    REDIS_SSL_VERIFY_PEER_NAME="false" \
    REDIS_SSL_ALLOW_SELF_SIGNED="true" \
    --app "$APP_NAME"

# Remove Heroku Redis URL if it exists
echo -e "${YELLOW}🔧 Step 3: Removing Heroku Redis URL...${NC}"
heroku config:unset REDIS_URL --app "$APP_NAME" 2>/dev/null || echo "REDIS_URL not found, skipping..."

# Test Redis connection
echo -e "${YELLOW}🔧 Step 4: Testing Redis Cloud connection...${NC}"
heroku run "php artisan tinker --execute=\"try { Redis::ping(); echo '✅ Redis Cloud connected successfully'; } catch (Exception \$e) { echo '❌ Redis Cloud connection failed: ' . \$e->getMessage(); }\"" --app "$APP_NAME"

# Clear and rebuild caches
echo -e "${YELLOW}🔧 Step 5: Clearing and rebuilding caches...${NC}"
heroku run "php artisan config:clear" --app "$APP_NAME"
heroku run "php artisan cache:clear" --app "$APP_NAME"
heroku run "php artisan config:cache" --app "$APP_NAME"

# Restart application
echo -e "${YELLOW}🔧 Step 6: Restarting application...${NC}"
heroku restart --app "$APP_NAME"

echo -e "${GREEN}✅ Redis Cloud setup completed!${NC}"
echo ""
echo -e "${BLUE}🔗 Your application URLs:${NC}"
echo -e "   Main App: https://${APP_NAME}.herokuapp.com/"
echo -e "   Admin Panel: https://${APP_NAME}.herokuapp.com/admin"
echo ""
echo -e "${BLUE}📊 Monitor your application:${NC}"
echo -e "   heroku logs --tail --app ${APP_NAME}"
echo ""
echo -e "${BLUE}🔧 Redis Cloud Configuration:${NC}"
echo -e "   Host: ${REDIS_HOST}"
echo -e "   Port: ${REDIS_PORT}"
echo -e "   Database: ${REDIS_DB}"
echo ""
echo -e "${GREEN}🎉 Your application is now using Redis Cloud!${NC}"
