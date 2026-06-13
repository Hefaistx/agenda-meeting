# Dokumentasi Teknis — Agenda & Kalender Meeting IT D'PARAGON

## Gambaran Umum

Aplikasi internal untuk divisi IT D'PARAGON dalam mencatat, memantau, dan mengkomunikasikan jadwal meeting. Fitur utama:

- CRUD agenda meeting dengan conflict checking PIC dan ruangan
- Kalender bulanan yang menampilkan meeting per hari
- Copy pesan WhatsApp otomatis sesuai format baku IT
- Reschedule dan pencatatan notula meeting (NM)
- Master data Ruangan dan Topik
- Simulasi login untuk testing multi-role

---

## Tech Stack

| Layer | Teknologi |
|---|---|
| Backend | Laravel 11 |
| Database | PostgreSQL (NeonDB) |
| Frontend | Blade + Bootstrap 5.3 + Alpine.js 3.14 |
| Icons | Bootstrap Icons 1.11 |
| CSS/JS | CDN (tidak pakai Vite/Mix) |

> **PostgreSQL note:** Beberapa query pakai `ILIKE` (case-insensitive LIKE) yang merupakan sintaks PostgreSQL. Kalau pakai MySQL, ganti ke `LIKE` biasa karena MySQL sudah case-insensitive by default.

---

## Database Schema

### Tabel `meetings`

Tabel utama semua data agenda meeting.

```sql
CREATE TABLE meetings (
    id                  BIGSERIAL PRIMARY KEY,
    meeting_code        VARCHAR(255) UNIQUE,        -- Format: INT-YYYYMM-xxx atau EXT-YYYYMM-xxx
    ruangan_id          BIGINT REFERENCES rooms(id) ON DELETE SET NULL,
    topik_id            BIGINT REFERENCES topics(id) ON DELETE SET NULL,
    tanggal             DATE NOT NULL,
    jam_mulai           TIME NOT NULL,
    jam_selesai         TIME NOT NULL,
    kategori            VARCHAR(100) NOT NULL,       -- 'Internal' atau 'External'
    kegiatan            TEXT NOT NULL,               -- Deskripsi kegiatan
    status              VARCHAR(50) DEFAULT 'To Do', -- 'To Do'|'Done'|'Cancelled'|'Rescheduled'
    pic_internal        TEXT,                        -- Nama-nama dipisah koma: "Aldy Raditya, Nabil Ilyasa"
    pic_external        TEXT,                        -- Nama-nama dipisah koma: "Agus Dermawan"
    link_nm             VARCHAR(500),                -- Link notula meeting (URL)
    nm_file             VARCHAR(255),                -- Path file NM yang diupload
    hasil               TEXT,                        -- Catatan/hasil meeting
    reschedule_history  JSONB,                       -- Array riwayat reschedule
    created_at          TIMESTAMP,
    updated_at          TIMESTAMP
);
```

**Format `reschedule_history` (JSONB array):**
```json
[
  {
    "dari_tanggal":     "2026-06-10",
    "dari_jam_mulai":   "10:00",
    "dari_jam_selesai": "11:00",
    "ke_tanggal":       "2026-06-15",
    "ke_jam_mulai":     "13:00",
    "ke_jam_selesai":   "14:00",
    "alasan":           "Konflik jadwal",
    "rescheduled_by":   "Aldy Raditya",
    "rescheduled_at":   "2026-06-09 09:30"
  }
]
```

---

### Tabel `rooms`

Master data ruangan meeting.

```sql
CREATE TABLE rooms (
    id          BIGSERIAL PRIMARY KEY,
    nama        VARCHAR(100) NOT NULL,
    lokasi      VARCHAR(200),
    keterangan  TEXT,
    created_at  TIMESTAMP,
    updated_at  TIMESTAMP
);
```

---

### Tabel `topics`

Master data topik meeting. Data awal sudah di-seed via migration.

```sql
CREATE TABLE topics (
    id          BIGSERIAL PRIMARY KEY,
    nama        VARCHAR(200) NOT NULL UNIQUE,
    created_at  TIMESTAMP,
    updated_at  TIMESTAMP
);
```

