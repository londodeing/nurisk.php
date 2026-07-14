# NURISK MOBILE — MOBILE PERMISSION MATRIX
## Document 08: Permission Matrix per Role per Feature
**Version**: 1.0.0 | **Status**: PRE-PRODUCTION | **Domain**: Platform-Wide

---

## LEGENDA

| Simbol | Arti |
|--------|------|
| ✅ | Diizinkan |
| ❌ | Tidak diizinkan |
| ⚠️ | Kondisional (tergantung mandate/territory) |
| 🔑 | Hanya jika punya authority spesifik |
| 📋 | Hanya milik sendiri |

**Role Columns**:
| Col | Role |
|-----|------|
| SA | Super Admin |
| PW | Admin PWNU |
| PC | Admin PCNU |
| OP | Operator |
| RE | Relawan |

---

## 1. AUTHENTICATION & PROFILE

| Feature | SA | PW | PC | OP | RE |
|---------|----|----|----|----|-----|
| Login | ✅ | ✅ | ✅ | ✅ | ✅ |
| Logout | ✅ | ✅ | ✅ | ✅ | ✅ |
| View own profile | ✅ | ✅ | ✅ | ✅ | ✅ |
| Edit own profile | ✅ | ✅ | ✅ | ✅ | ✅ |
| Change password | ✅ | ✅ | ✅ | ✅ | ✅ |
| View device list | ✅ | ✅ | ✅ | ✅ | ✅ |
| Logout all devices | ✅ | ✅ | ✅ | ✅ | ✅ |
| Enable biometric | ✅ | ✅ | ✅ | ✅ | ✅ |
| Enable PIN | ✅ | ✅ | ✅ | ✅ | ✅ |
| View other users | ✅ | ⚠️ | ⚠️ | ❌ | ❌ |
| Edit other users | ✅ | ❌ | ❌ | ❌ | ❌ |
| Approve user registration | ✅ | ⚠️ | ⚠️ | ❌ | ❌ |

---

## 2. GOVERNANCE — MANDATE

| Feature | SA | PW | PC | OP | RE |
|---------|----|----|----|----|-----|
| View own mandate | ✅ | ✅ | ✅ | ✅ | ✅ |
| Switch active mandate | ✅ | ✅ | ✅ | ✅ | ✅ |
| View all mandates | ✅ | ⚠️ | ⚠️ | ❌ | ❌ |
| Create mandate | ✅ | ❌ | ❌ | ❌ | ❌ |
| Update mandate | ✅ | ❌ | ❌ | ❌ | ❌ |
| Delete mandate | ✅ | ❌ | ❌ | ❌ | ❌ |
| View mandate authorities | ✅ | ✅ | ✅ | ❌ | ❌ |

---

## 3. GOVERNANCE — DELEGATION

| Feature | SA | PW | PC | OP | RE |
|---------|----|----|----|----|-----|
| View own delegations | ✅ | ✅ | ✅ | ❌ | ❌ |
| Create delegation | ✅ | 🔑 | 🔑 | ❌ | ❌ |
| Update delegation | ✅ | 🔑 | 🔑 | ❌ | ❌ |
| Cancel delegation | ✅ | 🔑 | 🔑 | ❌ | ❌ |
| View all delegations | ✅ | ⚠️ | ❌ | ❌ | ❌ |

> **🔑 Kondisi**: Hanya jika mandate aktif memiliki authority `delegate`

---

## 4. GOVERNANCE — APPROVAL

| Feature | SA | PW | PC | OP | RE |
|---------|----|----|----|----|-----|
| View governance inbox | ✅ | ✅ | ✅ | ❌ | ❌ |
| Approve SPK | ✅ | 🔑 | 🔑 | ❌ | ❌ |
| Reject SPK | ✅ | 🔑 | 🔑 | ❌ | ❌ |
| Approve mission | ✅ | 🔑 | 🔑 | ❌ | ❌ |
| Reject mission | ✅ | 🔑 | 🔑 | ❌ | ❌ |
| Approve asset usage | ✅ | 🔑 | 🔑 | ❌ | ❌ |
| Reject asset usage | ✅ | 🔑 | 🔑 | ❌ | ❌ |
| Approve mobilisasi | ✅ | 🔑 | 🔑 | ❌ | ❌ |
| Emergency override | ✅ | 🔑 | 🔑 | ❌ | ❌ |

> **🔑 Kondisi**: Hanya jika mandate aktif memiliki authority yang sesuai

---

## 5. GOVERNANCE — SURAT & PARAF

