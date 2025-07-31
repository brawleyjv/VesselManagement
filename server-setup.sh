#!/bin/bash
# Server Setup Script for logicdock.org
# Run this on your web server after uploading files

echo "🚀 Setting up Vessel Data Logger server..."

# Create necessary directories
echo "📁 Creating directories..."
mkdir -p api
mkdir -p dist
mkdir -p logs
mkdir -p data

# Set proper permissions
echo "🔒 Setting permissions..."
chmod 755 api
chmod 700 dist    # Protected directory
chmod 755 logs
chmod 755 data

# Make log files writable
chmod 666 logs/ 2>/dev/null || echo "No log files yet (normal)"

# Create empty database file with proper permissions
touch data/licenses.sqlite
chmod 666 data/licenses.sqlite

# Create index file for protected directories
echo "Forbidden" > dist/index.html
echo "Forbidden" > logs/index.html
echo "Forbidden" > data/index.html

# Test PHP functionality
echo "🧪 Testing PHP..."
php -v || echo "⚠️  PHP not found - install PHP 7.4+ on your server"

# Test SQLite
echo "🗄️  Testing SQLite..."
php -m | grep sqlite || echo "⚠️  SQLite extension not found"

echo "✅ Server setup complete!"
echo ""
echo "📋 Next steps:"
echo "1. Upload all files to your web server"
echo "2. Test: https://logicdock.org/landing.html"
echo "3. Test API: https://logicdock.org/api/validate-license.php"
echo "4. Check SSL: Verify green lock in browser"
echo ""
echo "🎯 Ready for testing!"
