# Setup Instructions

## Prerequisites
- PHP 8.2+
- Composer
- Extension PHP yang harus aktif: `pdo_pgsql`, `openssl`, `mbstring`, `tokenizer`, `xml`, `ctype`

Cek di `php.ini`, uncomment baris:
```
extension=pdo_pgsql
extension=openssl
```

---

## Langkah Install

Buka terminal di folder ini, jalankan satu per satu:

```bash
# 1. Install dependencies Laravel
composer install

# 2. Generate APP_KEY
php artisan key:generate

# 3. Jalankan migrasi ke NeonDB
php artisan migrate

# 4. Jalankan development server
php artisan serve
```

Buka browser: **http://localhost:8000**

---

## Struktur Fitur

| URL | Deskripsi |
|-----|-----------|
| `GET  /agenda` | List agenda meeting (tabel + filter) |
| `GET  /agenda/create` | Form tambah agenda baru |
| `POST /agenda` | Simpan agenda baru |
| `GET  /agenda/{id}/edit` | Form edit agenda |
| `PUT  /agenda/{id}` | Update agenda |
| `DELETE /agenda/{id}` | Hapus agenda |
| `GET  /kalender` | Kalender bulanan |
| `GET  /kalender?year=2026&month=6` | Kalender bulan tertentu |

---

## NeonDB Connection

Credentials sudah dikonfigurasi di `.env`:
- Host: `ep-billowing-night-ao34aao3-pooler.c-2.ap-southeast-1.aws.neon.tech`
- Database: `neondb`
- SSL: `require`

---

## Troubleshooting

**Error `could not find driver`** → Aktifkan `pdo_pgsql` di `php.ini`

**Error `SSL connection required`** → Pastikan `DB_SSLMODE=require` di `.env`

**Error `APP_KEY not set`** → Jalankan `php artisan key:generate`

**Halaman 500** → Cek `storage/logs/laravel.log`
