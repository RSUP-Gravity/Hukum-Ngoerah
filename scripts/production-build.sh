#!/bin/bash

# =============================================================================
# RS Ngoerah Legal Document Management - Production Build Script
# =============================================================================
# This script prepares the application for production deployment
# Run this script on your production server after deployment
# =============================================================================

set -e

echo "🚀 Starting Production Build Process..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo -e "${RED}Error: artisan file not found. Please run this script from the Laravel root directory.${NC}"
    exit 1
fi

echo ""
echo "📦 Step 1: Installing Composer Dependencies (Production)..."
echo "=============================================="
composer install --no-dev --optimize-autoloader --no-interaction

echo ""
echo "📦 Step 2: Installing NPM Dependencies..."
echo "=============================================="
npm ci --production=false

echo ""
echo "🎨 Step 3: Building Frontend Assets..."
echo "=============================================="
npm run build

echo ""
echo "⚙️ Step 4: Optimizing Configuration..."
echo "=============================================="

# Clear all caches first
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Generate optimized class loader
php artisan optimize

# Cache configuration
php artisan config:cache
echo -e "${GREEN}✓ Configuration cached${NC}"

# Cache routes
php artisan route:cache
echo -e "${GREEN}✓ Routes cached${NC}"

# Cache views
php artisan view:cache
echo -e "${GREEN}✓ Views cached${NC}"

# Cache events
php artisan event:cache
echo -e "${GREEN}✓ Events cached${NC}"

echo ""
echo "🔗 Step 5: Setting Up Storage..."
echo "=============================================="

# Create storage link if not exists
if [ ! -L "public/storage" ]; then
    php artisan storage:link
    echo -e "${GREEN}✓ Storage link created${NC}"
else
    echo -e "${YELLOW}Storage link already exists${NC}"
fi

# Create necessary directories
mkdir -p storage/app/documents
mkdir -p storage/app/temp
mkdir -p storage/app/public/avatars

# Set proper permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache

echo ""
echo "🗄️ Step 6: Database Migrations..."
echo "=============================================="

# Run migrations (with confirmation in production)
if [ "$1" == "--force" ]; then
    php artisan migrate --force
    echo -e "${GREEN}✓ Migrations completed${NC}"
else
    echo -e "${YELLOW}Skipping migrations. Run with --force flag to execute migrations.${NC}"
    echo "  Usage: ./scripts/production-build.sh --force"
fi

echo ""
echo "🔐 Step 7: Security Checks..."
echo "=============================================="

# Check APP_DEBUG
if grep -q "APP_DEBUG=true" .env; then
    echo -e "${RED}⚠ WARNING: APP_DEBUG is set to true. Set it to false for production!${NC}"
else
    echo -e "${GREEN}✓ APP_DEBUG is properly configured${NC}"
fi

# Check APP_ENV
if grep -q "APP_ENV=production" .env; then
    echo -e "${GREEN}✓ APP_ENV is set to production${NC}"
else
    echo -e "${YELLOW}⚠ NOTE: APP_ENV is not set to production${NC}"
fi

# Check if APP_KEY exists
if grep -q "APP_KEY=base64:" .env; then
    echo -e "${GREEN}✓ APP_KEY is set${NC}"
else
    echo -e "${RED}⚠ WARNING: APP_KEY is not set. Generate one with: php artisan key:generate${NC}"
fi

echo ""
echo "📊 Step 8: Final Checks..."
echo "=============================================="

# Show application status
php artisan about --only=environment,cache,drivers 2>/dev/null || echo "Application status check skipped"

echo ""
echo "=============================================="
echo -e "${GREEN}✅ Production Build Complete!${NC}"
echo "=============================================="
echo ""
echo "Next steps:"
echo "  1. Ensure .env file has production settings"
echo "  2. Configure web server (Nginx/Apache)"
echo "  3. Set up SSL certificate"
echo "  4. Configure supervisor for queue workers"
echo "  5. Set up cron for scheduled tasks"
echo ""
echo "For queue worker setup, add this to supervisor:"
echo "  php artisan queue:work redis --sleep=3 --tries=3"
echo ""
echo "For scheduler, add this to crontab:"
echo "  * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1"
echo ""
