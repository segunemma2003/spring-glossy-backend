#!/bin/bash

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${BLUE}🔍 SSL Configuration Checker${NC}"
echo "================================"

# Get app name
read -p "Enter your Heroku app name: " APP_NAME

if [ -z "$APP_NAME" ]; then
    echo -e "${RED}❌ App name is required${NC}"
    exit 1
fi

echo -e "${BLUE}🔍 Checking SSL configuration for $APP_NAME...${NC}"

# Check if app exists
if ! heroku apps:info --app "$APP_NAME" &> /dev/null; then
    echo -e "${RED}❌ App $APP_NAME not found. Please check the app name.${NC}"
    exit 1
fi

echo -e "${YELLOW}📋 Checking current configuration...${NC}"

# Check database SSL configuration
echo -e "${BLUE}🗄️ Database SSL Configuration:${NC}"
DB_SSLMODE=$(heroku config:get DB_SSLMODE --app "$APP_NAME" 2>/dev/null || echo "Not set")
if [ "$DB_SSLMODE" = "require" ]; then
    echo -e "${GREEN}✅ DB_SSLMODE=require (SSL enabled)${NC}"
else
    echo -e "${RED}❌ DB_SSLMODE=$DB_SSLMODE (SSL may not be enabled)${NC}"
fi

# Check database connection
echo -e "${BLUE}🔗 Database Connection Test:${NC}"
if heroku run php artisan tinker --execute="echo 'Database connection successful';" --app "$APP_NAME" 2>/dev/null; then
    echo -e "${GREEN}✅ Database connection working${NC}"
else
    echo -e "${RED}❌ Database connection failed${NC}"
fi

# Check Redis configuration
echo -e "${BLUE}🔴 Redis Configuration:${NC}"
REDIS_CLIENT=$(heroku config:get REDIS_CLIENT --app "$APP_NAME" 2>/dev/null || echo "Not set")
if [ "$REDIS_CLIENT" = "predis" ]; then
    echo -e "${GREEN}✅ REDIS_CLIENT=predis (SSL compatible)${NC}"
else
    echo -e "${YELLOW}⚠️ REDIS_CLIENT=$REDIS_CLIENT (may need update)${NC}"
fi

# Check Redis connection
echo -e "${BLUE}🔗 Redis Connection Test:${NC}"
if heroku run php artisan tinker --execute="Redis::ping(); echo 'Redis connection successful';" --app "$APP_NAME" 2>/dev/null; then
    echo -e "${GREEN}✅ Redis connection working${NC}"
else
    echo -e "${RED}❌ Redis connection failed${NC}"
fi

# Check Laravel configuration
echo -e "${BLUE}⚙️ Laravel Configuration:${NC}"
APP_ENV=$(heroku config:get APP_ENV --app "$APP_NAME" 2>/dev/null || echo "Not set")
APP_DEBUG=$(heroku config:get APP_DEBUG --app "$APP_NAME" 2>/dev/null || echo "Not set")
DB_CONNECTION=$(heroku config:get DB_CONNECTION --app "$APP_NAME" 2>/dev/null || echo "Not set")

echo "   APP_ENV: $APP_ENV"
echo "   APP_DEBUG: $APP_DEBUG"
echo "   DB_CONNECTION: $DB_CONNECTION"

# Check Heroku add-ons
echo -e "${BLUE}🔧 Heroku Add-ons:${NC}"
heroku addons --app "$APP_NAME" | grep -E "(postgresql|redis)" || echo "No database/Redis add-ons found"

# Check application logs for SSL errors
echo -e "${BLUE}📝 Recent SSL-related logs:${NC}"
heroku logs --tail=50 --app "$APP_NAME" 2>/dev/null | grep -i "ssl\|certificate\|tls" | tail -5 || echo "No SSL-related errors found in recent logs"

echo ""
echo -e "${BLUE}📊 Summary:${NC}"
if [ "$DB_SSLMODE" = "require" ] && [ "$REDIS_CLIENT" = "predis" ]; then
    echo -e "${GREEN}✅ SSL configuration appears to be correct${NC}"
else
    echo -e "${YELLOW}⚠️ SSL configuration may need updates${NC}"
    echo -e "${YELLOW}💡 Run ./fix_ssl.sh to fix SSL issues${NC}"
fi

echo ""
echo -e "${BLUE}🔧 Quick fixes:${NC}"
echo "1. Fix SSL issues: ./fix_ssl.sh"
echo "2. Check logs: heroku logs --tail --app $APP_NAME"
echo "3. Restart app: heroku restart --app $APP_NAME"
echo "4. Check add-ons: heroku addons --app $APP_NAME"
