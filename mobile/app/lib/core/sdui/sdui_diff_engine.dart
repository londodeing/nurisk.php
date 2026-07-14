class SduiDiffEngine {
  /// Applies a JSON Patch (RFC 6902) style list of operations to a target Map
  static Map<String, dynamic> applyPatch(Map<String, dynamic> source, List<Map<String, dynamic>> patches) {
    // Deep copy source to avoid mutating original state directly
    final target = _deepCopyMap(source);

    for (var patch in patches) {
      final op = patch['op'];
      final path = patch['path'] as String;
      final value = patch['value'];

      final keys = path.split('/').where((k) => k.isNotEmpty).toList();
      if (keys.isEmpty) continue;

      if (op == 'replace') {
        _applyReplace(target, keys, value);
      } else if (op == 'add') {
        _applyAdd(target, keys, value);
      } else if (op == 'remove') {
        _applyRemove(target, keys);
      }
    }

    return target;
  }

  static void _applyReplace(dynamic target, List<String> keys, dynamic value) {
    for (int i = 0; i < keys.length - 1; i++) {
      final key = keys[i];
      if (target is Map) {
        target = target[key];
      } else if (target is List) {
        target = target[int.parse(key)];
      }
      if (target == null) return; // Path not found
    }

    final lastKey = keys.last;
    if (target is Map) {
      target[lastKey] = value;
    } else if (target is List) {
      target[int.parse(lastKey)] = value;
    }
  }

  static void _applyAdd(dynamic target, List<String> keys, dynamic value) {
    for (int i = 0; i < keys.length - 1; i++) {
      final key = keys[i];
      if (target is Map) {
        target = target[key] ??= {};
      } else if (target is List) {
        target = target[int.parse(key)];
      }
    }

    final lastKey = keys.last;
    if (target is Map) {
      target[lastKey] = value;
    } else if (target is List) {
      final idx = lastKey == '-' ? target.length : int.parse(lastKey);
      if (idx >= target.length) {
        target.add(value);
      } else {
        target.insert(idx, value);
      }
    }
  }

  static void _applyRemove(dynamic target, List<String> keys) {
    for (int i = 0; i < keys.length - 1; i++) {
      final key = keys[i];
      if (target is Map) {
        target = target[key];
      } else if (target is List) {
        target = target[int.parse(key)];
      }
      if (target == null) return;
    }

    final lastKey = keys.last;
    if (target is Map) {
      target.remove(lastKey);
    } else if (target is List) {
      target.removeAt(int.parse(lastKey));
    }
  }

  static Map<String, dynamic> _deepCopyMap(Map<String, dynamic> source) {
    return _deepCopy(source) as Map<String, dynamic>;
  }

  static dynamic _deepCopy(dynamic source) {
    if (source is Map) {
      final newMap = <String, dynamic>{};
      for (var entry in source.entries) {
        newMap[entry.key as String] = _deepCopy(entry.value);
      }
      return newMap;
    } else if (source is List) {
      return source.map((value) => _deepCopy(value)).toList();
    } else {
      return source; // primitive
    }
  }
}
