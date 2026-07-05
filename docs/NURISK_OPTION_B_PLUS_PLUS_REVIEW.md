# NURISK OPTION B++ ENTERPRISE ARCHITECTURE REVIEW

## Executive Summary
Sebagai Principal Architecture Reviewer, saya telah membedah desain "Option B++" dengan asumsi sistem akan didera beban ekstrem bencana nasional: ratusan ribu relawan, jutaan *record*, pemutusan jaringan (*network partition*) berbulan-bulan, dan rotasi akses dinamis berskala masif. 

Meskipun secara konseptual desain ini adalah yang terbaik dari semua iterasi sebelumnya—telah memecahkan persoalan *multi-tenant isolation*—namun arsitektur ini masih menyimpan **bom waktu tersembunyi**. Ketergantungan pada sekuens *auto-increment* untuk sinkronisasi di bawah tekanan konkurensi (I/O berat) menjamin terjadinya *data loss* yang tidak terdeteksi. Selain itu, desain infrastruktur untuk menangani penugasan massal akan memicu *Denial of Service (DoS)* terhadap server NURISK itu sendiri (*Thundering Herd*). Arsitektur ini kuat di atas kertas, tetapi akan hancur dalam skenario bencana sesungguhnya tanpa mitigasi sistem terdistribusi yang lebih puritan.

---

## Critical Findings

### [CRITICAL] CF-01: The "Phantom Read" Cursor Hole (Silent Data Loss)
**Severity:** CRITICAL
**Root Cause:** Sistem terdistribusi tidak bisa bergantung pada nilai `id` atau *auto-increment* sebagai kursor sinkronisasi. Dalam RDBMS standar (InnoDB), urutan pembuatan *ID* (saat `INSERT` dimulai) tidak dijamin sama dengan urutan *commit* (saat transaksi selesai dan bisa dibaca sesi lain). 
**Failure Scenario:** 
1. Transaksi A (Payload besar) mendapat Kursor 100. (Belum commit)
2. Transaksi B (Payload kecil) mendapat Kursor 101. (Selesai & Commit)
3. Ribuan *mobile device* melakukan Pull Sync. Server menjalankan `WHERE cursor > 90`. Server mengembalikan data hingga Kursor 101. Perangkat *mobile* menyimpan `local_cursor = 101`.
4. Transaksi A akhirnya *commit*.
5. Pada *Sync* berikutnya, klien meminta data `cursor > 101`. Kursor 100 dilewati secara permanen.
**Blast Radius:** Kehilangan data krusial (*Assessment*, *Sitrep*) secara acak di sisi klien tanpa ada peringatan atau *error log*. Tingkat ketepatan data hancur total.
**Production Impact:** Keputusan lapangan diambil berdasarkan data yang cacat (*Missing Delivery*).
**Recommended Fix:** 
Tinggalkan *naive auto-increment cursor*. Gunakan implementasi `transaction_id` (High-Water Mark) atau Change Data Capture (CDC) seperti Debezium yang membaca dari *binlog* dan menggaransi *Event Ordering*. Solusi menengah: Terapkan *Overlap Window Sync* (klien selalu menarik `cursor > (X - 50)`) dipadukan dengan *Idempotency Check* di sisi SQLite klien.

### [CRITICAL] CF-02: Bootstrap Thundering Herd (Self-Inflicted DDoS)
**Severity:** CRITICAL
**Root Cause:** Validasi `membership_version` yang memicu `{require_bootstrap: true}` secara instan saat klien terhubung.
**Failure Scenario:** Saat bencana besar (contoh: Gempa Bumi Megathrust), komandan di pusat menugaskan 50.000 relawan dari berbagai wilayah ke `Insiden_Gempa`. Versi *membership* 50.000 relawan naik seketika. Dalam 10 detik, 50.000 aplikasi Flutter mendeteksi versi usang dan serentak mengeksekusi `POST /api/v1/sync/bootstrap`. 
Kueri ini akan melakukan *Full Table Scan* relasional untuk menyusun JSON berukuran puluhan Megabyte per relawan.
**Blast Radius:** 
- *Database Connection Pool Exhaustion*.
- *OOM (Out Of Memory)* pada PHP-FPM / Laravel Workers.
- NURISK lumpuh total (*System-wide Outage*).
**Production Impact:** Platform tidak bisa digunakan justru pada jam-jam emas (*Golden Hours*) tanggap darurat bencana.
**Recommended Fix:**
Desain *Pre-computed Snapshots*. Saat insiden besar, server *Worker* merender *Snapshot* statis ke dalam format JSON/Protobuf dan menyimpannya di S3/CDN. Endpoint Bootstrap hanya me-*redirect* klien untuk mengunduh *file* statis dari S3 alih-alih merender *query* di MariaDB secara komputasional.

