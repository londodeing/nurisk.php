# Infrastructure Layer — Media

## Purpose
Technical implementation of Domain contracts. Framework-specific code lives here.

## Responsibilities
- Implement repository contracts (Eloquent)
- Implement storage providers (MinIO, Local)
- Map between Eloquent models and domain entities
- Provide service provider registrations

## Allowed Dependencies
- Domain layer (Contracts only)
- Laravel framework (Eloquent, Storage facade, Config)
- Third-party packages (MinIO SDK, etc.)

## Directory Map
| Directory            | Content                                          |
|---------------------|--------------------------------------------------|
| `Storage/Contracts/`| Storage provider interface                        |
| `Storage/Providers/`| Service provider for storage binding              |
| `Storage/Adapters/` | Concrete adapters (MinIO, Local, future S3/R2)   |
| `Persistence/Models/`| Eloquent models (data layer, no domain logic)    |
| `Persistence/Repositories/`| Eloquent implementations of domain contracts |
| `Persistence/Mappers/`| Map Eloquent models ↔ Domain entities           |

## Rules
- Must NOT be referenced by Domain layer
- Must NOT contain business logic
- All implementations MUST satisfy Domain Contracts
