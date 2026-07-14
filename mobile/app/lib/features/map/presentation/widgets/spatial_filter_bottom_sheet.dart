import 'package:flutter/material.dart';

class SpatialFilterBottomSheet extends StatefulWidget {
  final Function(Map<String, dynamic>) onApplyFilters;

  const SpatialFilterBottomSheet({Key? key, required this.onApplyFilters}) : super(key: key);

  @override
  State<SpatialFilterBottomSheet> createState() => _SpatialFilterBottomSheetState();
}

class _SpatialFilterBottomSheetState extends State<SpatialFilterBottomSheet> {
  double _radius = 10.0;
  String _severity = 'Semua';
  String _status = 'Semua';
  bool _useMyLoc = false;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          const Text(
            'Filter Spasial Operasional',
            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
          ),
          const SizedBox(height: 16),
          
          SwitchListTile(
            contentPadding: EdgeInsets.zero,
            title: const Text('Di sekitar lokasi saya'),
            value: _useMyLoc,
            onChanged: (val) => setState(() => _useMyLoc = val),
          ),
          
          if (_useMyLoc) ...[
            Text('Radius: ${_radius.toInt()} km'),
            Slider(
              value: _radius,
              min: 1,
              max: 100,
              divisions: 100,
              label: '${_radius.toInt()} km',
              onChanged: (val) => setState(() => _radius = val),
            ),
          ],
          
          const SizedBox(height: 8),
          DropdownButtonFormField<String>(
            decoration: const InputDecoration(labelText: 'Tingkat Keparahan (Severity)'),
            value: _severity,
            items: ['Semua', 'Rendah', 'Sedang', 'Tinggi']
                .map((e) => DropdownMenuItem(value: e, child: Text(e)))
                .toList(),
            onChanged: (val) => setState(() => _severity = val!),
          ),
          
          const SizedBox(height: 16),
          DropdownButtonFormField<String>(
            decoration: const InputDecoration(labelText: 'Status Operasional'),
            value: _status,
            items: ['Semua', 'Aktif', 'Monitoring', 'Selesai']
                .map((e) => DropdownMenuItem(value: e, child: Text(e)))
                .toList(),
            onChanged: (val) => setState(() => _status = val!),
          ),

          const SizedBox(height: 24),
          ElevatedButton(
            onPressed: () {
              widget.onApplyFilters({
                'radius': _useMyLoc ? _radius : null,
                'severity': _severity != 'Semua' ? _severity.toUpperCase() : null,
                'status': _status != 'Semua' ? _status.toUpperCase() : null,
              });
              Navigator.pop(context);
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: Theme.of(context).primaryColor,
              foregroundColor: Colors.white,
            ),
            child: const Text('Terapkan Filter'),
          )
        ],
      ),
    );
  }
}
