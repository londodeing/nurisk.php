# Quick Action Compliance Audit

Pemeriksaan kepatuhan tombol pintas cepat (*Quick Actions*) di seluruh dasbor.

---

## Temuan 1: Quick Command Widget di Dasbor Keputusan (Governance)
- **File:** [quick_command_widget.dart](file:///home/londo/nurisk/mobile/app/lib/features/governance/presentation/widgets/quick_command_widget.dart#L21-L28)
- **Kode Bukti:**
  ```dart
  children: [
    _buildCommandChip(context, Icons.note_add, 'Buat SPK', Colors.blue),
    _buildCommandChip(context, Icons.flag, 'Aktivasi Posko', Colors.orange),
    _buildCommandChip(context, Icons.mail, 'Surat Masuk', Colors.purple),
    _buildCommandChip(context, Icons.add_circle, 'Draft Baru', Colors.green),
    _buildCommandChip(context, Icons.people, 'Delegasikan', Colors.teal),
  ],
  ```
- **Aksi Penekanan (Hardcoded Navigation):**
  Setiap chip memiliki penanganan klik yang mengarahkan ke fungsi statis lokal di Flutter, contoh:
  `onTap: () => context.push('/spk/create')` (atau sejenisnya).
- **Pelanggaran Arsitektur:**
  Menu aksi cepat harus 100% didikte dari BFF melalui tipe widget `ActionList` atau `QuickActionGrid`.
  Setiap *item* harus membawa skema:
  ```json
  {
    "id": "btn_create_spk",
    "label": "Buat SPK",
    "icon": "note_add",
    "color": "#3B82F6",
    "action": {
      "type": "navigate",
      "target": "/spk/create"
    }
  }
  ```

---

## Temuan 2: Menu Utama Widget Dinamis Baru (Phase 2.2C)
- **Status:** **PATUH** (Pada dashboard publik/BFF utama).
- **Analisis:**
  Setelah perbaikan di `DashboardBffController.php` dan `WidgetFactory.dart` (pada tugas sebelumnya), `ActionListWidget` kini memuat menu dinamis:
  ```php
  $menuItems[] = ['id' => 'm1', 'label' => 'Lapor Kejadian', 'icon' => 'plus-circle', 'target' => '/p/report'];
  ```
  Namun, menu pada layar kepemimpinan (*Governance Dashboard*) dan profile (*Account HomeScreen*) **belum** dikonversi menggunakan skema dinamis ini dan masih menggunakan tombol statis native.
