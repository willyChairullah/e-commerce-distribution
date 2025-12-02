# Setup Instructions

## 🆕 NEW: Regional Distribution System

**Sistem ini sekarang mendukung deployment terdistribusi!**

- **Mode Central**: Database pusat dengan semua data region
- **Mode Regional**: Database regional dengan data satu region saja

📖 **Panduan Lengkap:**

- Baca `REGIONAL_SYSTEM.md` untuk konsep & implementasi
- Baca `QUICKSTART.md` untuk setup cepat

---

## Prerequisites Check

1. **PHP Version**: PHP 7.4 or higher
2. **SQL Server**: SQL Server 2016 or newer
3. **PHP Extensions Required**:
   - sqlsrv
   - pdo_sqlsrv
   - mbstring
   - openssl

## Quick Setup

### 1. Database Setup

```bash
# Open SQL Server Management Studio (SSMS)
# Run the database.sql script to create database and tables
```

Or manually:

```sql
-- Open database.sql and execute all commands
```

### 2. Configure Database Connection

Edit `config/database.php`:

```php
private $host = "localhost";           // Your SQL Server host
private $database = "warehouse_db";    // Database name
private $username = "sa";              // Your username
private $password = "YOUR_PASSWORD";   // Your password
```

### 3. Web Server Setup

#### Option A: Using PHP Built-in Server (Recommended for Development)

```bash
cd public
php -S localhost:8000
```

Then open: http://localhost:8000

#### Option B: Using Apache

1. Configure Apache DocumentRoot to `public/` folder:

```apache
DocumentRoot "C:/path/to/project/public"
<Directory "C:/path/to/project/public">
    AllowOverride All
    Require all granted
</Directory>
```

2. Ensure mod_rewrite is enabled
3. Restart Apache
4. Access: http://localhost/

#### Option C: Using XAMPP/WAMP

1. Copy project folder to `htdocs/` or `www/`
2. Access: http://localhost/project-name/public/

### 4. Test Installation

Open browser and navigate to:

- http://localhost:8000/ (if using PHP built-in server)
- http://localhost/public/ (if using Apache with project in htdocs)

You should see the homepage.

### 5. Login

**Admin Account:**

- Email: admin@example.com
- Password: admin123

**User Account:**

- Email: user@example.com
- Password: user123

## Troubleshooting

### SQL Server Connection Failed

**Problem**: "Database connection failed"

**Solutions**:

1. Check SQL Server service is running
2. Verify credentials in `config/database.php`
3. Check if PHP sqlsrv extension is installed:
   ```bash
   php -m | grep sqlsrv
   ```
4. For Windows, download from: https://docs.microsoft.com/en-us/sql/connect/php/download-drivers-php-sql-server

### CSS/Images Not Loading

**Problem**: Styles not applied or images broken

**Solutions**:

1. If using Apache, ensure .htaccess files are working:
   - Enable mod_rewrite module
   - Set `AllowOverride All` in Apache config
2. If using PHP built-in server from project root:

   ```bash
   # Wrong
   php -S localhost:8000

   # Correct
   cd public
   php -S localhost:8000
   ```

3. Check browser console for 404 errors on assets

### Upload Directory Permissions

**Problem**: Cannot upload product images

**Solution**:

```bash
chmod -R 755 public/assets/img/
# or for development:
chmod -R 777 public/assets/img/
```

### Session Issues

**Problem**: Login not working, session data lost

**Solutions**:

1. Check PHP session directory is writable:
   ```bash
   php -i | grep "session.save_path"
   ```
2. Ensure session_start() is called (already in public/index.php)

## File Structure Summary

```
project/
├── .htaccess                 # Root htaccess (redirects to public/)
├── database.sql              # Database schema & sample data
├── README.md                 # Documentation
├── routes.php                # URL routing configuration
├── config/
│   └── database.php          # Database connection
├── public/                   # Web root (point Apache here)
│   ├── index.php             # Application entry point
│   ├── .htaccess             # URL rewriting rules
│   └── assets/               # Static files (CSS, JS, Images)
├── app/
│   ├── controllers/          # Application controllers
│   └── models/               # Database models
├── views/
│   ├── dashboard/            # Admin panel views
│   ├── client/               # User website views
│   └── auth/                 # Login/Register views
└── helpers/
    ├── csrf.php              # CSRF protection
    └── util.php              # Utility functions
```

## Default URLs

### Public URLs (No login required)

- `/` or `/klien` - Homepage/Product catalog
- `/login` - Login page
- `/register` - Registration page

### User URLs (Login required)

- `/klien/keranjang` - Shopping cart
- `/klien/checkout` - Checkout page
- `/klien/order_history` - Order history
- `/klien/profile` - User profile

### Admin URLs (Admin login required)

- `/dashboard` - Admin dashboard
- `/dashboard/product` - Manage products
- `/dashboard/category` - Manage categories
- `/dashboard/warehouse` - Manage warehouses
- `/dashboard/warehouse_item` - Manage inventory stock
- `/dashboard/order` - View orders
- `/dashboard/user` - View users
- `/dashboard/report` - View reports

## Important Notes

1. **Stock Management**: Stock is stored in `warehouse_items` table, NOT in `products` table
2. **Cart Items**: Cart stores `warehouse_item_id` to track which warehouse the product comes from
3. **Checkout**: Automatically reduces stock from `warehouse_items` table
4. **CSRF Protection**: All forms include CSRF tokens for security
5. **Password Hashing**: Uses PHP's `password_hash()` with bcrypt

## Next Steps

After successful installation:

1. Login as admin
2. Add more categories and products
3. Set up warehouse inventory
4. Test the shopping flow as a regular user
5. Check reports and order management

## Support

For issues or questions:

1. Check error logs: Check Apache/PHP error logs
2. Enable PHP error display for development:
   ```php
   // Add to top of public/index.php
   ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ```
3. Verify all prerequisites are met
4. Check this guide for common troubleshooting steps
