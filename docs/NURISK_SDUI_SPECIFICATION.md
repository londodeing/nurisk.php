# NURISK SDUI SPECIFICATION (NSS)
## Indeks Utama (Root Index)

Berdasarkan keputusan arsitektur (Architectural Audit v3), Spesifikasi SDUI Nurisk (NSS) telah dipecah menjadi tiga sub-dokumen fungsional untuk memudahkan pengembangan dan isolasi kontrak.

Silakan merujuk ke dokumen berikut sebagai *Single Source of Truth* (SSOT):

1. **[NSS-CORE](file:///home/londo/nurisk/docs/NSS_CORE.md)**: Mendefinisikan 28+ Primitive resmi, struktur skema kanonikal, properti layout, dan sistem Design Token (Warna, Jarak, Tipografi).
2. **[NSS-ACTION](file:///home/londo/nurisk/docs/NSS_ACTION.md)**: Mendefinisikan kontrak navigasi, interaksi pengguna, pengiriman form (`submit`), dialog, bottom sheet, dan fungsi utilitas eksternal.
3. **[NSS-RUNTIME](file:///home/londo/nurisk/docs/NSS_RUNTIME.md)**: Mendefinisikan kontrol versi skema, pelacakan status (`id`, `version`, `dirty`), dan format payload untuk *Diff Engine* (Patching & Live Updates).

*Note: Dilarang keras menjadikan Laravel Builder atau kode Flutter Renderer sebagai acuan spesifikasi. Ketiga dokumen di atas adalah sumber hukum tertinggi.*