**Data awal (6 topik):**
1. HnM
2. Pembahasan konsep, alur, dan simulasi
3. Review alur dan simulasi
4. Review PRD dan timeline
5. Review sistem pra-production
6. Evaluasi sistem post-production

---

## Models & Relationships

### `Meeting` (app/Models/Meeting.php)

```php
protected $fillable = [
    'meeting_code', 'ruangan_id', 'topik_id', 'tanggal', 'jam_mulai', 'jam_selesai',
    'kategori', 'kegiatan', 'status', 'pic_internal', 'pic_external',
    'link_nm', 'nm_file', 'hasil', 'reschedule_history',
];

protected $casts = [
    'tanggal'            => 'date',
    'reschedule_history' => 'array',
];

// Relationships
public function ruangan() → belongsTo(Room::class, 'ruangan_id')
public function topic()   → belongsTo(Topic::class, 'topik_id')

// Computed attributes
public function getStatusBadgeAttribute(): string   // → 'success'|'primary'|'danger'|'warning'
public function getKategoriColorAttribute(): string  // → hex color string
```

**Static data (hardcoded di model — bukan dari DB):**

```php
// Daftar kategori meeting
static $kategoriOptions = ['Internal', 'External'];

// Daftar status
static $statusOptions = ['To Do', 'Done', 'Cancelled', 'Rescheduled'];

// Daftar nama PIC Internal (25 orang)
static $picOptions = [ 'Aldy Raditya', 'Dhimas Rafi\' Hardoyo', ... ];

// Role tiap PIC (untuk header WA)
static $picRoles = [
    'Aldy Raditya' => 'Manager IT',
    'Dhimas Rafi\' Hardoyo' => 'Head IT',
    // ...
];

// Label dropdown: nama → "Nama – Jabatan"
static $picLabels = [ ... ];

// Divisi eksternal: kode → { label, members: { nama => jabatan } }
static $externalDivisions = [
    'FO'   => ['label' => 'Front Office',               'members' => [...]],
    'HRD'  => ['label' => 'Human Resources',             'members' => [...]],
    'BDSM' => ['label' => 'Business Dev & Sales Marketing', 'members' => [...]],
];

// Akun simulasi login
static $accounts = [
    ['name' => 'Aldy Raditya', 'role' => 'Manager IT', 'abbrev' => 'AR', 'color' => '#29b4d0'],
    // ...
];

// Role yang punya akses edit/delete
static $managerRoles = ['Manager IT', 'Head IT'];
```

### `Room` (app/Models/Room.php)

```php
protected $fillable = ['nama', 'lokasi', 'keterangan'];
public function meetings() → hasMany(Meeting::class, 'ruangan_id')
```

### `Topic` (app/Models/Topic.php)

```php
protected $fillable = ['nama'];
```

---

## Routes

```
GET  /                          → redirect ke /agenda

# Agenda Meeting
GET  /agenda                    → agenda.index     (list + filter)
GET  /agenda/create             → agenda.create
POST /agenda                    → agenda.store
GET  /agenda/{id}               → agenda.show
GET  /agenda/{id}/edit          → agenda.edit
PUT  /agenda/{id}               → agenda.update
DELETE /agenda/{id}             → agenda.destroy
PATCH /agenda/{id}/cancel       → agenda.cancel
POST /agenda/{id}/reschedule    → agenda.reschedule
POST /agenda/{id}/upload-nm     → agenda.upload-nm
GET  /agenda/check-conflict     → agenda.check-conflict  (JSON API, AJAX)

# Kalender
GET  /kalender                  → kalender.index

# Master Ruangan
GET/POST/PUT/DELETE /ruangan    → ruangan.*  (resource)

# Master Topik
GET/POST/PUT/DELETE /topik      → topik.*  (resource)

# Simulasi Login
POST /simulasi/login            → simulasi.login
```

---

## Controllers

### `MeetingController`

