import 'package:dio/dio.dart';

class DioExceptionMapper {
  static String toUserMessage(Object error) {
    if (error is DioException) {
      return _mapDioException(error);
    }
    if (error is Exception) {
      final message = error.toString().replaceFirst('Exception: ', '');
      if (message.length > 100) {
        return 'Terjadi kesalahan sistem. Silakan coba lagi.';
      }
      return message;
    }
    return 'Terjadi kesalahan yang tidak diketahui.';
  }

  static String _mapDioException(DioException e) {
    switch (e.type) {
      case DioExceptionType.connectionTimeout:
        return 'Koneksi timeout. Periksa jaringan Anda dan coba lagi.';
      case DioExceptionType.sendTimeout:
        return 'Waktu pengiriman data habis. Coba lagi.';
      case DioExceptionType.receiveTimeout:
        return 'Server tidak merespon. Coba lagi nanti.';
      case DioExceptionType.connectionError:
        return 'Tidak dapat terhubung ke server. Periksa koneksi internet Anda.';
      case DioExceptionType.badResponse:
        final responseData = e.response?.data;
        if (responseData is Map && responseData['message'] != null) {
          return responseData['message'].toString();
        }
        return _mapBadResponse(e.response?.statusCode);
      case DioExceptionType.cancel:
        return 'Permintaan dibatalkan.';
      case DioExceptionType.badCertificate:
        return 'Koneksi tidak aman. Hubungi administrator.';
      default:
        return 'Terjadi kesalahan jaringan. Silakan coba lagi.';
    }
  }

  static String _mapBadResponse(int? statusCode) {
    switch (statusCode) {
      case 400:
        return 'Data yang dikirim tidak valid. Periksa kembali input Anda.';
      case 401:
        return 'Sesi Anda telah berakhir. Silakan login kembali.';
      case 403:
        return 'Anda tidak memiliki akses ke fitur ini.';
      case 404:
        return 'Data tidak ditemukan.';
      case 408:
        return 'Waktu permintaan habis. Coba lagi.';
      case 409:
        return 'Terjadi konflik data. Coba lagi.';
      case 422:
        return 'Data yang dikirim tidak valid. Periksa kembali input Anda.';
      case 429:
        return 'Terlalu banyak permintaan. Silakan tunggu beberapa saat.';
      case 500:
        return 'Terjadi kesalahan pada server. Tim kami sedang menanganinya.';
      case 502:
        return 'Server sedang sibuk. Coba lagi nanti.';
      case 503:
        return 'Server sedang dalam pemeliharaan. Coba lagi nanti.';
      default:
        return 'Terjadi kesalahan sistem (kode: $statusCode). Silakan coba lagi.';
    }
  }
}