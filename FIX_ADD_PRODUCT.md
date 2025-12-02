# Fix: Gagal Menambahkan Produk

## 🔴 Masalah

Gagal menambahkan produk dari dashboard karena database tidak memiliki kolom `photo_url` dan `category_id` di tabel `products`, serta tidak ada tabel `categories`.

## ✅ Solusi

### Pilihan 1: Setup Database Baru (Recommended)

Jika Anda belum punya data penting, jalankan script ini untuk setup lengkap:

```powershell
sqlcmd -S localhost -d warehouse_db -E -i "E:\laragon\www\distribution\01_schema_base.sql"
```

**Script ini akan:**

- ✅ Drop & recreate semua tabel dengan struktur lengkap
- ✅ Buat tabel `categories` dengan 5 kategori default
- ✅ Tambah kolom `photo_url` dan `category_id` ke tabel `products`
- ✅ Insert sample data (5 produk, 3 user, 3 warehouse)

---

### Pilihan 2: Update Database Existing (Jika Ada Data)

Jika sudah punya data dan tidak ingin kehilangan, jalankan script ALTER ini:

```powershell
sqlcmd -S localhost -d warehouse_db -E -i "E:\laragon\www\distribution\02_alter_add_columns.sql"
```

**Script ini akan:**

- ✅ Buat tabel `categories` (jika belum ada)
- ✅ Tambah kolom `photo_url` ke tabel `products`
- ✅ Tambah kolom `category_id` ke tabel `products`
- ✅ Setup foreign key dan indexes
- ✅ **TIDAK menghapus data existing**

---

## 🗂️ Struktur Tabel Setelah Update

### Tabel `categories`

```sql
category_id   INT IDENTITY(1,1) PRIMARY KEY
category_name NVARCHAR(100)
created_at    DATETIME
```

**Kategori Default:**

1. Elektronik
2. Pakaian
3. Makanan
4. Minuman
5. Peralatan Rumah Tangga

### Tabel `products` (Updated)

```sql
product_id   INT IDENTITY(1,1) PRIMARY KEY
product_name NVARCHAR(100)
price        DECIMAL(12,2)
photo_url    NVARCHAR(255)           -- ✅ BARU!
category_id  INT                     -- ✅ BARU!
created_at   DATETIME
```

---

## 📸 Cara Upload Foto Produk

1. Buat folder `E:\laragon\www\distribution\public\assets\img\products\` (jika belum ada)
2. Upload foto dari form dashboard
3. Foto akan disimpan dengan nama unik (timestamp)
4. Path disimpan di database sebagai `/assets/img/products/filename.jpg`

---

## ✨ Fitur yang Sekarang Berfungsi

Setelah menjalankan salah satu script di atas:

✅ **Tambah Produk** - Bisa upload foto dan pilih kategori  
✅ **Edit Produk** - Update foto dan kategori  
✅ **Hapus Produk** - Foto akan otomatis terhapus  
✅ **Filter by Category** - User bisa browse produk per kategori  
✅ **Tambah Warehouse** - Dengan distributed ID  
✅ **Tambah Stock** - Stok per gudang per region

---

## 🚀 Test Aplikasi

Setelah menjalankan script SQL:

1. **Login sebagai Admin:**

   - Email: `admin@example.com`
   - Password: `password` (hash sudah tersedia)

2. **Akses Dashboard:**

   ```
   http://localhost/distribution/public/dashboard
   ```

3. **Tambah Produk Baru:**

   - Menu: Products (Global) → Tambah Produk
   - Isi form: Nama, Harga, Upload Foto, Pilih Kategori
   - Submit

4. **Tambah Warehouse:**

   - Menu: Warehouse → Tambah Warehouse
   - Isi: Nama Gudang, Region, Alamat

5. **Tambah Stock:**
   - Menu: Warehouse Items → Tambah Stock
   - Pilih: Gudang, Produk, Jumlah Stok

---

## 🔍 Troubleshooting

### Error: "Invalid column name 'photo_url'"

➡️ Jalankan script `02_alter_add_columns.sql`

### Error: "Invalid object name 'categories'"

➡️ Jalankan script `02_alter_add_columns.sql` atau `01_schema_base.sql`

### Foto tidak tersimpan

➡️ Cek permission folder `public/assets/img/products/` (harus writable)

### Sample data tidak muncul

➡️ Jalankan script `01_schema_base.sql` (akan insert sample data)

---

## 📝 Catatan Penting

- Default foto: `/assets/img/products/default.svg`
- Kategori bisa null (optional)
- Foreign key: `products.category_id` → `categories.category_id`
- Saat delete category, product's category_id akan di-set NULL (tidak menghapus produk)

---

**File yang diupdate:**

- `01_schema_base.sql` - Schema lengkap dengan categories
- `02_alter_add_columns.sql` - ALTER tanpa hapus data
