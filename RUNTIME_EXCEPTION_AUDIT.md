# RUNTIME EXCEPTION AUDIT REPORT

This audit documents the critical runtime exceptions identified in the NURISK application stack.

## 1. `StateError: Bad state: No ProviderScope found`
- **Location**: [main.dart:38](file:///home/londo/nurisk/mobile/app/lib/main.dart#L38)
- **Call Stack**:
  ```
  #0      ProviderScope.containerOf (package:flutter_riverpod/src/core/provider_scope.dart:105:7)
  #1      ConsumerStatefulElement.read (package:flutter_riverpod/src/core/consumer.dart:558:26)
  #2      _NuriskAppState.initState (package:nurisk_mobile/main.dart:38:19)
  #3      StatefulElement._firstBuild (package:flutter/src/widgets/framework.dart:5950:55)
  ```
- **Cause**: The application entry widget (`NuriskApp`) is built in testing without wrapping the root tree in a `ProviderScope`.

## 2. `AssertionError: RuntimeServices not initialized`
- **Location**: [runtime_initializer.dart:63](file:///home/londo/nurisk/mobile/app/lib/core/runtime/runtime_initializer.dart#L63)
- **Call Stack**:
  ```
  #0      RuntimeServicesScope.instance (package:nurisk_mobile/core/runtime/runtime_initializer.dart:63:5)
  #1      runtimeServicesProvider (package:nurisk_mobile/core/runtime/runtime_initializer.dart:71:30)
  #2      SplashScreenState._tryNavigate (package:nurisk_mobile/core/splash/splash_screen.dart:36:15)
  ```
- **Cause**: Reading `runtimeServicesProvider` to perform navigation inside `SplashScreen` before the asynchronous `RuntimeInitializer.initialize` completes.

## 3. `TypeError` / `NullThrownError` (Null check operator used on a null value)
- **Location**: [runtime_initializer.dart:64](file:///home/londo/nurisk/mobile/app/lib/core/runtime/runtime_initializer.dart#L64)
- **Cause**: In production/release builds, assertions are stripped. The getter `_instance!` executes without the assertion check, throwing a Null Check exception since `_instance` is `null` prior to initialization completion.

## 4. `RangeError (Index out of range)`
- **Location**: [sdui_container.dart:16](file:///home/londo/nurisk/mobile/app/lib/core/sdui/components/sdui_container.dart#L16)
- **Cause**: Accessing elements 2 and 3 of the padding/margin list (`paddingArr[2]`, `paddingArr[3]`) when the JSON payload returns a list with fewer than 4 items (e.g. `[8, 8]`).

## 5. `TypeError` (type 'List<dynamic>' is not a subtype of type 'Map<String, dynamic>')
- **Location**: [sdui_node.dart:16](file:///home/londo/nurisk/mobile/app/lib/core/sdui/sdui_node.dart#L16)
- **Cause**: Accessing properties like `props` or `actions` when they are returned as empty JSON arrays `[]` (from PHP empty arrays) instead of JSON objects `{}`.
