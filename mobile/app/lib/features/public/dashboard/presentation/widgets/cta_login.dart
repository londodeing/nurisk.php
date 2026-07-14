import 'package:flutter/material.dart';

class CtaLogin extends StatelessWidget {
  const CtaLogin({super.key});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 12.0),
      padding: const EdgeInsets.all(20.0),
      decoration: BoxDecoration(
        color: Colors.blueGrey.shade800,
        borderRadius: BorderRadius.circular(12.0),
      ),
      child: Column(
        children: [
          const Icon(Icons.admin_panel_settings, color: Colors.white, size: 48),
          const SizedBox(height: 12),
          const Text(
            'Masuk sebagai Pengurus',
            style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 16),
          ElevatedButton(
            onPressed: () {}, // Navigate to Auth Module
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.white,
              foregroundColor: Colors.blueGrey.shade800,
            ),
            child: const Text('Login'),
          ),
        ],
      ),
    );
  }
}