**`index(Request $request)`**
- Filter: `tanggal`, `kategori`, `status`, `jam_dari`, `jam_sampai`, `pic_internal`, `pic_external`, `ruangan_id`, `topik_id`, `tab`
- PIC filter menggunakan `ILIKE '%nama%'`
- Paginate 20 per halaman, eager-load `ruangan` dan `topic`
- `tab` filter: `semua` | `to-do` | `done` | `cancelled` | `rescheduled`
- Hitung counts per status (untuk badge tab)
- Pass `$topics` dan `$rooms` ke view

**`store(Request $request)`**
- Generate `meeting_code` unik: `INT-YYYYMM-xxx` atau `EXT-YYYYMM-xxx`
- Validasi conflict PIC (jika ada PIC yang sudah meeting di waktu yang sama)
- Status awal: `To Do`

**`checkConflict(Request $request)`** → JSON
```json
{
  "room_conflict": { "meeting_code": "...", "kegiatan": "...", "jam": "HH:MM–HH:MM" } | null,
  "pic_conflicts": [{ "name": "...", "meeting_code": "...", "jam": "...", "kegiatan": "..." }]
}
```
- Dipakai AJAX dari form create untuk live conflict warning
- Parameter: `tanggal`, `jam_mulai`, `jam_selesai`, `ruangan_id`, `pic_internal`, `exclude_id`

**`reschedule(Request $request, Meeting $agenda)`**
- Update tanggal, jam, ruangan, PIC
- Append ke `reschedule_history` JSONB
- Set status ke `Rescheduled`

**`uploadNm(Request $request, Meeting $agenda)`**
- Simpan `link_nm` (URL)
- Set status ke `Done`

### `KalenderController`

**`index(Request $request)`**
- Parameter: `year`, `month`, `divisi`, `tanggal`, `kategori`, `status`, `pic_internal`, `pic_external`, `ruangan_id`, `topik_id`
- Ambil semua meeting bulan itu → `$allMeetings` → group by date → `$meetings` (untuk grid kalender)
- Apply filter tambahan (tanggal, kategori, dll) secara in-memory → `$tableData` (untuk tabel di bawah kalender)
- `divisi` filter: kalau bukan `IT`, filter meeting yang ada PIC eksternal dari divisi tersebut

### `RuanganController` / `TopikController`
- Standard resource CRUD
- Access control: hanya `Manager IT` dan `Head IT` yang bisa modify (cek via `session('sim_user')`)

---

## Simulasi Login (Access Control)

Tidak ada auth session sungguhan. Pakai `session('sim_user')` yang di-set via `SimulasiController`.

```php
// Cek role di controller
$simUser = session('sim_user', Meeting::$accounts[0]);
$canEdit = in_array($simUser['role'], Meeting::$managerRoles);
// Manager IT dan Head IT → bisa create/edit/delete
// Role lain → view only
```

Sidebar bawah ada dropdown untuk switch user (5 akun simulasi tersedia).

---

## Fitur Per Halaman

### 1. Agenda Meeting (`/agenda`)

**Filter (collapsible card):**
- Tanggal, Kategori, Status, Jam Mulai (dari–sampai)
- PIC Internal (multi-select custom dropdown dengan Alpine.js)
- Divisi Eksternal + PIC Eksternal (2 dropdown terhubung — pilih divisi dulu, baru orang muncul)
- Topik, Ruangan

**Tab status:** Semua | To Do | Done | Cancelled | Rescheduled (dengan count badge)

**Tabel:** ID (meeting_code), Tanggal, Jam, Kategori, Topik, Kegiatan, Ruangan, Status, PIC Internal, PIC External, Divisi, NM, Aksi

**Aksi per baris:** View detail, Upload NM, Cancel, Delete

---

### 2. Tambah/Edit Agenda (`/agenda/create`, `/agenda/{id}/edit`)

**Urutan field:**
1. Tanggal, Jam Mulai, Jam Selesai
2. Kategori, Topik, Ruangan
3. Status (edit only) + info NM
4. Kegiatan / Deskripsi
5. PIC Internal (multi-select)
6. PIC Eksternal (muncul hanya jika Kategori = External) — dropdown Divisi → Orang
7. Catatan Awal / Hasil

