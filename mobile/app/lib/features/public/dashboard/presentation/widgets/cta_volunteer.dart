import 'package:flutter/material.dart';

class CtaVolunteer extends StatelessWidget {
  const CtaVolunteer({super.key});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 12.0),
      padding: const EdgeInsets.all(20.0),
      decoration: BoxDecoration(
        color: Colors.green.shade700,
        borderRadius: BorderRadius.circular(12.0),
      ),
      child: Column(
        children: [
          const Icon(Icons.volunteer_activism, color: Colors.white, size: 48),
          const SizedBox(height: 12),
          const Text(
            'Ingin menjadi Relawan NU Peduli?',
            style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 16),
          ElevatedButton(
            onPressed: () {}, // Navigate to Volunteer Registration
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.white,
              foregroundColor: Colors.green.shade700,
            ),
            child: const Text('Daftar Sekarang'),
          ),
        ],
      ),
    );
  }
}
