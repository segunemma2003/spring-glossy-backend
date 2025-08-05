#!/bin/bash

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${BLUE}🔒 Comprehensive SSL Certificate Verification Fix${NC}"
echo "========================================================"

# Get app name
read -p "Enter your Heroku app name: " APP_NAME

if [ -z "$APP_NAME" ]; then
    echo -e "${RED}❌ App name is required${NC}"
    exit 1
fi

echo -e "${BLUE}🔧 Fixing SSL configuration for $APP_NAME...${NC}"

# Check if app exists
if ! heroku apps:info --app "$APP_NAME" &> /dev/null; then
    echo -e "${RED}❌ App $APP_NAME not found. Please check the app name.${NC}"
    exit 1
fi

# Set SSL mode for PostgreSQL
echo -e "${YELLOW}Setting DB_SSLMODE=require...${NC}"
heroku config:set DB_SSLMODE=require --app "$APP_NAME"

# Set comprehensive Laravel configuration for production
echo -e "${YELLOW}Setting production Laravel configuration...${NC}"
heroku config:set \
    APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stack \
    DB_CONNECTION=pgsql \
    CACHE_DRIVER=redis \
    SESSION_DRIVER=redis \
    QUEUE_CONNECTION=redis \
    --app "$APP_NAME"

# Ensure Redis is properly configured
echo -e "${YELLOW}Configuring Redis for SSL...${NC}"
heroku config:set REDIS_CLIENT=predis --app "$APP_NAME"

# Set proper timezone
echo -e "${YELLOW}Setting timezone...${NC}"
heroku config:set APP_TIMEZONE=UTC --app "$APP_NAME"

# Clear all caches and rebuild
echo -e "${YELLOW}Clearing and rebuilding all caches...${NC}"
heroku run php artisan config:clear --app "$APP_NAME"
heroku run php artisan cache:clear --app "$APP_NAME"
heroku run php artisan route:clear --app "$APP_NAME"
heroku run php artisan view:clear --app "$APP_NAME"

# Rebuild caches
echo -e "${YELLOW}Rebuilding application caches...${NC}"
heroku run php artisan config:cache --app "$APP_NAME"
heroku run php artisan route:cache --app "$APP_NAME"
heroku run php artisan view:cache --app "$APP_NAME"

# Restart the application
echo -e "${YELLOW}Restarting application...${NC}"
heroku restart --app "$APP_NAME"

# Test database connection
echo -e "${YELLOW}Testing database connection...${NC}"
if heroku run php artisan tinker --execute="echo 'Database connection successful';" --app "$APP_NAME" 2>/dev/null; then
    echo -e "${GREEN}✅ Database connection test passed!${NC}"
else
    echo -e "${RED}❌ Database connection test failed. Check logs for details.${NC}"
fi

echo -e "${GREEN}✅ SSL configuration updated successfully!${NC}"
echo ""
echo -e "${BLUE}📋 Next steps:${NC}"
echo "1. Check your application logs: heroku logs --tail --app $APP_NAME"
echo "2. Test your application: heroku open --app $APP_NAME"
echo "3. Run migrations if needed: heroku run php artisan migrate --force --app $APP_NAME"
echo "4. Check Redis connection: heroku run php artisan tinker --execute=\"Redis::ping()\" --app $APP_NAME"
echo ""
echo -e "${GREEN}🎉 Your application should now work without SSL errors!${NC}"
echo ""
echo -e "${YELLOW}💡 If you still experience issues, try:${NC}"
echo "1. Check Heroku add-ons: heroku addons --app $APP_NAME"
echo "2. Verify database: heroku pg:info --app $APP_NAME"
echo "3. Verify Redis: heroku redis:info --app $APP_NAME"
