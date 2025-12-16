# 🎀 Knits & Prints - E-Commerce POS System

A cute, pink-themed e-commerce website with POS functionality for small businesses in the Philippines.

## ✨ Features

### Customer Features
- 📱 Mobile-first, responsive design
- 📁 Album-based product browsing (Facebook-style)
- 🖼️ Lightbox photo viewer with navigation
- 🛒 Shopping cart functionality
- 💳 Philippine payment methods (GCash & Bank Transfer)
- 📸 Payment proof upload
- 📧 Order confirmation page

### Admin Features
- 🔐 Secure admin login
- 📁 Album management
- 📤 Bulk product upload (multiple images at once)
- ✏️ Product editing (name, price, stock, visibility)
- 📋 Order management
- ✅ Payment verification
- 📊 Dashboard with statistics

## 🛠️ Installation

### Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- GD Library (for image processing)

### Setup Steps

1. **Extract Files**
```
   Extract the zip file to your web server directory
   (e.g., htdocs/knits-and-prints/ for XAMPP)
```

2. **Create Database**
   - Open phpMyAdmin
   - Create a new database named `knits_prints`
   - Import the `database.sql` file

3. **Configure Database Connection**
   - Edit `config/database.php`
   - Update database credentials if needed:
```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'knits_prints');
```

4. **Set Permissions**
```bash
   chmod 777 uploads/products/
   chmod 777 uploads/payments/
```

5. **Access the Website**
   - Customer Site: `http://localhost/knits-and-prints/customer/`
   - Admin Panel: `http://localhost/knits-and-prints/admin/`

## 🔑 Default Admin Credentials

- **Username:** `admin`
- **Password:** `admin123`

**⚠️ IMPORTANT:** Change the default password immediately after first login!

## 📖 Usage Guide

### For Admin

1. **Login to Admin Panel**
   - Go to `/admin/login.php`
   - Use default credentials

2. **Create Albums**
   - Go to "Manage Albums"
   - Create collections (e.g., "Summer Collection", "Knitted Bags")

3. **Upload Products**
   - Go to "Bulk Upload Products"
   - Select an album
   - Set default price and stock
   - Select multiple images (Ctrl+Click or Cmd+Click)
   - Click "Upload Products"

4. **Manage Orders**
   - Go to "View Orders"
   - View customer details and payment proof
   - Update order status (Pending → Paid → Completed)

### For Customers

1. **Browse Products**
   - Click on albums to view products
   - Click any product to open lightbox viewer
   - Use arrow keys or buttons to navigate

2. **Add to Cart**
   - Click "Add to Cart" in lightbox
   - View cart from header

3. **Checkout**
   - Fill in delivery information
   - Select payment method (GCash or Bank Transfer)
   - Upload payment proof
   - Submit order

## 💳 Payment Setup

### GCash
1. Edit `customer/checkout.php`
2. Update GCash number and account name
3. Replace QR code image with actual GCash QR

### Bank Transfer
1. Edit `customer/checkout.php`
2. Update bank details (bank name, account name, account number)

## 🎨 Customization

### Change Colors
Edit `css/style.css` and modify these color variables:
```css
/* Primary Pink: #ec407a */
/* Light Pink: #f8bbd0 */
/* Background: #fce4ec */
```

### Change Site Name
1. Edit all header files
2. Replace "Knits & Prints" with your business name
3. Update page titles in all PHP files

### Add Payment Methods
1. Edit `customer/checkout.php`
2. Add new payment option in the payment methods section
3. Add corresponding payment details section

## 📁 File Structure
```
knits-and-prints/
├── admin/              # Admin panel files
├── customer/           # Customer-facing files
├── config/             # Database configuration
├── css/                # Stylesheets
├── js/                 # JavaScript files
├── uploads/            # Uploaded files
│   ├── products/       # Product images
│   └── payments/       # Payment proofs
└── database.sql        # Database schema
```

## 🔒 Security Notes

1. **Change default admin password**
2. **Use HTTPS in production**
3. **Update database credentials**
4. **Set proper file permissions**
5. **Validate all user inputs**
6. **Backup database regularly**

## 🐛 Troubleshooting

### Images not uploading
- Check folder permissions (chmod 777)
- Verify GD library is installed
- Check PHP upload_max_filesize setting

### Database connection error
- Verify database credentials
- Ensure MySQL service is running
- Check database name is correct

### Session issues
- Verify session.save_path is writable
- Check PHP session settings

## 📱 Browser Compatibility

- Chrome (recommended)
- Firefox
- Safari
- Edge
- Mobile browsers

## 📞 Support

For issues or questions, please check:
1. Database connection settings
2. File permissions
3. PHP error logs
4. Browser console

## 📄 License

This project is provided as-is for small business use.

## 🎉 Credits

Created for small businesses in the Philippines 🇵🇭