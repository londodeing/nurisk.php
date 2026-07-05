# WEATHER TERRITORY MAPPING

## Territory Resolution

Weather forecast must map to NURISK territory hierarchy:

```
PWNU (Jawa Tengah)
  ├── PCNU Kab. Kudus  (id=12, unit.id_wilayah=3319)
  │     ├── MWC ...
  │     └── ...
  ├── PCNU Kab. Jepara (id=13, unit.id_wilayah=3320)
  │     └── ...
  └── ... (35 PCNU)
```

## Territory Code Convention

| Scope | Format | Example | Coverage |
|-------|--------|---------|----------|
| PWNU | `pwnu:0` | `pwnu:0` | Central coord (-7.5, 110.0) |
| PCNU | `pcnu:{id_pcnu}` | `pcnu:12` | Kabupaten-level centroid |
| MWC | `mwc:{id_mwc}` | `mwc:45` | Kecamatan-level centroid |

## Coordinate Resolution

### Geographic Centroid Mapping

| Territory | Coordinate Source | Detail |
|-----------|------------------|--------|
| PWNU | Fixed: -7.5, 110.0 | Central Java centroid |
| PCNU | `organisasi_pcnu → unit → id_wilayah → wilayah_kabupaten → centroid` | Compute from kabupaten geometry or use kab center |
| MWC | `organisasi_mwc → id_kecamatan → wilayah_kecamatan → centroid` | Use kecamatan center |

### Alternative: Populasi-Weighted
For PCNU with multiple kecamatan, use population-weighted average of kecamatan coordinates (future enhancement).

## Location-to-Territory Resolution

```
User GPS (lat, lon)
  ↓
Reverse Geocode (cached)
  ↓
Determine id_kab (kabupaten code)
  ↓
Find PCNU where unit.id_wilayah = id_kab
  ↓
territory_code = pcnu:{id_pcnu}
```

## Territory Weather API

### Internal API (auth-protected)
```
GET /api/internal/weather/current   → { territory_code: "pcnu:12" }
GET /api/internal/weather/hourly    → { territory_code: "pcnu:12" }
GET /api/internal/weather/daily     → { territory_code: "pcnu:12" }
GET /api/internal/weather/risk      → { territory_code: "pcnu:12" }
```

### Territory Override
Admin can manually assign different coordinates for a PCNU's weather forecast:
```
weather_snapshots.territory_code = pcnu:12
weather coordinates → lat/lon stored in snapshot metadata
```

## Initial Territory Seed List

Generated from database:
```sql
SELECT
    CONCAT('pcnu:', p.id_pcnu) AS territory_code,
    'pcnu' AS territory_type,
    p.id_pcnu AS territory_id,
    p.nama_pcnu AS name,
    u.id_wilayah AS kab_code
FROM organisasi_pcnu p
JOIN organisasi_unit u ON p.id_unit = u.id_unit
WHERE u.tipe_unit = 'pcnu';
```
