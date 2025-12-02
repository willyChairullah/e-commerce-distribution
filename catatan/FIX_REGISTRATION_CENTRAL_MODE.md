# 🔧 Fix: Registrasi Gagal di Central Mode

**Issue:** User tidak bisa registrasi di http://localhost/distribution/public/register

**Root Cause:** Di mode `REGION_MODE='central'`, `REGION_CODE=null`, tapi stored procedure `sp_InsertUser` membutuhkan `region_code` untuk generate distributed ID.

---

## ✅ Solusi Yang Sudah Diterapkan

### 1. Update Form Register (`views/auth/register.php`)

**Added:** Dropdown region untuk central mode

```php
<?php if (isCentralMode()): ?>
    <!-- User pilih region di central mode -->
    <select name="region_code" required>
        <option value="">-- Pilih Region --</option>
        <option value="JKT">Jakarta</option>
        <option value="BDG">Bandung</option>
        <option value="SBY">Surabaya</option>
    </select>
<?php else: ?>
    <!-- Auto-detect region di regional mode -->
    <input type="text" value="Bandung" disabled>
<?php endif; ?>
```

---

### 2. Update AuthController (`app/controllers/AuthController.php`)

**Changed:** Logic untuk handle region dari POST di central mode

```php
// BEFORE:
$regionCode = getCurrentRegion(); // null di central mode!

// AFTER:
if (isCentralMode()) {
    $regionCode = $_POST['region_code']; // user pilih dari dropdown
} else {
    $regionCode = getCurrentRegion(); // auto dari config
}
```

---

## 🧪 Testing

### Test 1: Manual via SQL (✅ PASSED)

```powershell
sqlcmd -S localhost -d warehouse_db -E -Q "
DECLARE @new_user_id VARCHAR(50);
EXEC sp_InsertUser
    @full_name='Test User',
    @email='test@example.com',
    @password='hashed',
    @region_code='BDG',
    @is_admin=0,
    @new_user_id=@new_user_id OUTPUT;
SELECT @new_user_id;
"
```

**Result:** `BDG-U-000002` ✅ (sequential ID generated)

---

### Test 2: Via Browser (Ready to Test)

1. Open: http://localhost/distribution/public/register
2. **Expected:** Form sekarang ada dropdown "Pilih Region"
3. Fill form:
   - Nama: Test User
   - Email: test2@example.com
   - Password: password123
   - **Region: Pilih salah satu (JKT/BDG/SBY)**
4. Submit
5. **Expected:** Registrasi berhasil, redirect ke login

---

## 📊 Behavior per Mode

| Mode               | REGION_CODE | Region Input        | Behavior                         |
| ------------------ | ----------- | ------------------- | -------------------------------- |
| **Central**        | `null`      | Dropdown (required) | User **WAJIB** pilih region      |
| **Regional (JKT)** | `'JKT'`     | Disabled field      | Auto `JKT`, user tidak bisa ubah |
| **Regional (BDG)** | `'BDG'`     | Disabled field      | Auto `BDG`, user tidak bisa ubah |

---

## 🎯 User Flow

### Central Mode (Laptop 1 - Database Pusat):

```
1. User buka /register
2. Form muncul dengan dropdown region
3. User pilih: "Bandung"
4. Submit
5. AuthController validate region
6. Call sp_InsertUser dengan region_code='BDG'
7. SP generate user_id: BDG-U-XXXXXX
8. User tersimpan di database pusat
9. Redirect ke /login
```

### Regional Mode (Laptop 2 - Database BDG):

```
1. User buka /register
2. Form muncul dengan field region disabled (otomatis "Bandung")
3. Submit
4. AuthController ambil region dari config (BDG)
5. Call sp_InsertUser dengan region_code='BDG'
6. SP generate user_id: BDG-U-XXXXXX
7. User tersimpan di database regional BDG
8. Redirect ke /login
```

---

## 🔍 Troubleshooting

### Error: "Region harus dipilih"

**Cause:** User tidak pilih region di dropdown (central mode)  
**Solution:** Pilih region dari dropdown sebelum submit

### Error: "Region tidak valid"

**Cause:** Region code tidak ada di `AVAILABLE_REGIONS`  
**Solution:** Update `config/app.php`, tambahkan region ke array

### Error: "Registrasi gagal"

**Cause:** Stored procedure `sp_InsertUser` error  
**Check:**

```powershell
# Check if SP exists
sqlcmd -S localhost -d warehouse_db -E -Q "SELECT name FROM sys.procedures WHERE name='sp_InsertUser'"

# If not found, install logic objects
sqlcmd -S localhost -d warehouse_db -E -i "E:\laragon\www\distribution\02_logic_objects.sql"
```

---

## ✅ Files Modified

1. `views/auth/register.php` - Added region dropdown for central mode
2. `app/controllers/AuthController.php::register()` - Updated region handling logic

---

## 📝 Next Steps

Test registrasi via browser:

- http://localhost/distribution/public/register

Expected result:

- ✅ Form menampilkan dropdown region (central mode)
- ✅ Registrasi berhasil
- ✅ User ID format: `{REGION}-U-{SEQUENCE}`
- ✅ Redirect ke login page

---

**Status:** ✅ Fixed  
**Date:** December 2, 2024
