# NURISK Sprint 19A — UI Foundation Audit

## 1. Layout Existing
- Terdapat peninggalan skema otentikasi dari `Laravel Breeze` (menggunakan Tailwind CSS) pada berkas di `resources/views/auth/`.
- File `dashboard.blade.php` masih menggunakan struktur *layout* ganda yang mencampur Tailwind dan HTML polosan.

## 2. Blade Existing
- Terdapat inkonsistensi struktur hirarki komponen di `resources/views/components`. Blade komponen bawaan framework seperti `primary-button` dan `dropdown` menggunakan styling yang bertabrakan dengan Bootstrap 5.

## 3. CSS & Javascript Existing
- Aplikasi saat ini tidak menyertakan library Bootstrap 5 secara global pada *header*.
- Polling *Ajax* dan penanganan *Event* Websocket pada `realtime-map.js` berpotensi tumpang tindih dengan utilitas DOM dari jQuery.

## 4. Technical Debt Ditemukan
1. **Duplikasi Dependensi:** Aplikasi memuat dua engine CSS (Tailwind via Vite dan file statis).
2. **Ketiadaan Reusable Grid:** Layout *dashboard* tidak menggunakan sistem *grid* yang mapan. Segala sesuatunya diletakkan secara linier (*stacked*).
3. **Hardcoded Strings:** Beberapa *alert* tidak menggunakan *translation* atau *slot* melainkan diketik mati di dalam *view*.

## 5. Keputusan Refactoring
Akan dilakukan "sapu bersih" (*Clean Sweep*) pada file `resources/views/layouts/app.blade.php`. Seluruh CSS framework lama akan diganti dengan referensi Bootstrap 5 via CDN, disertai jQuery 3.6+ untuk menjamin standardisasi antar muka operasional.