**Conflict checking (create only):**
- AJAX ke `/agenda/check-conflict` setiap kali tanggal/jam/ruangan/PIC berubah
- Warning non-blocking untuk room conflict
- Hard-block untuk PIC conflict (modal konfirmasi)

**PIC Eksternal dropdown:**
- Muncul hanya kalau Kategori = External (via `@kategori-changed` Alpine event)
- Pilih Divisi dulu → list orang muncul
- Diimplementasi via `picExtFormField` Alpine component (defined di `layouts/app.blade.php`)
- Nilai disimpan sebagai comma-separated string di kolom `pic_external`

---

### 3. Detail Agenda (`/agenda/{id}`)

Tampilkan semua info meeting. Aksi yang tersedia (jika Manager/Head IT dan belum Cancelled):
- **Reschedule** → modal form (tanggal baru, jam baru, ruangan, PIC, alasan)
- **Edit** → ke halaman edit

Riwayat reschedule tampil di bawah jika ada.

---

### 4. Kalender Meeting (`/kalender`)

**Grid kalender bulanan:**
- 7 kolom (Sen–Min)
- Setiap cell yang punya meeting: klik → scroll ke tabel di bawah
- Summary per cell: jumlah meeting + breakdown per status dengan warna

**Navigator bulan:** prev/next chevron + dropdown bulan/tahun (auto-submit)

**Filter (collapsible):** sama dengan Agenda Meeting tapi tanpa filter Jam. Kalau ada filter aktif, kolaps otomatis terbuka.

**Tabel meeting:** ID, Tanggal, Kegiatan, Topik, Jam, Ruangan, PIC Internal, PIC External, Status, Aksi
- Aksi: View detail (modal), Copy WA per baris

**Copy WA Bulk** (tombol di header tabel):
- Baca filter `pic_internal` dari URL
- Tidak ada filter PIC → satu blok, header `*Agenda Meeting*`
- Filter 1 PIC → satu blok, header `*Agenda [role] ([nama])*`
- Filter 2+ PIC → satu blok per PIC, hanya meeting yang ada PIC tersebut

**Copy WA Per Baris:** Header cukup `*AGENDA IT Hari, DD Bulan YYYY*`

**Format WA:**
```
*AGENDA IT [periode]*
*Agenda [role] ([nama])*   ← hanya untuk bulk copy dengan filter PIC

_Hari, DD Bulan YYYY_

*N. Jam HH.MM*
        a. Kegiatan : Meeting [Kategori] - [Topik] - [Deskripsi]
        b. Status : [status]
        c. PIC Internal : [nama, nama]
        d. PIC External : [nama, nama]   ← hanya jika ada
        e. Link NM : [url]               ← kalau ada PIC Ext, label jadi 'e'
        d. Link NM : [url]               ← kalau tidak ada PIC Ext
```

---

### 5. Master Ruangan (`/ruangan`)

CRUD sederhana. Fields: Nama Ruangan, Lokasi, Keterangan. Hanya Manager/Head IT yang bisa tambah/hapus.

---

### 6. Master Topik (`/topik`)

CRUD. Fields: Nama Topik saja. 6 data awal sudah di-seed. Hanya Manager/Head IT yang bisa tambah/hapus. Tidak ada halaman edit (hapus lalu tambah ulang jika perlu ubah nama).

---

## Pola Implementasi Penting

### Alpine.js Component Pattern

Proyek ini pakai Alpine.js untuk beberapa dropdown interaktif. Ada dua pola:

**1. Dropdown tunggal** (PIC Internal multi-select):
```html
<!-- Di partials/pic-dropdown.blade.php -->
<div class="pic-dropdown" x-data="{ open: false, selected: [...] }">
    <div class="pic-trigger" @click="open = !open">...</div>
    <div class="pic-list" x-show="open" @click.outside="open = false">...</div>
    <input type="hidden" name="pic_internal" :value="selected.join(', ')">
</div>
```