---

## High Findings

### [HIGH] HF-01: Offline Write Conflict & Data Discard
**Severity:** HIGH
**Root Cause:** Implementasi *Optimistic Locking* (`sync_version`) yang menolak data lama (HTTP 409 Conflict).
**Failure Scenario:** Relawan A dan Relawan B bertugas di daerah tanpa sinyal selama 3 hari. Keduanya mengedit dokumen *Sitrep* yang sama. Relawan A mendapat sinyal lebih dulu dan berhasil sinkronisasi. Relawan B mendapat sinyal esok harinya. Server menolak sinkronisasi B dengan kode 409. Klien Flutter tidak memiliki kapabilitas *Merge* lokal, sehingga data jerih payah Relawan B terbuang (*Lost Update*).
**Blast Radius:** Kekecewaan pengguna ekstrem, hilangnya data intelijen lapangan.
**Production Impact:** Relawan menolak menggunakan NURISK dan kembali menggunakan WhatsApp/Kertas.
**Recommended Fix:** 
Tinggalkan penolakan keras (*Hard Reject*). Server harus mengimplementasikan "Forking / Draft Mechanism" (mirip *Git Branching*). Jika konflik, server menyimpan versi B sebagai *Draft Resolusi Konflik* yang di-flag, dan mengirimkan notifikasi ke dasbor *Command Center* untuk digabungkan secara manual (*Manual Merge Queue*).

### [HIGH] HF-02: Composite Index Misuse pada Multi-Scope Sync
**Severity:** HIGH
**Root Cause:** Asumsi bahwa indeks `(scope_type, scope_id, cursor_value)` dapat menangani *IN clause* secara efisien.
**Failure Scenario:** Jika seorang supervisor PCNU memegang 20 insiden, query yang dijalankan adalah `WHERE scope_type = 'insiden' AND scope_id IN (1,2,3...20) AND cursor_value > X`. MySQL **tidak bisa** memanfaatkan porsi `cursor_value` pada *Composite Index* jika klausa sebelumnya menggunakan `IN` (Range/List condition). Ini akan memicu *Index Condition Pushdown (ICP)* yang berat atau *filesort*.
**Blast Radius:** Degradasi performa linier (*Query Timeout*).
**Production Impact:** Laporan analitik dan sinkronisasi akan membebani I/O *Database* hingga batas maksimal.
**Recommended Fix:** 
Sistem wajib melarang penggabungan (*batching*) *scope_id* dalam satu kueri *Sync*. Endpoint *Sync* harus melakukan iterasi di sisi aplikasi atau satu kueri independen (UNION ALL) untuk setiap *scope_id*, sehingga `(scope_type = ? AND scope_id = ? AND cursor_value > X)` dieksekusi murni sebagai *Index Range Scan* yang sangat cepat (O(log N)).

---

## Medium Findings

### [MEDIUM] MF-01: Revocation Blackhole (Split-Brain Fallback)
**Severity:** MEDIUM
**Root Cause:** Tombstone `access_revocation` mengandalkan sinyal sinkronisasi.
**Failure Scenario:** Jika klien tidak pernah memanggil *endpoint* Sync setelah di-revoke (misal akun *expired* atau *device* diblokir dari jaringan), data *Ghost* akan abadi di SQLite lokal perangkat tersebut dan bisa diekstrak secara forensik.
**Recommended Fix:** Flutter *Client* harus mengimplementasikan **Timebomb TTL (Time-To-Live)**. Jika klien tidak bisa terhubung dan memverifikasi `membership_version` ke server selama 7x24 jam berturut-turut, klien mengunci layar (*Lock Screen*) atau men-*drop* *table* sensitif secara mandiri (*Self-Destruct*).

---

## Low Findings

### [LOW] LF-01: Table Size Amplification (Tombstone & Cursors)
**Severity:** LOW
**Root Cause:** Log transaksi kursor tumbuh abadi.
**Recommended Fix:** Rancang *Job Pruning* yang membersihkan kursor yang usianya lebih tua dari masa retensi *Database Restore Policy* (contoh: hapus kursor > 6 bulan).

---

## Architecture Strengths
1. **Dynamic Scope Multi-Tenancy:** Pemisahan mutlak `scope_type` dan `scope_id` merupakan desain yang sangat visioner (*Future-Proof*). Skalabilitas horizontal (sharding) dapat dilakukan dengan mudah berbasis *scope_id*.
2. **Absolute Revocation Security:** Penggunaan `membership_version` membunuh masalah kronologi *out-of-order event delivery*. Klien tidak bisa menebak atau memalsukan *state* mereka.
3. **Cursor Epoch:** Menutup lubang *Disaster Recovery* yang paling sering diabaikan oleh arsitek *junior*. Jika *database* di-*restore* ke H-1, seluruh jaringan seluler akan tersinkronisasi ulang secara otomatis tanpa merusak integritas *node*.

