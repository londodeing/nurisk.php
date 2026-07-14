import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../domain/models/operational_object.dart';

class SelectedOperationalObjectNotifier extends Notifier<OperationalObject?> {
  @override
  OperationalObject? build() => null;

  void selectObject(OperationalObject obj) {
    state = obj;
  }

  void clearSelection() {
    state = null;
  }
}

final selectedOperationalObjectProvider = NotifierProvider<SelectedOperationalObjectNotifier, OperationalObject?>(() {
  return SelectedOperationalObjectNotifier();
});
