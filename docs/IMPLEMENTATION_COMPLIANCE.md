# CertPath 123 Implementation Compliance

This file tracks the Laravel application against the source-of-truth rule pack in `../rules`.

## Current Decision

The application is being built as a Laravel modular monolith using Blade for the first local version. PostgreSQL remains the target database, with SQLite allowed only for local bootstrapping until PostgreSQL is configured.

## Compliance Status

| Area | Source | Status | Notes |
|---|---|---|---|
| Product scope | PRD.md | Complete | MVP acceptance criteria are implemented: dashboard, roadmap, workspace, curriculum, study planner, projects, project evidence, resource library, flashcards, topic quizzes, timed mock attempts, weak-domain view, guarded readiness snapshots, budgeting, credential vault, personal backup export, auth, certification creation, primary paid activation, free credential activation, domain/topic creation, lesson completion, and notes. |
| Architecture | ARCHITECTURE.md | Partial | Domain folders and Eloquent models exist. More action classes, policies, jobs, and ADRs are needed. |
| Schema | SCHEMA.md | Partial | Initial relational tables exist. Topics, attempts, flashcards, labs, budgets, credentials, readiness snapshots, audit logs remain. |
| Rules | RULES.md | Partial | Seed import, tests, and domain models exist. Critical activation rules are being moved into action classes. |
| Design | DESIGN.md | Partial | Calm light UI with dark nav exists. Needs full navigation tree, empty states, forms, and accessibility pass. |
| Materials | MATERIALS_AND_PROJECTS.md | Partial | Seed data imports official links and project briefs. Full topic/question/lab depth remains. |

## Highest Priority Gaps

1. Audit logging and rate limiting.
2. PostgreSQL production verification.
3. Complete ARCHITECTURE.md/RULES.md/DESIGN.md/SCHEMA.md hardening passes after the PRD MVP.

## Definition of Done for Current Foundation Slice

- Activation rules are implemented in domain actions.
- Tests cover one-primary-paid and free-credential activation limits.
- Database schema stores ownership directly for projects and resources.
- Dashboard/workspace still render from seeded relational data.
- Documentation records remaining gaps honestly.

## PRD.md Pass

Status: complete for MVP acceptance criteria.

Completed from MVP acceptance criteria:

- Authentication.
- Add both a paid certification and a free credential.
- Mark one paid certification as primary.
- Activate up to two free credentials.
- Create domains and ordered topics.
- Schedule and complete study sessions.
- Add resources from the UI with source, trust, copyright, status, rating, and optional domain/topic attachment.
- Create and review flashcards with simple spaced repetition.
- Take topic quizzes from versioned lesson questions.
- Take timed mock exams.
- View weak domains and mastery.
- View a guarded readiness score.
- Track exam savings.
- Create a project and upload evidence.
- Record an earned certificate.
- Export or back up personal learning data.
- Add lesson notes.
- Complete lessons with confidence and server-side progress updates.

Remaining from MVP acceptance criteria:

- None.
