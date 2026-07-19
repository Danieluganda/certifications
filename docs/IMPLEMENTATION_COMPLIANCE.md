# CertPath 123 Implementation Compliance

This file tracks the Laravel application against the source-of-truth rule pack in `../rules`.

## Current Decision

The application is being built as a Laravel modular monolith using Blade for the first local version. PostgreSQL remains the target database, with SQLite allowed only for local bootstrapping until PostgreSQL is configured.

## Compliance Status

| Area | Source | Status | Notes |
|---|---|---|---|
| Product scope | PRD.md | Partial | Dashboard, roadmap, workspace, curriculum, projects, resources are started. Auth, planner, practice attempts, flashcards, budgeting, credentials, export remain. |
| Architecture | ARCHITECTURE.md | Partial | Domain folders and Eloquent models exist. More action classes, policies, jobs, and ADRs are needed. |
| Schema | SCHEMA.md | Partial | Initial relational tables exist. Topics, attempts, flashcards, labs, budgets, credentials, readiness snapshots, audit logs remain. |
| Rules | RULES.md | Partial | Seed import, tests, and domain models exist. Critical activation rules are being moved into action classes. |
| Design | DESIGN.md | Partial | Calm light UI with dark nav exists. Needs full navigation tree, empty states, forms, and accessibility pass. |
| Materials | MATERIALS_AND_PROJECTS.md | Partial | Seed data imports official links and project briefs. Full topic/question/lab depth remains. |

## Highest Priority Gaps

1. Authentication and user ownership policies.
2. Certification activation actions and tests.
3. Direct `user_id` ownership on user-owned tables.
4. Readiness calculator with guard conditions.
5. Study sessions and lesson completions.
6. Question bank with versioned questions.
7. Flashcards and spaced repetition service.
8. Budgeting and credential vault.
9. ADRs for architecture decisions.
10. PostgreSQL configuration and migration verification.

## Definition of Done for Current Foundation Slice

- Activation rules are implemented in domain actions.
- Tests cover one-primary-paid and free-credential activation limits.
- Database schema stores ownership directly for projects and resources.
- Dashboard/workspace still render from seeded relational data.
- Documentation records remaining gaps honestly.