## Architecture Weaknesses
1. **Relational Sync Engine:** Mencoba membangun *Event Sourcing* di atas tabel *relational state* (InnoDB) adalah *anti-pattern* yang dipaksakan. Arsitektur ini rentan terhadap *Phantom Reads* kecuali disokong dengan mekanisme log transaksional yang kuat.
2. **Heavy-Client Dependency:** Server terlalu mengandalkan Flutter untuk menaati hukum *Database Purge*. Jika kode Flutter memiliki cacat logika (gagal menghapus lokal), *Data Leak* tetap terjadi.

## Missing Requirements
1. **Binary/File Synchronization:** Tidak ada desain bagaimana gambar (*Assesment Photos*, *Evacuation Logs*) disinkronisasi luring tanpa membebani ukuran *payload* JSON API secara eksponensial.
2. **Dead-Letter Queue / Quarantine:** Mekanisme penanganan jika sebuah *payload* Sync dari klien secara terus menerus memicu *Internal Server Error* di MariaDB (contoh: format karakter aneh). *Sync* akan terhenti tanpa batas (*Infinite Loop*) jika tidak ada karantina.

---

## Alternative Architectures

| Architecture | Security | Scalability | Offline Reliability | Operation Cost | Verdict |
|---|---|---|---|---|---|
| Option B++ (Current) | Sangat Tinggi | Medium | Medium | Medium | Harus diperbaiki. |
| **Change Data Capture (Debezium + Kafka)** | Sangat Tinggi | Sangat Tinggi | Maksimal | Sangat Tinggi | *Terlalu kompleks untuk NURISK. Biaya DevOps tidak sepadan.* |
| **Device Queue Fan-Out** | Ekstrem | Sangat Rendah | Sangat Tinggi | Tinggi | *Akan membunuh performa MariaDB akibat Write Amplification.* |
| **Event Sourcing (CQRS)** | Ekstrem | Sangat Tinggi | Maksimal | Tinggi | *Ideal, tetapi perombakan kode Laravel akan memakan waktu bertahun-tahun.* |

---

## Production Readiness Score

* **Security:** 99/100 (Sempurna di sisi desain otorisasi)
* **Scalability:** 60/100 (Berisiko runtuh karena *Bootstrap Storm*)
* **Maintainability:** 80/100
* **Offline Reliability:** 70/100 (Berisiko *Data Loss* akibat *Cursor Hole* & *Hard Reject Conflicts*)
* **Operational Risk:** 40/100 (Sangat berisiko saat migrasi besar & pemadaman jaringan)
* **Production Readiness:** 69/100

---

## Final Verdict

**CONDITIONAL APPROVAL**

Secara teoritis desain otorisasi Anda brilian dan level atas (*top-tier*). Namun sebagai *Distributed System*, arsitektur ini **DILARANG MASUK PRODUKSI** jika tidak memenuhi syarat-syarat *Zero-Trust Operation* berikut:

1. **Anti-Thundering Herd Protocol:** Wajib memisahkan *Endpoint Bootstrap* dari MariaDB. Terapkan S3/Redis Cache untuk mendistribusikan *Snapshot Insiden* statis, guna menghindari eksekusi kueri masif bersamaan.
2. **Phantom Read Mitigation:** Dilarang murni mengandalkan *auto-increment*. Sinkronisasi wajib ditarik mundur (`overlap window`) dipadukan dengan *Idempotency Keys* (UPSERT) di level Flutter, ATAU gunakan *Transaction-ID / High-Water Mark tracking* secara mutlak.
3. **Graceful Conflict Resolution:** Penolakan *Sync* akibat konflik *version* wajib memasukkan fitur *Draft Quarantining* di sisi server agar kerja keras relawan luring berhari-hari tidak terbuang sia-sia oleh penolakan HTTP 409 standar. 
4. **Single-Scope Sync Iteration:** Kueri ke `sync_cursors` wajib dilooping per `scope_id` dari sisi *Application Layer* untuk memastikan *Composite Index* membaca data dengan latensi O(1) dan bukan jatuh menjadi O(N) *Index Pushdown Scan*.

Setelah keempat syarat teknis mutlak ini didokumentasikan dan diimplementasikan, arsitektur ini layak menjadi standar emas (*Gold Standard*) untuk sistem respons bencana nasional.
