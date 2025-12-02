# Warehouse Management & E-commerce System

Website sistem warehouse dan e-commerce sederhana menggunakan PHP Native dan SQL Server.

## 📋 Fitur Utama

### Dashboard Admin

- **Dashboard**: Ringkasan data produk, warehouse, stok, dan pesanan
- **Kelola Produk**: CRUD produk dengan foto dan kategori
- **Kelola Kategori**: CRUD kategori produk
- **Kelola Warehouse**: CRUD gudang dengan region dan alamat
- **Kelola Stok Inventory**: Manajemen stok per warehouse per produk
- **Kelola Pesanan**: Lihat daftar pesanan dan detailnya
- **Kelola User**: Lihat daftar user terdaftar
- **Laporan**: Laporan pesanan bulanan

### Website Client (User)

- **Homepage**: Katalog produk dengan filter kategori
- **Detail Produk**: Info lengkap produk dan stok per warehouse
- **Keranjang**: Manajemen keranjang belanja
- **Checkout**: Proses pemesanan (simple, langsung create order)
- **Riwayat Pesanan**: History pesanan user
- **Profile**: Lihat data profile user

## 🛠️ Teknologi

- **Backend**: PHP Native (tanpa framework)
- **Database**: SQL Server (menggunakan sqlsrv driver)
- **Frontend**: HTML, CSS (vanilla)
- **Authentication**: Session-based authentication

## 📁 Struktur Folder

```
project/
├── config/
│   └── database.php          # Koneksi SQL Server
├── public/
│   ├── index.php             # Entry point & router
│   ├── .htaccess
│   └── assets/
│       ├── css/              # Style files
│       ├── img/              # Images
│       └── js/
├── app/
│   ├── controllers/          # Controllers (MVC pattern)
│   └── models/               # Models untuk database
├── views/
│   ├── dashboard/            # Admin views
│   ├── client/               # User views
│   └── auth/                 # Login & Register
├── helpers/
│   ├── csrf.php              # CSRF protection
│   └── util.php              # Utility functions
├── routes.php                # Routing configuration
├── database.sql              # Database schema & sample data
└── README.md
```

## 🚀 Instalasi

### Prerequisites

1. PHP 7.4 atau lebih tinggi
2. SQL Server (2016 atau lebih baru)
3. PHP SQL Server Extension (sqlsrv dan pdo_sqlsrv)
4. Apache/Nginx Web Server

### Langkah Instalasi

1. **Clone/Download Project**

   ```bash
   git clone [repository-url]
   cd project
   ```

2. **Setup Database**

   - Buka SQL Server Management Studio (SSMS)
   - Jalankan script `database.sql`
   - Database `warehouse_3` akan terbuat otomatis

3. **Konfigurasi Database**
   Edit file `config/database.php`:

   ```php
   private $host = "localhost";
   private $database = "warehouse_3";
   private $username = "sa";
   private $password = "your_password";
   ```

4. **Setup Web Server**

   **Apache:**

   - Pastikan mod_rewrite enabled
   - Set DocumentRoot ke folder `public/`
   - Atau buka: `http://localhost/project/public/`

   **PHP Built-in Server (untuk testing):**

   ```bash
   cd public
   php -S localhost:8000
   ```

   Buka: `http://localhost:8000`

5. **Login**

   **Admin:**

   - Email: `admin@example.com`
   - Password: `admin123`

   **User:**

   - Email: `user@example.com`
   - Password: `user123`

## 📊 Struktur Database

### Tabel Utama:

- `users` - Data user (admin & customer)
- `categories` - Kategori produk
- `products` - Data produk (TANPA stok)
- `warehouses` - Data gudang
- `warehouse_items` - **STOK INVENTORY per gudang**
- `cart_items` - Keranjang belanja
- `orders` - Header pesanan
- `order_items` - Detail item pesanan

### ⚠️ Catatan Penting:

- **STOK HANYA ADA DI `warehouse_items`**, tidak di tabel `products`
- Saat checkout, stok dikurangi dari `warehouse_items`
- Cart menyimpan `warehouse_item_id`, bukan `product_id`

## 🔄 Flow Bisnis

### 1. Add to Cart

- User memilih produk dan warehouse tertentu
- System menyimpan `warehouse_item_id` dan `qty` ke `cart_items`

### 2. Checkout Process

- Ambil semua `cart_items` milik user
- Loop setiap item:
  - Insert ke `order_items`
  - Kurangi `warehouse_items.stock`
- Hitung total dan insert ke `orders`
- Clear `cart_items` user

## 🎨 Fitur Keamanan

- CSRF Protection pada semua form
- Password hashing dengan `password_hash()`
- Input sanitization
- Session-based authentication
- SQL injection prevention dengan parameterized queries

## 📝 URL Routes

### Auth

- `/login` - Halaman login
- `/register` - Halaman register
- `/logout` - Logout

### Dashboard (Admin)

- `/dashboard` - Dashboard home
- `/dashboard/product` - CRUD Produk
- `/dashboard/category` - CRUD Kategori
- `/dashboard/warehouse` - CRUD Warehouse
- `/dashboard/warehouse_item` - CRUD Stok
- `/dashboard/order` - Daftar pesanan
- `/dashboard/user` - Daftar user
- `/dashboard/report` - Laporan

### Client (User)

- `/klien` - Homepage katalog
- `/klien/produk_kategori?id=X` - Filter by kategori
- `/klien/detil_produk?id=X` - Detail produk
- `/klien/keranjang` - Keranjang
- `/klien/checkout` - Checkout
- `/klien/order_history` - Riwayat pesanan
- `/klien/profile` - Profile user

## 🐛 Troubleshooting

### SQL Server Connection Error

- Pastikan SQL Server service berjalan
- Cek username & password di `config/database.php`
- Pastikan PHP sqlsrv extension terinstall

### 404 Error

- Pastikan mod_rewrite Apache aktif
- Cek file `.htaccess` ada di folder `public/`

### Upload Image Error

- Pastikan folder `public/assets/img/products/` writable
- Cek permission folder (755 atau 777)

## 📞 Support

Untuk pertanyaan dan bantuan, silakan buat issue di repository ini.

## 📄 License

Project ini dibuat untuk keperluan pembelajaran.

---

**Catatan**: Ini adalah sistem sederhana untuk pembelajaran. Untuk production, pertimbangkan:

- Validasi input yang lebih ketat
- Error handling yang lebih baik
- Logging system
- Rate limiting
- HTTPS only
- Environment variables untuk konfigurasi

# e-commerce-distribution