| Feature | SA | PW | PC | OP | RE |
|---------|----|----|----|----|-----|
| View surat | ✅ | ✅ | ✅ | ⚠️ | ❌ |
| Create surat | ✅ | ✅ | ✅ | ⚠️ | ❌ |
| Edit surat (draft) | ✅ | 📋 | 📋 | 📋 | ❌ |
| Delete surat (draft) | ✅ | 📋 | 📋 | 📋 | ❌ |
| Submit surat untuk paraf | ✅ | ✅ | ✅ | ⚠️ | ❌ |
| Paraf surat (approve) | ✅ | 🔑 | 🔑 | ❌ | ❌ |
| Tolak paraf | ✅ | 🔑 | 🔑 | ❌ | ❌ |
| Finalisasi surat | ✅ | 🔑 | 🔑 | ❌ | ❌ |
| Download surat | ✅ | ✅ | ✅ | ⚠️ | ❌ |
| Export surat PDF | ✅ | ✅ | ✅ | ❌ | ❌ |

---

## 6. GOVERNANCE — STRUKTUR ORGANISASI

| Feature | SA | PW | PC | OP | RE |
|---------|----|----|----|----|-----|
| View org tree | ✅ | ✅ | ✅ | ❌ | ❌ |
| View node detail | ✅ | ✅ | ✅ | ❌ | ❌ |
| Create node | ✅ | ❌ | ❌ | ❌ | ❌ |
| Update node | ✅ | ❌ | ❌ | ❌ | ❌ |
| Delete node | ✅ | ❌ | ❌ | ❌ | ❌ |
| View SK | ✅ | ✅ | ✅ | ❌ | ❌ |
| Audit Trail view | ✅ | ✅ | ✅ | ❌ | ❌ |

---

## 7. OPERASIONAL — INSIDEN

| Feature | SA | PW | PC | OP | RE |
|---------|----|----|----|----|-----|
| View insiden list | ✅ | ✅ | ⚠️ | ✅ | ❌ |
| View insiden detail | ✅ | ✅ | ⚠️ | ✅ | ❌ |
| Create insiden | ✅ | ✅ | ✅ | ✅ | ❌ |
| Update insiden | ✅ | ✅ | ⚠️ | ✅ | ❌ |
| Update status insiden | ✅ | ✅ | ⚠️ | ✅ | ❌ |
| Delete insiden | ✅ | ❌ | ❌ | ❌ | ❌ |
| Escalate insiden | ✅ | 🔑 | 🔑 | ❌ | ❌ |
| Unlock insiden | ✅ | ❌ | ❌ | ❌ | ❌ |
| View insiden peta | ✅ | ✅ | ⚠️ | ✅ | ✅ |
| Tambah assessment | ✅ | ✅ | ⚠️ | ✅ | ❌ |
| Buat sitrep | ✅ | ✅ | ⚠️ | ✅ | ❌ |
| View sitrep | ✅ | ✅ | ⚠️ | ✅ | ❌ |

> **⚠️ PCNU**: Hanya insiden di wilayah territory mandate aktif

---

## 8. OPERASIONAL — POSKO (POSAJU)

| Feature | SA | PW | PC | OP | RE |
|---------|----|----|----|----|-----|
| View posko list | ✅ | ✅ | ⚠️ | ✅ | ❌ |
| View posko detail | ✅ | ✅ | ⚠️ | ✅ | ⚠️ |
| Create posko | ✅ | ✅ | ✅ | ❌ | ❌ |
| Update posko | ✅ | ✅ | ⚠️ | ✅ | ❌ |
| Activate posko | ✅ | ✅ | ⚠️ | ❌ | ❌ |
| Extend posko | ✅ | ✅ | ⚠️ | ❌ | ❌ |
| Close posko | ✅ | ✅ | ⚠️ | ❌ | ❌ |

> **⚠️ RE**: Hanya jika relawan bertugas di posko tersebut

---

## 9. OPERASIONAL — PENUGASAN (MISI)

| Feature | SA | PW | PC | OP | RE |
|---------|----|----|----|----|-----|
| View penugasan | ✅ | ✅ | ⚠️ | ✅ | 📋 |
| Create penugasan | ✅ | ✅ | ⚠️ | ✅ | ❌ |
| Bulk penugasan | ✅ | ✅ | ⚠️ | ✅ | ❌ |
| Update status penugasan | ✅ | ✅ | ⚠️ | ✅ | 📋 |
| Delete penugasan | ✅ | ⚠️ | ❌ | ❌ | ❌ |
| View penugasan history | ✅ | ✅ | ⚠️ | ✅ | 📋 |
| Approve penugasan | ✅ | 🔑 | 🔑 | ❌ | ❌ |

> **📋 RE**: Hanya penugasan milik dirinya sendiri

---

## 10. OPERASIONAL — MOBILISASI

