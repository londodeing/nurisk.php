# Domain Layer — Media

## Purpose
Core business logic for the Media domain. Framework-agnostic, pure PHP.

## Responsibilities
- Define Media aggregate, entities, value objects
- Declare repository contracts (interfaces)
- Define domain events, exceptions, policies, specifications
- Enforce business rules

## Allowed Dependencies
- Domain layer only (no framework, no Eloquent, no HTTP, no I/O)

## Directory Map
| Directory       | Content                                      |
|-----------------|----------------------------------------------|
| `Entities/`     | Pure domain aggregates (not Eloquent models) |
| `ValueObjects/` | Immutable typed value objects                |
| `Contracts/`    | Repository interfaces, service contracts     |
| `Events/`       | Domain events (MediaUploaded, MediaDeleted)  |
| `Exceptions/`   | Domain-specific exceptions                   |
| `Services/`     | Domain services (pure business logic, no I/O)|
| `Factories/`    | Aggregate/entity factories                   |
| `Policies/`     | Domain policies (business rules)             |
| `Enums/`        | Backed enums replacing magic strings         |
| `Specifications/`| Domain specifications for validation        |

## Rules
- No `use Illuminate\*` imports
- No `Storage::`, `DB::`, `Eloquent` calls
- All public methods MUST have PHPDoc
- All classes MUST declare `declare(strict_types=1)`
