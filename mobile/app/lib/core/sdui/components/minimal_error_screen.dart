import 'package:flutter/material.dart';

class MinimalErrorScreen extends StatelessWidget {
  final String title;
  final String message;

  const MinimalErrorScreen({
    super.key,
    this.title = 'Something went wrong',
    this.message = 'An unexpected error occurred. Please try again.',
  });

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(title)),
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(32),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(Icons.cloud_off, size: 64, color: Colors.grey.shade400),
              const SizedBox(height: 16),
              Text(
                message,
                textAlign: TextAlign.center,
                style: TextStyle(color: Colors.grey.shade600, fontSize: 16),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