| Feature | SA | PW | PC | OP | RE |
|---------|----|----|----|----|-----|
| View mobilisasi | ✅ | ✅ | ⚠️ | ✅ | ❌ |
| Create mobilisasi | ✅ | ✅ | ⚠️ | ✅ | ❌ |
| Approve mobilisasi | ✅ | 🔑 | 🔑 | ❌ | ❌ |
| Cancel mobilisasi | ✅ | ✅ | ⚠️ | ✅ | ❌ |
| Track mobilisasi (depart/arrive) | ✅ | ✅ | ⚠️ | ✅ | ❌ |
| Finish mobilisasi | ✅ | ✅ | ⚠️ | ✅ | ❌ |

---

## 11. LAPORAN KEJADIAN

| Feature | SA | PW | PC | OP | RE |
|---------|----|----|----|----|-----|
| Submit laporan | ✅ | ✅ | ✅ | ✅ | ✅ |
| View laporan list | ✅ | ✅ | ⚠️ | ✅ | 📋 |
| Validasi laporan | ✅ | ✅ | ⚠️ | ✅ | ❌ |
| Tolak laporan | ✅ | ✅ | ⚠️ | ✅ | ❌ |
| View laporan peta | ✅ | ✅ | ⚠️ | ✅ | ❌ |

---

## 12. ASET

| Feature | SA | PW | PC | OP | RE |
|---------|----|----|----|----|-----|
| View aset | ✅ | ✅ | ⚠️ | ✅ | ❌ |
| Create aset | ✅ | ✅ | ⚠️ | ✅ | ❌ |
| Update aset | ✅ | ✅ | ⚠️ | ✅ | ❌ |
| Delete aset | ✅ | ❌ | ❌ | ❌ | ❌ |
| Update status aset | ✅ | ✅ | ⚠️ | ✅ | ❌ |
| Approve penggunaan aset | ✅ | 🔑 | 🔑 | ❌ | ❌ |
| Export aset | ✅ | ✅ | ⚠️ | ❌ | ❌ |

---

## 13. MEDIA

| Feature | SA | PW | PC | OP | RE |
|---------|----|----|----|----|-----|
| Upload foto (laporan) | ✅ | ✅ | ✅ | ✅ | ✅ |
| Upload foto (insiden) | ✅ | ✅ | ✅ | ✅ | ❌ |
| View media | ✅ | ✅ | ✅ | ✅ | 📋 |
| Delete media | ✅ | 📋 | 📋 | 📋 | 📋 |
| Replace media | ✅ | 📋 | 📋 | 📋 | 📋 |
| Download media | ✅ | ✅ | ✅ | ✅ | ✅ |
| Sync media offline | ✅ | ✅ | ✅ | ✅ | ✅ |

---

## 14. SYNC & OFFLINE

| Feature | SA | PW | PC | OP | RE |
|---------|----|----|----|----|-----|
| Offline mode (read) | ✅ | ✅ | ✅ | ✅ | ✅ |
| Manual sync | ✅ | ✅ | ✅ | ✅ | ✅ |
| Background sync | ✅ | ✅ | ✅ | ✅ | ✅ |
| Upload queue (media) | ✅ | ✅ | ✅ | ✅ | ✅ |
| View sync status | ✅ | ✅ | ✅ | ✅ | ✅ |
| Force full sync | ✅ | ✅ | ✅ | ❌ | ❌ |
| View sync metrics | ✅ | ✅ | ❌ | ❌ | ❌ |

---

## 15. ADMINISTRASI (Super Admin Only)

| Feature | SA | PW | PC | OP | RE |
|---------|----|----|----|----|-----|
| Manage users | ✅ | ❌ | ❌ | ❌ | ❌ |
| Approve user registration | ✅ | ❌ | ❌ | ❌ | ❌ |
| View admin metrics | ✅ | ❌ | ❌ | ❌ | ❌ |
| Manage pengguna-jabatan | ✅ | ❌ | ❌ | ❌ | ❌ |
| Approve role application | ✅ | ❌ | ❌ | ❌ | ❌ |
| View all role applications | ✅ | ❌ | ❌ | ❌ | ❌ |

---

## 16. IMPLEMENTASI PERMISSION CHECK DI FLUTTER

Permission check dilakukan berlapis:

### Layer 1: Route Guard
```
Go Router `redirect` callback
  └── Check permissionProvider.hasPermission('feature.action')
        └── Jika false → redirect ke /403
```

### Layer 2: Widget Guard
```dart
// Semua tombol/aksi sensitif dibungkus:
PermissionGuard(
  permission: 'governance.approval.create',
  child: ElevatedButton(...)
)
```

### Layer 3: API Response
```
HTTP 403 → Tampilkan Permission Denied screen
           (backend adalah sumber kebenaran akhir)
```

---

*Document Status: DRAFT — Permission code (kolom 'Izin yang Diperlukan') perlu difinalisasi setelah endpoint G-05 tersedia*
