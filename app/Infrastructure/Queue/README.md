# Infrastructure — Queue

## Purpose
Shared queue job classes organized by domain.

## Structure
```
Queue/
├── Media/     (thumbnail generation, WebP conversion, etc.)
├── Notification/
└── Organization/
```

## Rules
- Jobs dispatch domain events and call Application handlers
- Jobs should be idempotent where possible
- Retry and failure handling follows system-wide policy