**2. Dua dropdown terhubung via window event** (Divisi + PIC Eksternal di filter):
```html
<!-- Component 1: Divisi Picker -->
<div x-data="kalExtDivPicker(divisions, initDivs)">
    <!-- dispatch window event 'kal-ext-div-changed' saat toggle -->
</div>

<!-- Component 2: People Picker (dengerin event dari atas) -->
<div x-data="kalExtPeoplePicker(divisions, initDivs, initPeople)"
     @kal-ext-div-changed.window="handleDivChange($event.detail.selected)"
     x-show="selectedDivisions.length > 0" x-cloak>
</div>
```

**Penting:** Fungsi Alpine yang dipanggil dari `x-data="namaFungsi(...)"` **harus didefinisikan SEBELUM Alpine.js menginisialisasi komponen** (bukan di `@push('scripts')` yang bisa race condition). Taruh di inline `<script>` tepat sebelum elemen `x-data` tersebut di dalam view.

### Alpine.js Script Timing (Kritis)

```
Layout HTML:
<script defer src="alpinejs.js">     ← defer: jalan SETELAH HTML selesai di-parse
<script>layout functions...</script>  ← jalan SAAT HTML di-parse (sebelum Alpine)
@stack('scripts')                    ← jalan SAAT HTML di-parse (sebelum Alpine)

Content HTML:
<script>inline functions...</script>  ← jalan SAAT HTML di-parse ← PALING AMAN untuk Alpine functions
```

Fungsi yang dipanggil dari atribut `x-data` harus sudah ada di global scope sebelum Alpine evaluasi atribut tersebut. Kalau taruh di `@push('scripts')`, bisa bermasalah kalau ada runtime error sebelumnya di block yang sama.

### Conflict Check Logic

```
GET /agenda/check-conflict?tanggal=&jam_mulai=&jam_selesai=&ruangan_id=&pic_internal=&exclude_id=

Query: Meeting WHERE tanggal = X
                AND jam_mulai < jam_selesai_baru
                AND jam_selesai > jam_mulai_baru
                AND status NOT IN ('Cancelled')
                AND id != exclude_id (kalau edit)
```

Room conflict: check satu meeting dengan `ruangan_id` yang sama.
PIC conflict: untuk setiap nama PIC, check apakah ada meeting lain dengan PIC tersebut.

### Meeting Code Generation

```php
$initial   = $kategori === 'Internal' ? 'INT' : 'EXT';
$yearMonth = now('Asia/Jakarta')->format('Ym');  // e.g. '202606'
do {
    $random = strtolower(substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 3));
    $code   = $initial . '-' . $yearMonth . '-' . $random;  // e.g. 'INT-202606-a3k'
} while (Meeting::where('meeting_code', $code)->exists());
```

### Access Control (Simulasi)

```php
// Di setiap controller yang butuh cek role:
private function simUser(): array {
    return session('sim_user', Meeting::$accounts[0]);
}
private function canModify(): bool {
    return in_array($this->simUser()['role'], Meeting::$managerRoles);
}
```

---

## UI/Layout

### Layout Utama (`resources/views/layouts/app.blade.php`)

- Sidebar fixed kiri (220px)
- Topbar sticky atas
- Content area `padding: 20px 24px`
- Toast notifications (Bootstrap Toast) untuk flash session `success` dan `error`
- Simulasi Login dropdown di bawah sidebar (Alpine.js)

### CSS Classes Penting

```css
.card          → card putih dengan border-radius 8px dan shadow ringan
.card-hdr      → header card dengan border-bottom, flex space-between
.btn-teal      → tombol teal (#29b4d0)
.badge-status  → badge status meeting
.pic-chip      → chip biru muda untuk nama PIC
.pic-dropdown  → wrapper custom dropdown PIC
.pic-trigger   → trigger/button dropdown
.pic-list      → dropdown list items (position:absolute, z-index:999)
```

### Warna Status

