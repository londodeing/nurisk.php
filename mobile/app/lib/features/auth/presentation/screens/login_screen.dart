import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:go_router/go_router.dart';
import 'package:nurisk_mobile/core/router/app_router.dart';
import 'package:nurisk_mobile/core/theme/nurisk_colors.dart';
import 'package:nurisk_mobile/core/theme/nurisk_radius.dart';
import 'package:nurisk_mobile/core/theme/nurisk_spacing.dart';
import '../providers/auth_provider.dart';

class LoginScreen extends ConsumerStatefulWidget {
  const LoginScreen({super.key});

  @override
  ConsumerState<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends ConsumerState<LoginScreen> {
  final _phoneController = TextEditingController();
  final _passwordController = TextEditingController();
  final _formKey = GlobalKey<FormState>();
  bool _obscurePassword = true;

  @override
  void dispose() {
    _phoneController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (_formKey.currentState?.validate() ?? false) {
      await ref.read(authProvider.notifier).login(
            _phoneController.text.trim(),
            _passwordController.text,
          );
      if (mounted) {
        final auth = ref.read(authProvider);
        if (auth.hasValue && auth.value != null) {
          context.go(RoutePaths.profile);
        }
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authProvider);
    final isLoading = authState.isLoading;

    ref.listen(authProvider, (previous, next) {
      if (next.hasError && !next.isLoading) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(next.error.toString()),
            backgroundColor: NuriskColors.emergencyRed,
          ),
        );
      }
    });

    return Scaffold(
      backgroundColor: NuriskColors.bgWhite,
      body: SafeArea(
        child: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const SizedBox(height: 60),
              SvgPicture.asset(
                'assets/login/login.svg',
                height: 160,
                fit: BoxFit.contain,
                placeholderBuilder: (context) => Container(
                  height: 160,
                  alignment: Alignment.center,
                  child: Icon(
                    Icons.shield_rounded,
                    size: 80,
                    color: NuriskColors.primary600,
                  ),
                ),
              ),
              const SizedBox(height: 28),
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: NuriskSpacing.xl),
                child: Form(
                  key: _formKey,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      Text(
                        'Masuk ke NURISK',
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          fontSize: 24,
                          fontWeight: FontWeight.w700,
                          color: NuriskColors.neutral900,
                        ),
                      ),
                      const SizedBox(height: NuriskSpacing.sm),
                      Text(
                        'Silakan masukkan nomor handphone dan kata sandi Anda',
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          fontSize: 14,
                          color: NuriskColors.neutral600,
                        ),
                      ),
                      const SizedBox(height: NuriskSpacing.xxl),
                  TextFormField(
                    controller: _phoneController,
                    keyboardType: TextInputType.phone,
                    decoration: InputDecoration(
                      labelText: 'Nomor Handphone',
                      prefixIcon: Icon(Icons.phone_rounded, color: NuriskColors.neutral500),
                      filled: true,
                      fillColor: NuriskColors.neutral50,
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(NuriskRadius.xs),
                        borderSide: BorderSide(color: NuriskColors.neutral300),
                      ),
                      enabledBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(NuriskRadius.xs),
                        borderSide: BorderSide(color: NuriskColors.neutral300),
                      ),
                      focusedBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(NuriskRadius.xs),
                        borderSide: const BorderSide(color: NuriskColors.primary600, width: 1.5),
                      ),
                      errorBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(NuriskRadius.xs),
                        borderSide: const BorderSide(color: NuriskColors.emergencyRed, width: 1.5),
                      ),
                    ),
                    validator: (value) {
                      if (value == null || value.isEmpty) {
                        return 'Nomor handphone tidak boleh kosong';
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: NuriskSpacing.lg),
                  TextFormField(
                    controller: _passwordController,
                    obscureText: _obscurePassword,
                    decoration: InputDecoration(
                      labelText: 'Kata Sandi',
                      prefixIcon: Icon(Icons.lock_rounded, color: NuriskColors.neutral500),
                      suffixIcon: IconButton(
                        icon: Icon(
                          _obscurePassword ? Icons.visibility_rounded : Icons.visibility_off_rounded,
                          color: NuriskColors.neutral500,
                        ),
                        onPressed: () {
                          setState(() {
                            _obscurePassword = !_obscurePassword;
                          });
                        },
                      ),
                      filled: true,
                      fillColor: NuriskColors.neutral50,
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(NuriskRadius.xs),
                        borderSide: BorderSide(color: NuriskColors.neutral300),
                      ),
                      enabledBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(NuriskRadius.xs),
                        borderSide: BorderSide(color: NuriskColors.neutral300),
                      ),
                      focusedBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(NuriskRadius.xs),
                        borderSide: const BorderSide(color: NuriskColors.primary600, width: 1.5),
                      ),
                      errorBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(NuriskRadius.xs),
                        borderSide: const BorderSide(color: NuriskColors.emergencyRed, width: 1.5),
                      ),
                    ),
                    validator: (value) {
                      if (value == null || value.isEmpty) {
                        return 'Kata sandi tidak boleh kosong';
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: NuriskSpacing.xl),
                  SizedBox(
                    height: 52,
                    child: ElevatedButton(
                      onPressed: isLoading ? null : _submit,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: NuriskColors.primary600,
                        foregroundColor: Colors.white,
                        disabledBackgroundColor: NuriskColors.primary600.withValues(alpha: 0.38),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(NuriskRadius.sm),
                        ),
                      ),
                      child: isLoading
                          ? const SizedBox(
                              width: 20,
                              height: 20,
                              child: CircularProgressIndicator(
                                strokeWidth: 2,
                                valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                              ),
                            )
                          : const Text(
                              'MASUK',
                              style: TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.w700,
                                letterSpacing: 1,
                              ),
                            ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    ),
    ),
  );
  }
}
