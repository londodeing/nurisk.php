import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/api/auth_api_client.dart';

class PinVerificationDialog extends ConsumerStatefulWidget {
  const PinVerificationDialog({Key? key}) : super(key: key);

  static Future<bool?> show(BuildContext context) {
    return showDialog<bool>(
      context: context,
      barrierDismissible: false,
      builder: (context) => const PinVerificationDialog(),
    );
  }

  @override
  ConsumerState<PinVerificationDialog> createState() => _PinVerificationDialogState();
}

class _PinVerificationDialogState extends ConsumerState<PinVerificationDialog> {
  final _pinCtrl = TextEditingController();
  bool _isLoading = false;
  String? _errorMsg;

  Future<void> _verifyPin() async {
    if (_pinCtrl.text.length != 6) {
      setState(() => _errorMsg = 'PIN harus 6 digit');
      return;
    }

    setState(() {
      _isLoading = true;
      _errorMsg = null;
    });

    try {
      final dio = ref.read(authApiClientProvider);
      final res = await dio.post('auth/pin/verify', data: {
        'pin': _pinCtrl.text,
      });

      if (!mounted) return;

      if (res.statusCode == 200) {
        Navigator.pop(context, true);
      } else {
        setState(() => _errorMsg = 'PIN Salah');
      }
    } catch (e) {
      if (!mounted) return;

      setState(() => _errorMsg = 'PIN Salah');
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  void dispose() {
    _pinCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      title: const Text('Verifikasi PIN'),
      content: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Text('Masukkan 6-digit PIN untuk otorisasi tindakan ini.'),
          const SizedBox(height: 16),
          TextField(
            controller: _pinCtrl,
            keyboardType: TextInputType.number,
            obscureText: true,
            maxLength: 6,
            textAlign: TextAlign.center,
            style: const TextStyle(letterSpacing: 8, fontSize: 24, fontWeight: FontWeight.bold),
            decoration: const InputDecoration(
              counterText: '',
              border: OutlineInputBorder(),
            ),
          ),
          if (_errorMsg != null) ...[
            const SizedBox(height: 8),
            Text(_errorMsg!, style: const TextStyle(color: Colors.red)),
          ]
        ],
      ),
      actions: [
        TextButton(
          onPressed: _isLoading ? null : () => Navigator.pop(context, false),
          child: const Text('Batal'),
        ),
        ElevatedButton(
          onPressed: _isLoading ? null : _verifyPin,
          child: _isLoading
              ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2))
              : const Text('Verifikasi'),
        )
      ],
    );
  }
}