| Status | Warna | Bootstrap Badge |
|---|---|---|
| To Do | `#3b5bdb` (biru) | `bg-primary` |
| Done | `#16a34a` (hijau) | `bg-success` |
| Cancelled | `#dc2626` (merah) | `bg-danger` |
| Rescheduled | `#f59e0b` (kuning) | `bg-warning` |

### Warna Kategori

| Kategori | Warna |
|---|---|
| Internal | `#29b4d0` (teal) |
| External | `#4361ee` (biru ungu) |

---

## Environment & Setup

```bash
# Install dependencies
composer install

# Copy env
cp .env.example .env

# Set database ke PostgreSQL (NeonDB)
DB_CONNECTION=pgsql
DB_HOST=...
DB_PORT=5432
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...

# Generate key
php artisan key:generate

# Jalankan semua migration (sudah include seed topik awal)
php artisan migrate

# Jalankan server
php artisan serve
```

---

## Urutan Migration

```
1. create_meetings_table           ← tabel utama
2. add_nm_file_to_meetings         ← kolom nm_file
3. add_meeting_code_to_meetings    ← kolom meeting_code
4. add_reschedule_history          ← kolom JSONB reschedule_history
5. create_rooms_table              ← master ruangan
6. add_ruangan_to_meetings         ← FK ruangan_id
7. drop_kapasitas_from_rooms       ← hapus kolom kapasitas (tidak dipakai)
8. jam_range_to_meetings           ← pisah jam_mulai dan jam_selesai
9. create_topics_table             ← master topik + seed 6 data awal
10. add_topik_to_meetings          ← FK topik_id
```

---

## File Structure

```
app/
  Http/Controllers/
    MeetingController.php     ← CRUD + conflict check + reschedule + upload NM
    KalenderController.php    ← Calendar view
    RuanganController.php     ← Master ruangan
    TopikController.php       ← Master topik
    SimulasiController.php    ← Switch user simulasi
  Models/
    Meeting.php               ← Model utama + semua static data (PIC, divisions, dll)
    Room.php
    Topic.php

resources/views/
  layouts/
    app.blade.php             ← Layout utama + sidebar + Alpine components global
  agenda/
    index.blade.php           ← List meeting + filter + tab status
    create.blade.php          ← Form tambah
    edit.blade.php            ← Form edit
    show.blade.php            ← Detail + reschedule modal
  kalender/
    index.blade.php           ← Grid kalender + tabel + copy WA
  master/
    ruangan/
      index.blade.php, create.blade.php, edit.blade.php
    topik/
      index.blade.php, create.blade.php, edit.blade.php
  partials/
    pic-dropdown.blade.php    ← Reusable multi-select PIC Internal
    time-picker.blade.php     ← Custom time picker
```

---

## Catatan untuk Developer

1. **Jangan taruh fungsi Alpine di `@push('scripts')` kalau fungsi itu dipanggil dari atribut `x-data`** — taruh di inline `<script>` sebelum elemen yang membutuhkannya.

2. **PIC Internal dan PIC External disimpan sebagai plain text comma-separated**, bukan relasi FK ke tabel users. Ini by design karena daftar PIC bersifat statis dan cukup didefinisikan di `Meeting::$picOptions`.

3. **Conflict check di form create adalah live AJAX** — tidak hard-block kecuali PIC conflict. Room conflict hanya peringatan (user tetap bisa submit).

4. **Kalender menggunakan dua set data berbeda:**
   - `$meetings` (grouped by date) → untuk grid kalender visual
   - `$tableData` (server-filtered) → untuk tabel di bawah kalender
   - Filter form hanya mempengaruhi `$tableData`, grid kalender tetap tampilkan semua meeting bulan tersebut.

5. **Copy WA menggunakan `document.execCommand('copy')` (synchronous)** sebagai primary, Clipboard API sebagai fallback. Ini karena Clipboard API membutuhkan user gesture yang kadang bermasalah di beberapa browser.

6. **Simulasi login tidak pakai Laravel Auth** — hanya simpan array user ke `session('sim_user')`. Ini cukup untuk prototype/internal tool.
