# SDUI Contract Test Generator (AT-002)
## Konsep & Blueprint Otomasi Validasi NSS

Sebagai mekanisme pertahanan utama agar Laravel dan Flutter tidak melenceng dari NURISK SDUI Specification (NSS), kita tidak akan mengandalkan pengecekan manual. Kita akan membangun **Test Generator**.

---

## 1. Tujuan Kontrak Test
1.  **Backend Assurance**: Memastikan Builder Laravel tidak pernah memproduksi JSON yang menyalahi Canonical Schema (e.g., tidak ada pengiriman objek margin bertipe string, atau pengiriman radius berupa integer).
2.  **Frontend Assurance**: Memastikan Parser Flutter dan Registry tidak melakukan fallback ke *UnknownComponent* bila menerima Primitive resmi dari NSS.

---

## 2. Struktur Validator Skema (JSON Schema)

Kita akan memanfaatkan `Opis\JsonSchema` (di sisi PHP) atau Dart `json_schema` (di sisi Flutter) yang akan memvalidasi payload terhadap satu file skema statis `NSS_schema_v1.json`.

**Contoh Skema Validasi (NSS_schema_v1.json):**
```json
{
  "$id": "https://nurisk.id/sdui/schema/v1.json",
  "type": "object",
  "properties": {
    "type": { "type": "string" },
    "id": { "type": "string" },
    "visible": { "type": "boolean" },
    "enabled": { "type": "boolean" },
    "props": {
      "type": "object",
      "properties": {
        "padding": {
          "type": "object",
          "properties": {
            "all": { "type": "number" },
            "x": { "type": "number" },
            "y": { "type": "number" },
            "t": { "type": "number" },
            "b": { "type": "number" },
            "l": { "type": "number" },
            "r": { "type": "number" }
          },
          "additionalProperties": false
        },
        "background": {
          "enum": ["primary", "secondary", "surface", "background", "danger", "warning", "info", "success", "transparent"]
        },
        "radius": {
          "enum": ["none", "sm", "md", "lg", "xl", "full"]
        }
      }
    }
  },
  "required": ["type", "id"]
}
```

---

## 3. Implementasi Laravel (Backend Test Generator)

Kita akan membuat test suite abstrak di Laravel: `SduiContractTestCase`.

Setiap builder (misal: `IdentityBuilder`) akan memiliki unit test yang memanggil fungsinya, lalu JSON output-nya secara otomatis divalidasi dengan `NSS_schema_v1.json`.
```php
public function test_identity_builder_complies_with_nss() {
    $builder = new IdentityBuilder();
    $json = $builder->build();
    
    // Assertion bawaan dari SduiContractTestCase
    $this->assertCompliesWithNss($json);
}
```

---

## 4. Implementasi Flutter (Frontend Test Generator)

Di sisi Flutter, Test Generator akan mengambil list canonical JSON dari NSS, lalu meng-inject-nya langsung ke dalam Parser dan memastikan output akhirnya adalah Widget yang valid (bukan *UnknownComponent*).

```dart
testWidgets('NSS Contract: Parser must render Container correctly without fallback', (tester) async {
  final canonicalJson = {
    "type": "Container",
    "id": "test",
    "props": { "background": "surface", "radius": "lg" }
  };
  
  final node = SduiNode.fromJson(canonicalJson);
  await tester.pumpWidget(MaterialApp(home: SduiRenderer(node: node)));
  
  // Memastikan bahwa yang dirender bukanlah SduiUnknownComponent
  expect(find.byType(SduiUnknownComponent), findsNothing);
  expect(find.byType(SduiContainer), findsOneWidget);
});
```

*(Implementasi teknis spesifik Test Generator ini akan dikerjakan pada saat Fase Refactoring dimulai).*
