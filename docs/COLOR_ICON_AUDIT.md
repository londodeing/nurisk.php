# Color & Icon Compliance Audit

Investigasi mendalam terhadap kode warna dan penentuan ikon yang dipasang secara statis (*hardcoded*) di sisi klien Flutter untuk menampilkan tingkat keparahan (*severity*), status persetujuan (*approval status*), atau jenis bencana.

---

## 1. Warna & Ikon KPI Dashboard Publik
- **File:** [kpi_cards_section.dart](file:///home/londo/nurisk/mobile/app/lib/features/public/dashboard/presentation/widgets/kpi_cards_section.dart#L24-L27)
- **Kode Bukti:**
  ```dart
  _buildKpiCard(context, 'Insiden Aktif', kpi.activeIncidents.toString(), Icons.local_fire_department, Colors.red),
  _buildKpiCard(context, 'Personel Aktif', kpi.verifiedIncidents.toString(), Icons.group, Colors.green),
  _buildKpiCard(context, 'Korban Terdampak', kpi.impactedRegions.toString(), Icons.personal_injury, Colors.blue),
  _buildKpiCard(context, 'Kebutuhan Mendesak', kpi.deployedVolunteers.toString(), Icons.warning, Colors.orange),
  ```
- **Pelanggaran:**
  Ikon api (`Icons.local_fire_department`), grup (`Icons.group`), cedera (`Icons.personal_injury`), dan peringatan (`Icons.warning`) dipasang permanen. Warna merah, hijau, biru, dan oranye ditentukan oleh Flutter.

---

## 2. Warna & Ikon Quick Actions Tata Kelola (Governance)
- **File:** [quick_command_widget.dart](file:///home/londo/nurisk/mobile/app/lib/features/governance/presentation/widgets/quick_command_widget.dart#L22-L26)
- **Kode Bukti:**
  ```dart
  _buildCommandChip(context, Icons.note_add, 'Buat SPK', Colors.blue),
  _buildCommandChip(context, Icons.flag, 'Aktivasi Posko', Colors.orange),
  _buildCommandChip(context, Icons.mail, 'Surat Masuk', Colors.purple),
  _buildCommandChip(context, Icons.add_circle, 'Draft Baru', Colors.green),
  _buildCommandChip(context, Icons.people, 'Delegasikan', Colors.teal),
  ```
- **Pelanggaran:**
  Ikon-ikon dan skema warna chip tombol operasional dideklarasikan manual di klien. Jika ada menu baru, penambahan ikon dan warna mengharuskan *deployment* Flutter baru.

---

## 3. Warna Status Antrean Persetujuan (Document Queue)
- **File:** [pending_decision_widget.dart](file:///home/londo/nurisk/mobile/app/lib/features/governance/presentation/widgets/pending_decision_widget.dart#L54-L75)
- **Kode Bukti:**
  ```dart
  border: Border.all(color: Colors.red.shade100),
  backgroundColor: Colors.red.shade50,
  color: Colors.red.shade700,
  ```
- **Pelanggaran:**
  Warna merah muda (`Colors.red.shade50`) dan merah pekat (`Colors.red.shade700`) dikunci di klien untuk persetujuan dokumen berisiko tinggi. Semestinya backend mengirimkan kode hex warna secara dinamis (contoh: `#FEE2E2` untuk latar belakang, `#B91C1C` untuk border).
