# RUNTIME PERMISSION FLOW — NURISK

---

## CURRENT STATE (BROKEN)

```
┌─────────────────────────────────────────────────────────────────┐
│                    CURRENT PERMISSION FLOW                       │
│                                                                  │
│  ImagePicker(pickImage: camera)                                  │
│  │                                                                │
│  ├── NO permission check → PlatformException → CRASH            │
│  │                                                                │
│  ImagePicker(pickImage: gallery)                                 │
│  │                                                                │
│  ├── NO permission check (Android 13+) → SecurityException →    │
│  │    CRASH                                                      │
│  │                                                                │
│  Geolocator (getCurrentPosition)                                 │
│  │                                                                │
│  └── ✅ HAS permission check → works correctly                   │
│                                                                  │
│  APP IS INCONSISTENT: GPS checks permissions, camera/gallery    │
│  does NOT.                                                       │
└─────────────────────────────────────────────────────────────────┘
```

---

## TARGET STATE

```
┌─────────────────────────────────────────────────────────────────┐
│                    TARGET PERMISSION FLOW                        │
│                                                                  │
│  ┌─────────────────────┐                                        │
│  │  Unified Permission  │                                        │
│  │  Service (Riverpod)  │                                        │
│  │  permission_handler  │                                        │
│  └──────────┬──────────┘                                        │
│             │                                                     │
│  ┌──────────▼──────────────────────────────────────────────────┐ │
│  │  checkPermissionStatus(Permission.camera)                   │ │
│  │                                                              │ │
│  │  ┌──────────────┐    ┌──────────────────┐    ┌──────────┐  │ │
│  │  │  GRANTED     │───▶│  Proceed to      │───▶│  Use     │  │ │
│  │  │              │    │  ImagePicker      │    │  Camera  │  │ │
│  │  └──────────────┘    └──────────────────┘    └──────────┘  │ │
│  │                                                              │ │
│  │  ┌──────────────┐    ┌──────────────────┐    ┌──────────┐  │ │
│  │  │  DENIED      │───▶│  request()       │───▶│  Show    │  │ │
│  │  │              │    │  Show rationale   │    │  Error   │  │ │
│  │  └──────────────┘    └──────────────────┘    └──────────┘  │ │
│  │                                                              │ │
│  │  ┌──────────────┐    ┌──────────────────┐    ┌──────────┐  │ │
│  │  │ PERMANENTLY  │───▶│  Show settings   │───▶│  Open    │  │ │
│  │  │ DENIED       │    │  dialog          │    │  System  │  │ │
│  │  └──────────────┘    └──────────────────┘    │  Settings│  │ │
│  │                                               └──────────┘  │ │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                                  │
│  PERMISSIONS MANAGED:                                            │
│  ┌──────────────┬──────────────┬──────────────┬────────────────┐ │
│  │  CAMERA      │  LOCATION    │  STORAGE     │  NOTIFICATION  │ │
│  │              │  (FINE +     │  (IMAGES)    │                │ │
│  │              │   COARSE)    │              │                │ │
│  └──────────────┴──────────────┴──────────────┴────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
```

---

## PERMISSION REQUEST SEQUENCE

### Camera Permission

```
User taps "Ambil Foto"
  → permission_handler check(Permission.camera)
    ├── GRANTED → ImagePicker(pickImage: camera)
    ├── DENIED → request(Permission.camera)
    │   ├── GRANTED → ImagePicker(pickImage: camera)
    │   └── DENIED → showSnackBar("Camera permission required")
    │       └── PERMANENTLY_DENIED → showDialog → "Open Settings"
    └── PERMANENTLY_DENIED → showDialog → "Open Settings"
```

### Gallery/Storage Permission

```
User taps "Dari Galeri"
  → permission_handler check(Permission.photos) [Android 13+]
    OR check(Permission.storage) [Android <13]
    ├── GRANTED → ImagePicker(pickImage: gallery)
    ├── DENIED → request(permission)
    │   ├── GRANTED → ImagePicker(pickImage: gallery)
    │   └── DENIED → showSnackBar("Gallery permission required")
    └── PERMANENTLY_DENIED → showDialog → "Open Settings"
```

### Location Permission

```
User taps "Dapatkan Lokasi Saya"
  → Geolocator.isLocationServiceEnabled()
    ├── FALSE → showSnackBar("Aktifkan GPS")
    └── TRUE → Geolocator.checkPermission()
        ├── GRANTED → Geolocator.getCurrentPosition()
        ├── DENIED → Geolocator.requestPermission()
        │   ├── GRANTED → Geolocator.getCurrentPosition()
        │   ├── DENIED → showSnackBar("Izin lokasi ditolak")
        │   └── DENIED_FOREVER → showDialog → "Open Settings"
        └── DENIED_FOREVER → showDialog → "Open Settings"
```

### Notification Permission (Android 13+)

```
App start
  → permission_handler check(Permission.notification)
    ├── GRANTED → proceed
    ├── DENIED → request(Permission.notification)
    │   ├── GRANTED → proceed
    │   └── DENIED → disable notification features
    └── PERMANENTLY_DENIED → disable notification features
```

---

## ANDROID MANIFEST PERMISSIONS

```xml
<!-- Required -->
<uses-permission android:name="android.permission.INTERNET" />
<uses-permission android:name="android.permission.ACCESS_FINE_LOCATION" />
<uses-permission android:name="android.permission.ACCESS_COARSE_LOCATION" />
<uses-permission android:name="android.permission.CAMERA" />

<!-- Android 12+ -->
<uses-permission android:name="android.permission.FOREGROUND_SERVICE" />

<!-- Android 13+ (Granular Media Permission) -->
<uses-permission android:name="android.permission.READ_MEDIA_IMAGES" />
<uses-permission android:name="android.permission.READ_MEDIA_VIDEO" />

<!-- Android 13+ (Notification Permission) -->
<uses-permission android:name="android.permission.POST_NOTIFICATIONS" />

<!-- Android 10 and below (legacy, still needed for some devices) -->
<uses-permission android:name="android.permission.READ_EXTERNAL_STORAGE"
    android:maxSdkVersion="32" />
<uses-permission android:name="android.permission.WRITE_EXTERNAL_STORAGE"
    android:maxSdkVersion="32" />
```

---

## FileProvider (Required for Camera)

```xml
<application ...>
    <provider
        android:name="androidx.core.content.FileProvider"
        android:authorities="${applicationId}.fileprovider"
        android:exported="false"
        android:grantUriPermissions="true">
        <meta-data
            android:name="android.support.FILE_PROVIDER_PATHS"
            android:resource="@xml/file_paths" />
    </provider>
</application>
```

`res/xml/file_paths.xml`:
```xml
<?xml version="1.0" encoding="utf-8"?>
<paths>
    <cache-path name="cache" path="." />
    <external-cache-path name="external_cache" path="." />
    <files-path name="files" path="." />
</paths>
```
