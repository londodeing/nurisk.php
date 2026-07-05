# M10 MOBILISASI SECURITY REVIEW

## 1. IDOR Prevention (Integer Exposure Mitigation)
Semua entitas dalam domain Mobilisasi (`operasi_mobilisasi`) menggunakan internal auto-increment PK (`id_mobilisasi`) yang sangat berpotensi menyebabkan Insecure Direct Object Reference (IDOR) jika di-expose.

**Pencegahan:**
- API Route bindings secara eksklusif menggunakan parameter `{uuid}` yang diarahkan ke kolom `uuid_mobilisasi`.
- Form Requests tidak menerima payload referensi menggunakan `id_mobilisasi`, melainkan harus `uuid_mobilisasi`, `uuid_insiden`, dan `uuid_pengguna` (atau mapping login `Auth::id()`).
- Resource `MobilisasiResource` mereturn `$this->uuid_mobilisasi` pada key `id`.
- Foreign Keys tetap menggunakan Integer PK di layer database demi efisiensi querying dan relasi indeks (RULE-UUID-001 terimplementasi secara patuh).

## 2. Authorization Boundaries (Lapis 4)
Mobilisasi menerapkan model otorisasi granular Lapis 4 melalui `AuthorizationContextService`.

**Pencegahan:**
- `OperasiMobilisasiPolicy` memvalidasi relasi instansi menggunakan `$this->authContext->canManageInsiden($user, $mobilisasi->insiden)`.
- Pemisahan ketat aksi transisi state machine (misal, siapa yang bisa nge-approve vs nge-cancel) via policy-policy spesifik: `approve`, `depart`, `arrive`, `finish`, `cancel`.
- Aktor PCNU A tidak diperbolehkan mengupdate mobilisasi milik PCNU B yang sedang dalam wilayah insiden berbeda, sesuai AUTHORIZATION_MATRIX.

## 3. Data Integrity & Sync
- **Race Condition Mutasi:** Offline Sync Endpoint melindungi dari tumpang tindih mutasi offline dengan Optimistic Locking (`sync_version`). Conflict akan dicegat jika payload client mengirim version yang <= version di server.
- **Tombstone Sync:** Upaya soft-delete offline akan disimpan ke tabel `sync_tombstones`. Record mobilisasi tidak akan dihapus keras, menjaga jejak audit (`deleted_by`, `alasan_hapus`, `dihapus_pada`).

## 4. Input Validation & Mass Assignment Protection
- **Mass Assignment:** Property `id_mobilisasi`, `id_insiden`, dan `id_pengguna` dilindungi oleh array `$fillable` yang spesifik di Eloquent Model, dan divalidasi ketat pada layer FormRequest dengan validasi keberadaan referensi (exists rule dengan clause khusus untuk mengecek validitas status).
- **Sanitasi SQL Injection:** Penggunaan strict mode ORM Laravel membatasi potensi injeksi SQL. Lazy loading juga dimatikan (`preventLazyLoading`) sehingga query relation tidak bocor pada response.
