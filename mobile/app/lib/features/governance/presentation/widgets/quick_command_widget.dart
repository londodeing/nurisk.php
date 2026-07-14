import 'package:flutter/material.dart';

class QuickCommandWidget extends StatelessWidget {
  const QuickCommandWidget({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'QUICK COMMAND',
            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
          ),
          const SizedBox(height: 12),
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: [
                _buildCommandChip(context, Icons.note_add, 'Buat SPK', Colors.blue),
                _buildCommandChip(context, Icons.flag, 'Aktivasi Posko', Colors.orange),
                _buildCommandChip(context, Icons.mail, 'Surat Masuk', Colors.purple),
                _buildCommandChip(context, Icons.add_circle, 'Draft Baru', Colors.green),
                _buildCommandChip(context, Icons.people, 'Delegasikan', Colors.teal),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCommandChip(BuildContext context, IconData icon, String label, Color color) {
    return Container(
      margin: const EdgeInsets.only(right: 8.0),
      child: ActionChip(
        avatar: Icon(icon, color: color, size: 16),
        label: Text(label),
        backgroundColor: color.withOpacity(0.1),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(8),
          side: BorderSide(color: color.withOpacity(0.3)),
        ),
        onPressed: () {
          // Open Draft Action
        },
      ),
    );
  }
}
