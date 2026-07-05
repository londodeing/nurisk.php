# UI Design: Command Center V1

**Target Pengguna:** Operator Jaga 24 Jam (Ditampilkan di TV Layar Besar / Monitor Utama).
**Teknologi:** Polling via AJAX setiap 30 Detik (Tanpa WebSockets).

## Komponen Visual (Full Screen)

```text
+-------------------------------------------------------------------------+
| [ LIVE MAP (Leaflet.js) - 60% Lebar Layar ]                             |
|                                                                         |
|  (Peta interaktif dengan titik marker merah untuk Insiden dan titik     |
|   biru untuk Posko Aju. Klik marker memunculkan tooltip ringkasan)      |
|                                                                         |
+-------------------------------------+-----------------------------------+
| INCIDENT FEED (Polling 30s)         | STATUS POSKO & LOGISTIK           |
| ----------------------------------- | --------------------------------- |
| [14:02] Titik Darurat Baru di X     | Posko Alpha: [Aman]               |
| [13:50] Relawan A tiba di Lokasi    | Posko Bravo: [Butuh Beras]        |
| [13:45] Sitrep: "Banjir meluas"     | Posko Charlie: [Aman]             |
|                                     |                                   |
| VOLUNTEER STATUS                    | ESCALATION QUEUE                  |
| ----------------------------------- | --------------------------------- |
| Aktif: 150 | Idle: 20               | 1 Permintaan Pleno Darurat (BKO)  |
+-------------------------------------+-----------------------------------+
```

## Keputusan Desain (Polling vs WebSocket)
- **Mengapa Polling 30 Detik?** Untuk Command Center V1 yang tidak membutuhkan millisecond precision, *polling* menggunakan `setInterval()` + Axios/Fetch jauh lebih tangguh jika koneksi operator naik turun atau di lingkungan rural (pedesaan), dibandingkan koneksi WebSocket yang *fragile* (mudah terputus) saat pergantian sinyal.
