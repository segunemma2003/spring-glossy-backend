#!/bin/bash

# 🔒 SSL Certificate Verification Fix - Complete Solution
# Spring Glossy Cosmetics Laravel Application on Heroku

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${BLUE}🔒 SSL Certificate Verification Fix - Complete Solution${NC}"
echo "================================================================"

# Get app name
read -p "Enter your Heroku app name: " APP_NAME

if [ -z "$APP_NAME" ]; then
    echo -e "${RED}❌ App name is required${NC}"
    exit 1
fi

echo -e "${BLUE}📋 Starting SSL fix for app: ${APP_NAME}${NC}"

# Step 1: Set Production Environment Variables
echo -e "${YELLOW}🔧 Step 1: Setting production environment variables...${NC}"
heroku config:set \
    APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stack \
    DB_CONNECTION=pgsql \
    DB_SSLMODE=require \
    CACHE_DRIVER=redis \
    SESSION_DRIVER=redis \
    QUEUE_CONNECTION=redis \
    REDIS_CLIENT=predis \
    HTTP_VERIFY_SSL=true \
    HTTP_TIMEOUT=30 \
    HTTP_CONNECT_TIMEOUT=10 \
    --app "$APP_NAME"

# Step 2: Set SSL Certificate Paths
echo -e "${YELLOW}🔧 Step 2: Setting SSL certificate paths...${NC}"
heroku config:set \
    CURL_CA_BUNDLE="/usr/lib/ssl/certs/ca-certificates.crt" \
    SSL_CERT_FILE="/usr/lib/ssl/certs/ca-certificates.crt" \
    --app "$APP_NAME"

# Step 3: Install CA Bundle Dependency
echo -e "${YELLOW}🔧 Step 3: Installing CA bundle dependency...${NC}"
heroku run "composer require composer/ca-bundle --no-scripts" --app "$APP_NAME"

# Step 4: Clear and Rebuild Caches
echo -e "${YELLOW}🔧 Step 4: Clearing and rebuilding caches...${NC}"
heroku run "php artisan config:clear" --app "$APP_NAME"
heroku run "php artisan cache:clear" --app "$APP_NAME"
heroku run "php artisan route:clear" --app "$APP_NAME"
heroku run "php artisan view:clear" --app "$APP_NAME"
heroku run "php artisan config:cache" --app "$APP_NAME"
heroku run "php artisan route:cache" --app "$APP_NAME"

# Step 5: Restart Application
echo -e "${YELLOW}🔧 Step 5: Restarting application...${NC}"
heroku restart --app "$APP_NAME"

# Step 6: Test Connections
echo -e "${YELLOW}🔧 Step 6: Testing connections...${NC}"

echo -e "${BLUE}Testing Database Connection...${NC}"
heroku run "php artisan tinker --execute=\"try { DB::connection()->getPdo(); echo '✅ Database connected successfully'; } catch (Exception \$e) { echo '❌ Database connection failed: ' . \$e->getMessage(); }\"" --app "$APP_NAME"

echo -e "${BLUE}Testing Redis Connection...${NC}"
heroku run "php artisan tinker --execute=\"try { Redis::ping(); echo '✅ Redis connected successfully'; } catch (Exception \$e) { echo '❌ Redis connection failed: ' . \$e->getMessage(); }\"" --app "$APP_NAME"

echo -e "${BLUE}Testing HTTP SSL Client...${NC}"
heroku run "php artisan tinker --execute=\"try { Http::get('https://httpbin.org/get'); echo '✅ HTTP SSL working'; } catch (Exception \$e) { echo '❌ HTTP SSL failed: ' . \$e->getMessage(); }\"" --app "$APP_NAME"

# Step 7: Scale Worker Dynos
echo -e "${YELLOW}🔧 Step 7: Scaling worker dynos...${NC}"
heroku ps:scale worker=1 --app "$APP_NAME"

# Step 8: Final Configuration Check
echo -e "${YELLOW}🔧 Step 8: Final configuration check...${NC}"
echo -e "${BLUE}Current configuration:${NC}"
heroku config --app "$APP_NAME" | grep -E "(APP_ENV|DB_SSLMODE|REDIS_CLIENT|HTTP_VERIFY_SSL|CURL_CA_BUNDLE)"

echo -e "${GREEN}✅ SSL fix completed successfully!${NC}"
echo ""
echo -e "${BLUE}🔗 Your application URLs:${NC}"
echo -e "   Main App: https://${APP_NAME}.herokuapp.com/"
echo -e "   Admin Panel: https://${APP_NAME}.herokuapp.com/admin"
echo ""
echo -e "${BLUE}📊 Monitor your application:${NC}"
echo -e "   heroku logs --tail --app ${APP_NAME}"
echo ""
echo -e "${BLUE}🔧 Next steps:${NC}"
echo -e "   1. Test your application endpoints"
echo -e "   2. Configure webhook URLs in payment gateways"
echo -e "   3. Monitor logs for any remaining SSL issues"
echo ""
echo -e "${GREEN}🎉 SSL certificate verification should now work properly!${NC}"
