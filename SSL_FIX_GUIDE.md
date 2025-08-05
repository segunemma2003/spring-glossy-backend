# 🔒 SSL Fix Guide for Heroku Deployment

This guide will help you fix SSL certificate verification issues on Heroku while maintaining your Redis and database installations.

## 🚨 Common SSL Issues on Heroku

### 1. Database SSL Connection Issues

-   **Error**: `SSL connection required`
-   **Cause**: PostgreSQL requires SSL connections on Heroku
-   **Solution**: Set `DB_SSLMODE=require`

### 2. Redis SSL Connection Issues

-   **Error**: `Redis connection failed`
-   **Cause**: Redis client not configured for SSL
-   **Solution**: Use `REDIS_CLIENT=predis`

### 3. Application SSL Issues

-   **Error**: `Certificate verification failed`
-   **Cause**: Missing SSL configuration
-   **Solution**: Proper environment variables

## 🛠️ Quick Fix Scripts

### 1. Check Current SSL Status

```bash
./check_ssl.sh
```

This script will:

-   Check your current SSL configuration
-   Test database and Redis connections
-   Identify any SSL-related issues
-   Provide a summary of what needs to be fixed

### 2. Fix SSL Issues

```bash
./fix_ssl.sh
```

This script will:

-   Set `DB_SSLMODE=require` for PostgreSQL
-   Configure Redis for SSL compatibility
-   Clear and rebuild all caches
-   Test connections after fixes
-   Restart your application

### 3. Deploy with SSL Support

```bash
./deploy.sh
```

This script will:

-   Create a new Heroku app with SSL support
-   Set up PostgreSQL and Redis with SSL
-   Configure all environment variables
-   Deploy your application
-   Test SSL connections

## 🔧 Manual SSL Configuration

### Database SSL Configuration

1. **Set PostgreSQL SSL Mode**:

```bash
heroku config:set DB_SSLMODE=require --app your-app-name
```

2. **Update Database Configuration**:
   The `config/database.php` file has been updated to use:

```php
'pgsql' => [
    'driver' => 'pgsql',
    'url' => env('DATABASE_URL'),
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '5432'),
    'database' => env('DB_DATABASE', 'laravel'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => env('DB_CHARSET', 'utf8'),
    'prefix' => '',
    'prefix_indexes' => true,
    'search_path' => 'public',
    'sslmode' => env('DB_SSLMODE', 'require'),
    'options' => [
        PDO::ATTR_PERSISTENT => true,
    ],
],
```

### Redis SSL Configuration

1. **Set Redis Client**:

```bash
heroku config:set REDIS_CLIENT=predis --app your-app-name
```

2. **Ensure Redis Add-on is Installed**:

```bash
heroku addons:create heroku-redis:mini --app your-app-name
```

### Application Configuration

1. **Set Production Environment**:

```bash
heroku config:set \
    APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stack \
    DB_CONNECTION=pgsql \
    CACHE_DRIVER=redis \
    SESSION_DRIVER=redis \
    QUEUE_CONNECTION=redis \
    --app your-app-name
```

2. **Clear and Rebuild Caches**:

```bash
heroku run php artisan config:clear --app your-app-name
heroku run php artisan cache:clear --app your-app-name
heroku run php artisan route:clear --app your-app-name
heroku run php artisan view:clear --app your-app-name
heroku run php artisan config:cache --app your-app-name
heroku run php artisan route:cache --app your-app-name
heroku run php artisan view:cache --app your-app-name
```

3. **Restart Application**:

```bash
heroku restart --app your-app-name
```

## 🧪 Testing SSL Connections

### Test Database Connection

```bash
heroku run php artisan tinker --execute="echo 'Database connection successful';" --app your-app-name
```

### Test Redis Connection

```bash
heroku run php artisan tinker --execute="Redis::ping(); echo 'Redis connection successful';" --app your-app-name
```

### Check Configuration

```bash
heroku config --app your-app-name
```

## 📊 Monitoring SSL Status

### Check Logs for SSL Errors

```bash
heroku logs --tail --app your-app-name | grep -i "ssl\|certificate\|tls"
```

### Monitor Add-ons

```bash
heroku addons --app your-app-name
```

### Check Database Status

```bash
heroku pg:info --app your-app-name
```

### Check Redis Status

```bash
heroku redis:info --app your-app-name
```

## 🔄 Maintaining Redis and Database

### Database Maintenance

1. **Backup Database**:

```bash
heroku pg:backups:capture --app your-app-name
```

2. **Download Backup**:

```bash
heroku pg:backups:download --app your-app-name
```

3. **Restore Database**:

```bash
heroku pg:backups:restore b001 DATABASE_URL --app your-app-name
```

4. **Run Migrations**:

```bash
heroku run php artisan migrate --force --app your-app-name
```

### Redis Maintenance

1. **Check Redis Memory**:

```bash
heroku redis:info --app your-app-name
```

2. **Clear Redis Cache**:

```bash
heroku run php artisan cache:clear --app your-app-name
```

3. **Monitor Redis Usage**:

```bash
heroku logs --tail --dyno=worker --app your-app-name
```

## 🚀 Deployment Checklist

-   [ ] Heroku CLI installed and logged in
-   [ ] App created on Heroku
-   [ ] PostgreSQL add-on added with SSL
-   [ ] Redis add-on added with SSL
-   [ ] `DB_SSLMODE=require` set
-   [ ] `REDIS_CLIENT=predis` set
-   [ ] Production environment variables configured
-   [ ] Application deployed
-   [ ] Database migrations run
-   [ ] Caches cleared and rebuilt
-   [ ] Application restarted
-   [ ] SSL connections tested
-   [ ] Worker dyno scaled for queues

## 🆘 Troubleshooting

### SSL Connection Still Failing

1. **Check DATABASE_URL**:

```bash
heroku config:get DATABASE_URL --app your-app-name
```

2. **Verify SSL Mode**:

```bash
heroku config:get DB_SSLMODE --app your-app-name
```

3. **Check Redis URL**:

```bash
heroku config:get REDIS_URL --app your-app-name
```

### Database Connection Issues

1. **Reset Database** (⚠️ **WARNING**: This will delete all data):

```bash
heroku pg:reset DATABASE_URL --app your-app-name
heroku run php artisan migrate --force --app your-app-name
```

2. **Check Database Status**:

```bash
heroku pg:info --app your-app-name
```

### Redis Connection Issues

1. **Restart Redis**:

```bash
heroku redis:restart --app your-app-name
```

2. **Check Redis Status**:

```bash
heroku redis:info --app your-app-name
```

## 📞 Support

If you're still experiencing SSL issues after following this guide:

1. Check the Heroku logs: `heroku logs --tail --app your-app-name`
2. Verify your add-ons are properly configured
3. Ensure all environment variables are set correctly
4. Test connections manually using the provided commands

## 🎉 Success Indicators

Your SSL configuration is working correctly when:

-   ✅ Database connection test passes
-   ✅ Redis connection test passes
-   ✅ No SSL-related errors in logs
-   ✅ Application loads without SSL warnings
-   ✅ All API endpoints respond correctly
-   ✅ Payment webhooks work properly

---

**Remember**: SSL is required for all database and Redis connections on Heroku. The scripts provided will ensure your application is properly configured for secure connections.
