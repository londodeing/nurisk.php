import 'package:flutter/material.dart';

class ResourceScreen extends StatelessWidget {
  const ResourceScreen({super.key});

  static const _items = [
    _ResourceItem('🏥', 'Posko Kesehatan',
        'Lokasi dan informasi posko kesehatan, rumah sakit rujukan, dan fasilitas medis terdekat.'),
    _ResourceItem('🚛', 'Distribusi Logistik',
        'Informasi pusat distribusi bantuan, gudang logistik, dan rantai pasokan.'),
    _ResourceItem('🚑', 'Tim Reaksi Cepat',
        'Database TRC (Tim Reaksi Cepat) NU yang siap diterjunkan ke lokasi bencana.'),
    _ResourceItem('📋', 'SOP & Panduan',
        'Panduan tanggap darurat, prosedur evakuasi, dan standar operasi penanggulangan bencana.'),
    _ResourceItem('📡', 'Komunikasi Darurat',
        'Saluran komunikasi darurat, frekuensi radio, dan kontak personel kunci.'),
    _ResourceItem('👥', 'Relawan Terdaftar',
        'Data relawan NU yang sudah terverifikasi dan siap ditugaskan berdasarkan keahlian.'),
    _ResourceItem('🍲', 'Dapur Umum',
        'Lokasi dapur umum, kebutuhan bahan makanan, dan jadwal distribusi makanan.',
        comingSoon: true),
    _ResourceItem('🏠', 'Pengungsian',
        'Data lokasi pengungsian, kapasitas, dan kebutuhan dasar pengungsi.',
        comingSoon: true),
    _ResourceItem('📊', 'Statistik & Laporan',
        'Dashboard statistik bencana, laporan periodik, dan analisis data kebencanaan.',
        comingSoon: true),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF5F6F8),
      appBar: AppBar(
        title: const Text('Resource'),
        centerTitle: true,
        elevation: 0,
        scrolledUnderElevation: 1,
      ),
      body: LayoutBuilder(
        builder: (context, constraints) {
          final wide = constraints.maxWidth > 600;
          final crossAxisCount = wide ? 3 : 2;

          return SingleChildScrollView(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 100),
            child: GridView.builder(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: crossAxisCount,
                crossAxisSpacing: 12,
                mainAxisSpacing: 12,
                childAspectRatio: wide ? 1.35 : 1.1,
              ),
              itemCount: _items.length,
              itemBuilder: (context, index) => _ResourceCard(item: _items[index]),
            ),
          );
        },
      ),
    );
  }
}

class _ResourceItem {
  final String icon;
  final String title;
  final String description;
  final bool comingSoon;
  const _ResourceItem(this.icon, this.title, this.description, {this.comingSoon = false});
}

class _ResourceCard extends StatefulWidget {
  final _ResourceItem item;
  const _ResourceCard({required this.item});

  @override
  State<_ResourceCard> createState() => _ResourceCardState();
}

class _ResourceCardState extends State<_ResourceCard> {
  bool _hovered = false;

  @override
  Widget build(BuildContext context) {
    final item = widget.item;

    return MouseRegion(
      onEnter: (_) => setState(() => _hovered = true),
      onExit: (_) => setState(() => _hovered = false),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 250),
        transform: _hovered ? Matrix4.translationValues(0.0, -2.0, 0.0) : Matrix4.identity(),
        curve: Curves.easeOutCubic,
        padding: const EdgeInsets.fromLTRB(14, 14, 14, 16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(
            color: _hovered ? const Color(0xFF0F6B3C).withValues(alpha: 0.5) : const Color(0xFFEEF0F4),
            width: 1,
          ),
          boxShadow: [
            BoxShadow(
              color: _hovered
                  ? const Color(0xFF0F6B3C).withValues(alpha: 0.08)
                  : Colors.black.withValues(alpha: 0.04),
              blurRadius: _hovered ? 20 : 8,
              offset: Offset(0, _hovered ? 6 : 2),
            ),
          ],
        ),
        child: Stack(
          clipBehavior: Clip.none,
          children: [
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(item.icon, style: const TextStyle(fontSize: 26)),
                const SizedBox(height: 10),
                Text(
                  item.title,
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w700,
                    color: Color(0xFF1A1D1F),
                    letterSpacing: -0.2,
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: 4),
                Text(
                  item.description,
                  style: TextStyle(
                    fontSize: 11.5,
                    color: Colors.grey.shade500,
                    height: 1.35,
                  ),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
              ],
            ),
            if (item.comingSoon)
              Positioned(
                top: -6,
                right: -6,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                  decoration: BoxDecoration(
                    color: Colors.grey.shade200,
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(Icons.schedule, size: 8, color: Colors.grey.shade600),
                      const SizedBox(width: 2),
                      Text(
                        'Segera Hadir',
                        style: TextStyle(
                          fontSize: 8.5,
                          fontWeight: FontWeight.w700,
                          color: Colors.grey.shade600,
                          height: 1.3,
                          letterSpacing: 0.3,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }
}
