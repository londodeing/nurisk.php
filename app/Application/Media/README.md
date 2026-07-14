# Application Layer — Media

## Purpose
Orchestration layer between Presentation and Domain. Thin, stateless, no business logic.

## Responsibilities
- Accept commands/queries from controllers
- Coordinate domain objects with infrastructure
- Handle transactions, authorization checks
- Return DTOs to presentation layer

## Allowed Dependencies
- Domain layer (Contracts, Entities, ValueObjects, Exceptions)
- Laravel framework (only for infrastructure concern: DB, Auth)
- **Must NOT** contain business logic

## Directory Map
| Directory    | Content                                        |
|-------------|-------------------------------------------------|
| `Commands/` | CQRS write request objects (e.g. UploadMediaCommand) |
| `Queries/`  | CQRS read request objects (e.g. GetMediaQuery)  |
| `Handlers/` | Command/Query handlers (one per command/query)  |
| `DTOs/`     | Response/transfer objects (e.g. MediaResponse)  |
| `Services/` | Application services (thin orchestration)        |

## Rules
- Handlers inject Domain Contracts, NOT concrete implementations
- One handler per command/query (Single Responsibility)
- No business logic — delegate to Domain Services
- All public methods MUST have PHPDoc
