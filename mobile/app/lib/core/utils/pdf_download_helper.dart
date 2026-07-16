import 'dart:io';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:open_filex/open_filex.dart';
import 'package:path_provider/path_provider.dart';

class PdfDownloadHelper {
  static Future<void> downloadAndOpenPdf({
    required BuildContext context,
    required Dio dio,
    required String endpoint,
    required String fileName,
  }) async {
    // Show Loading Dialog
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => const AlertDialog(
        content: Row(
          children: [
            CircularProgressIndicator(),
            SizedBox(width: 20),
            Expanded(child: Text("Mengunduh dokumen PDF...")),
          ],
        ),
      ),
    );

    try {
      // Download bytes
      final response = await dio.get<List<int>>(
        endpoint,
        options: Options(responseType: ResponseType.bytes),
      );

      // Close loading dialog
      if (context.mounted) Navigator.pop(context);

      if (response.data == null || response.data!.isEmpty) {
        throw Exception("File PDF kosong.");
      }

      // Save file locally
      final dir = await getApplicationDocumentsDirectory();
      final file = File('${dir.path}/$fileName');
      await file.writeAsBytes(response.data!);

      // Open file
      final openResult = await OpenFilex.open(file.path);
      if (openResult.type != ResultType.done && context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text("Gagal membuka PDF: ${openResult.message}. File disimpan di: ${file.path}"),
            backgroundColor: Colors.orange,
            duration: const Duration(seconds: 5),
          ),
        );
      }
    } catch (e) {
      // Close loading dialog if open
      if (context.mounted) {
        Navigator.pop(context);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text("Gagal mengunduh PDF: $e"),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }
}
