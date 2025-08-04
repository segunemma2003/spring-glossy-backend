#!/bin/bash

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${BLUE}🔒 Fixing SSL Certificate Verification Issues${NC}"
echo "=================================================="

# Get app name
read -p "Enter your Heroku app name: " APP_NAME

if [ -z "$APP_NAME" ]; then
    echo -e "${RED}❌ App name is required${NC}"
    exit 1
fi

echo -e "${BLUE}🔧 Configuring SSL settings for $APP_NAME...${NC}"

# Set SSL mode for PostgreSQL
echo -e "${YELLOW}Setting DB_SSLMODE=require...${NC}"
heroku config:set DB_SSLMODE=require --app "$APP_NAME"

# Set basic Laravel configuration
echo -e "${YELLOW}Setting basic Laravel configuration...${NC}"
heroku config:set \
    APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stack \
    DB_CONNECTION=pgsql \
    CACHE_DRIVER=redis \
    SESSION_DRIVER=redis \
    QUEUE_CONNECTION=redis \
    --app "$APP_NAME"

# Clear and rebuild cache
echo -e "${YELLOW}Clearing and rebuilding cache...${NC}"
heroku run php artisan config:clear --app "$APP_NAME"
heroku run php artisan cache:clear --app "$APP_NAME"
heroku run php artisan config:cache --app "$APP_NAME"
heroku run php artisan route:cache --app "$APP_NAME"
heroku run php artisan view:cache --app "$APP_NAME"

# Restart the application
echo -e "${YELLOW}Restarting application...${NC}"
heroku restart --app "$APP_NAME"

echo -e "${GREEN}✅ SSL configuration updated successfully!${NC}"
echo ""
echo -e "${BLUE}📋 Next steps:${NC}"
echo "1. Check your application logs: heroku logs --tail --app $APP_NAME"
echo "2. Test database connection: heroku run php artisan tinker --app $APP_NAME"
echo "3. Run migrations if needed: heroku run php artisan migrate --force --app $APP_NAME"
echo ""
echo -e "${GREEN}🎉 Your application should now work without SSL errors!${NC}"
