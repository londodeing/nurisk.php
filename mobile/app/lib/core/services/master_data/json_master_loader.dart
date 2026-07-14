import 'dart:convert';
import 'package:flutter/services.dart';

class JsonMasterLoader {
  Future<Map<String, dynamic>> loadJson(String path) async {
    final string = await rootBundle.loadString(path);
    return json.decode(string) as Map<String, dynamic>;
  }

  Future<List<dynamic>> loadList(String path) async {
    final string = await rootBundle.loadString(path);
    return json.decode(string) as List<dynamic>;
  }
}
