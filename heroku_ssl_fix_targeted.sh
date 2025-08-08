#!/bin/bash

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${BLUE}🔒 Targeted Heroku SSL Fix for Redis and PostgreSQL${NC}"
echo "========================================================"

APP_NAME="spring-glossy-1754367689"

echo -e "${BLUE}🔧 Applying targeted SSL fixes for $APP_NAME...${NC}"

# Step 1: Configure Redis with self-signed certificate support
echo -e "${YELLOW}1. Configuring Redis for Heroku self-signed certificates...${NC}"
heroku config:set \
    REDIS_CLIENT=predis \
    REDIS_SCHEME=tls \
    REDIS_SSL_VERIFY_PEER=false \
    REDIS_SSL_VERIFY_PEER_NAME=false \
    --app "$APP_NAME"

# Step 2: Configure PostgreSQL SSL with proper mode
echo -e "${YELLOW}2. Configuring PostgreSQL SSL...${NC}"
heroku config:set \
    DB_SSLMODE=require \
    DB_SSL_VERIFY=false \
    --app "$APP_NAME"

# Step 3: Set Laravel Redis configuration to handle self-signed certs
echo -e "${YELLOW}3. Setting Laravel Redis SSL options...${NC}"
heroku config:set \
    REDIS_OPTIONS_SSL_VERIFY_PEER=false \
    REDIS_OPTIONS_SSL_VERIFY_PEER_NAME=false \
    REDIS_OPTIONS_SSL_ALLOW_SELF_SIGNED=true \
    --app "$APP_NAME"

# Step 4: Configure HTTP client to handle self-signed certificates
echo -e "${YELLOW}4. Configuring HTTP client SSL settings...${NC}"
heroku config:set \
    HTTP_VERIFY_SSL=false \
    HTTP_SSL_VERIFY_PEER=false \
    CURL_CA_BUNDLE=/etc/ssl/certs/ca-certificates.crt \
    --app "$APP_NAME"

# Step 5: Clear all caches
echo -e "${YELLOW}5. Clearing all caches...${NC}"
heroku run php artisan config:clear --app "$APP_NAME"
heroku run php artisan cache:clear --app "$APP_NAME"
heroku run php artisan route:clear --app "$APP_NAME"
heroku run php artisan view:clear --app "$APP_NAME"

# Step 6: Restart the application
echo -e "${YELLOW}6. Restarting application...${NC}"
heroku restart --app "$APP_NAME"

# Step 7: Wait for restart
echo -e "${YELLOW}7. Waiting for application to restart...${NC}"
sleep 15

# Step 8: Test connections with more detailed error reporting
echo -e "${YELLOW}8. Testing connections with detailed error reporting...${NC}"

# Test database connection
echo -e "${BLUE}Testing database connection...${NC}"
heroku run php artisan tinker --execute="
try {
    \$pdo = \DB::connection()->getPdo();
    echo 'Database SSL connection successful - PDO connected';
} catch(Exception \$e) {
    echo 'Database connection failed: ' . \$e->getMessage();
}" --app "$APP_NAME"

# Test Redis connection
echo -e "${BLUE}Testing Redis connection...${NC}"
heroku run php artisan tinker --execute="
try {
    \Redis::ping();
    echo 'Redis SSL connection successful - PING responded';
} catch(Exception \$e) {
    echo 'Redis connection failed: ' . \$e->getMessage();
}" --app "$APP_NAME"

# Test HTTP client
echo -e "${BLUE}Testing HTTP client...${NC}"
heroku run php artisan tinker --execute="
try {
    \$response = \Http::timeout(10)->get('https://httpbin.org/get');
    echo 'HTTP SSL client working - Status: ' . \$response->status();
} catch(Exception \$e) {
    echo 'HTTP SSL failed: ' . \$e->getMessage();
}" --app "$APP_NAME"

echo ""
echo -e "${GREEN}✅ Targeted SSL fixes applied!${NC}"
echo ""
echo -e "${BLUE}📋 Applied configurations:${NC}"
echo "   - Redis: Configured for self-signed certificates"
echo "   - PostgreSQL: SSL required but peer verification disabled"
echo "   - HTTP Client: Configured for Heroku environment"
echo ""
echo -e "${BLUE}🔍 Check application now:${NC}"
echo "   heroku open --app $APP_NAME"
echo "   heroku logs --tail --app $APP_NAME"
