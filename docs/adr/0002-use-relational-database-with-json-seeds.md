# ADR 0002: Use Relational Database With JSON Seeds

## Status

Accepted

## Context

The rules require PostgreSQL as the primary storage for certifications, attempts, lessons, progress, budgets, and credentials. JSON is allowed for imports, seeds, flexible metadata, and backups.

## Decision

Store operational data in relational tables. Keep initial certification catalogue data in `database/data/certifications/certpath-seed.json`, then import it through Laravel seeders.

SQLite is allowed only for local bootstrapping until PostgreSQL is configured.

## Alternatives

- Runtime JSON storage: rejected because connected records, history, and reporting would become unsafe.
- PostgreSQL-only local setup immediately: deferred to avoid blocking local development while the schema is still stabilising.

## Consequences

The app reads from the database, not the seed JSON, during normal requests.
