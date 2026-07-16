import 'package:flutter/material.dart';

class DonationQrCard extends StatelessWidget {
  const DonationQrCard({super.key});

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Padding(
      padding: const EdgeInsets.fromLTRB(14, 16, 14, 4),
      child: Container(
        clipBehavior: Clip.antiAlias,
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(16),
          gradient: LinearGradient(
            colors: isDark
                ? [const Color(0xFF0D2818), const Color(0xFF0A1F12)]
                : [const Color(0xFFE8F5E9), const Color(0xFFC8E6C9)],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
          border: Border.all(
            color: isDark
                ? const Color(0xFF0F6B3C).withValues(alpha: 0.3)
                : const Color(0xFF0F6B3C).withValues(alpha: 0.12),
            width: 0.5,
          ),
          boxShadow: [
            BoxShadow(
              color: const Color(0xFF0F6B3C).withValues(alpha: isDark ? 0.1 : 0.06),
              blurRadius: 10,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Padding(
          padding: const EdgeInsets.fromLTRB(16, 16, 16, 18),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    children: [
                      Container(
                        width: 36,
                        height: 36,
                        decoration: BoxDecoration(
                          color: const Color(0xFF0F6B3C).withValues(alpha: 0.15),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: const Icon(
                          Icons.favorite_rounded,
                          color: Color(0xFF0F6B3C),
                          size: 20,
                        ),
                      ),
                      const SizedBox(width: 10),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Donasi NU Peduli',
                            style: TextStyle(
                              fontSize: 15,
                              fontWeight: FontWeight.w700,
                              color: isDark ? Colors.white : const Color(0xFF1A1D1F),
                            ),
                          ),
                          Text(
                            'Laziznu Jawa Tengah',
                            style: TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.w500,
                              color: isDark ? Colors.white54 : Colors.black45,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: const Color(0xFF0F6B3C).withValues(alpha: 0.12),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Text(
                      'SYARIAH',
                      style: TextStyle(
                        fontSize: 8,
                        fontWeight: FontWeight.w700,
                        color: const Color(0xFF0F6B3C),
                        letterSpacing: 0.8,
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              Row(
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  Expanded(
                    flex: 3,
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Salurkan donasi terbaik Anda melalui\nLAZISNU Jawa Tengah',
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w500,
                            color: isDark ? Colors.white70 : Colors.black54,
                            height: 1.4,
                          ),
                        ),
                        const SizedBox(height: 12),
                        _infoChip(Icons.account_balance_rounded, 'Bank Syariah Indonesia', isDark),
                        const SizedBox(height: 6),
                        _infoChip(Icons.numbers_rounded, '7111.20.00002.1', isDark, bold: true),
                        const SizedBox(height: 6),
                        _infoChip(Icons.person_rounded, 'LAZISNU JATENG', isDark),
                        const SizedBox(height: 14),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                          decoration: BoxDecoration(
                            color: const Color(0xFF0F6B3C).withValues(alpha: 0.1),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.content_copy_rounded, size: 14, color: Color(0xFF0F6B3C)),
                              const SizedBox(width: 6),
                              const Text(
                                'Salin Nomor Rekening',
                                style: TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.w600,
                                  color: Color(0xFF0F6B3C),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    flex: 2,
                    child: _mockQrCode(isDark),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _infoChip(IconData icon, String text, bool isDark, {bool bold = false}) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, size: 11, color: isDark ? Colors.white38 : Colors.black26),
        const SizedBox(width: 5),
        Flexible(
          child: Text(
            text,
            style: TextStyle(
              fontSize: 11,
              fontWeight: bold ? FontWeight.w700 : FontWeight.w500,
              color: isDark ? Colors.white70 : Colors.black45,
            ),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
        ),
      ],
    );
  }

  Widget _mockQrCode(bool isDark) {
    return Container(
      width: 100,
      height: 100,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: Colors.black.withValues(alpha: 0.08),
          width: 1,
        ),
      ),
      padding: const EdgeInsets.all(10),
      child: CustomPaint(
        painter: _QrCodePainter(),
        size: const Size(80, 80),
      ),
    );
  }
}

class _QrCodePainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()..color = const Color(0xFF1A1D1F);
    final cellW = size.width / 21;
    final cellH = size.height / 21;

    void fill(int row, int col) {
      canvas.drawRect(
        Rect.fromLTWH(col * cellW, row * cellH, cellW, cellH),
        paint,
      );
    }

    const pattern = [
      [0,0,0,0,0,0,0,0,3,0,0,0,0,0,0,0,0,0,0,0,0],
      [0,1,1,1,1,1,0,0,0,0,0,0,0,1,1,1,1,1,1,0,0],
      [0,1,0,0,0,1,0,0,0,0,0,0,0,1,0,0,0,0,1,0,0],
      [0,1,0,0,0,1,0,0,0,0,0,0,0,1,0,0,0,0,1,0,0],
      [0,1,0,0,0,1,0,0,0,0,0,0,0,1,0,0,0,0,1,0,0],
      [0,1,1,1,1,1,0,0,0,0,0,0,0,1,1,1,1,1,1,0,0],
      [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
      [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
      [3,0,0,0,0,0,0,0,0,1,1,1,0,0,0,0,0,0,0,0,3],
      [0,0,0,0,0,0,0,0,1,0,0,0,1,0,0,0,0,0,0,0,0],
      [0,0,0,0,0,0,0,0,1,0,0,0,1,0,0,0,0,0,0,0,0],
      [0,0,0,0,0,0,0,0,1,1,1,1,0,0,0,0,0,0,0,0,0],
      [3,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,3],
      [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
      [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
      [0,1,1,1,1,1,0,0,0,0,0,0,0,1,1,1,1,1,1,0,0],
      [0,1,0,0,0,1,0,0,0,0,0,0,0,1,0,0,0,0,1,0,0],
      [0,1,0,0,0,1,0,0,0,0,0,0,0,1,0,0,0,0,1,0,0],
      [0,1,0,0,0,1,0,0,0,0,0,0,0,1,0,0,0,0,1,0,0],
      [0,1,1,1,1,1,0,0,0,0,0,0,0,1,1,1,1,1,1,0,0],
      [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
    ];

    for (int r = 0; r < 21; r++) {
      for (int c = 0; c < 21; c++) {
        final v = pattern[r][c];
        if (v == 1) {
          canvas.drawRect(
            Rect.fromLTWH(c * cellW, r * cellH, cellW, cellH),
            paint,
          );
        }
      }
    }
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}
