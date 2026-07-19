# CertPath 123 Rules Compliance Audit

Audit date: 2026-07-20

This audit compares the current Laravel app against every file in `../rules`, including the Private Tutor and first-class Study Planner amendments added on 2026-07-20.

## Overall Status

The app is a working MVP, but it is not yet at literal 100% rule coverage.

Implemented strongly:

- Laravel app with real relational database storage.
- MySQL configuration in `.env`.
- Authenticated personal learner account.
- Separate dashboard pages and separate certification workspace pages.
- Paid, free, and skill-specialisation tracks.
- Certification catalogue seeded from rule-backed JSON files.
- GIS, knowledge systems, search, analytics, and all-certs catalogue coverage.
- Lesson pages, notes, completions, progress updates, resources, projects, evidence upload, flashcards, basic quizzes, readiness snapshots, savings, and credentials.
- Official-link-first study-material policy with original summaries, labs, quizzes, and projects.
- Test suite covering ownership, activation rules, catalogue, curriculum, study sessions, lessons, flashcards, practice, readiness, resources, projects, budgets, credentials, exports, GIS amendment, and study-material imports.

Current seeded database snapshot after `php artisan migrate:fresh --seed`:

- Certifications: 49
- Lessons: 34
- Projects: 42
- Resources: 40
- Questions: 30

## File-By-File Result

| Rule file | Status | Notes |
| --- | --- | --- |
| `README.md` | Mostly complete | Laravel app exists and rule files have been followed as build inputs. |
| `PRD.md` | Partial, strong MVP | Core workflows exist. Remaining gaps: objective versioning UI, first-class planner generation, rest-day availability, full mock-exam blueprinting, labs as first-class records, vouchers, notifications, AI-assisted content workflow, reports, full-text notes search, and Markdown note export. |
| `ARCHITECTURE.md` | Mostly complete | Domain folders, controllers, actions, models, migrations, seeders, and tests exist. Remaining gaps: dedicated Revision domain, notification service, AI boundary service, search service, documented ADR files, and production backup/restore mechanics. |
| `SCHEMA.md` | Partial | Many MVP tables exist, but several named tables are not implemented yet. See schema gap section. |
| `RULES.md` | Partial, good engineering hygiene | Tests and domain organization are solid. Remaining gaps include audit logs, rate limiting, signed private file URLs, stricter database constraints, full accessibility verification, and question validation for all question types. |
| `DESIGN.md` | Mostly complete | Rich CSS and responsive layout exist. Needs browser-based visual QA against all stated design expectations before claiming 100%. |
| `design_ref.md` | Mostly complete | App is no longer a one-page shell; dashboard and certification workspaces have separate pages. Needs detailed visual parity pass against reference standards. |
| `refrence_apps.md` | Partial | The app borrows the correct standard: structured learning, references, examples, quizzes, and progress. Still weaker than mature reference apps on depth, search, explanations, exercises, and content browsing. |
| `app_tree.md` | Partial | Uses database as primary app storage and JSON only as import seeds. Uses domain folders, but the exact recommended tree is not fully built, and PostgreSQL was superseded by the user's MySQL instruction. |
| `all_certs.md` | Complete for catalogue | All 40 listed cert/credential items are represented, plus 8 skill specialisations from the amendment. |
| `AMENDMENT_GIS_KNOWLEDGE_SYSTEMS.md` | Partial | Catalogue and core projects are represented. Dedicated amendment tables are missing: `specialisations`, `certification_specialisation`, `datasets`, `ontology_resources`, `search_indexes`, `analytics_properties`. Some specialist tracks still need lessons/resources/projects. |
| `MATERIALS_AND_PROJECTS.md` | Mostly complete | Official sources, priority content seed, and main projects are imported. Some long-tail project depth can still be expanded. |
| `study_materials.md` | Mostly complete | Extra projects/resources from this file are now imported, including PMP 2026 outline, AI agent research project, explainable classifier, and LFD121 CI/CD pipeline. |
| `Private_Tutor_module.md` | Foundation started | Tutor domain models and required foundation tables now exist: sessions, messages, recommendations, misconceptions, and feedback. Remaining gaps: tutor workspace, Ask Tutor buttons, provider-independent AI boundary, approved-source retrieval service, guardrails, knowledge checks, incorrect-answer review workflow, and planner integration UI. |
| `first-class_Study_Planner_module.md` | Foundation started | Study goals, richer study-session fields, session tasks, project milestones, study streaks, and planner recommendations now exist. Remaining gaps: Today flow, Continue logic, timetable/week/month views, availability, missed-session rescheduling, revision queue, dynamic scheduling, streak/quest UI, and tutor integration. |

## Schema Gaps

Tables required by `SCHEMA.md` but not currently created:

- `audit_logs`
- `certification_objective_versions`
- `earned_credentials` (implemented as `credentials`)
- `exam_budgets` (partly folded into `certifications`)
- `labs`
- `notifications`
- `progress_snapshots`
- `quiz_blueprints`
- `study_plans`
- `study_session_events`
- `taggables`
- `tags`
- `topic_prerequisites`
- `vouchers`
- `weekly_availabilities`

Tables required by `AMENDMENT_GIS_KNOWLEDGE_SYSTEMS.md` but not currently created:

- `specialisations`
- `certification_specialisation`
- `datasets`
- `ontology_resources`
- `search_indexes`
- `analytics_properties`

## Content Gaps

Certifications currently seeded with no lesson, project, or resource coverage:

- `ARCGIS-PRO-FOUNDATION`
- `ARCGIS-DEVELOPER-FOUNDATION`
- `ARCGIS-PYTHON`
- `ARCGIS-ONLINE-ADMIN`
- `FME-PROFESSIONAL`
- `GISP`
- `AGRI-DATASETS`
- `BIBLIO-DATASETS`
- `GIS-REMOTE-SENSING`
- `POSTGIS-SPATIAL-DATA`
- `R-ANALYTICS`
- `SEARCH-IR`
- `TOGAF-FOUNDATION`
- `TOGAF-PRACTITIONER`

Partial content coverage:

- `PCAD`: projects exist, but no lessons/resources.
- `ELASTIC-ENGINEER`: project/resource exist, but no lessons.
- `APACHE-SOLR`: resource exists, but no lessons/projects.
- `EO-COLLEGE`: resource exists, but no lessons/projects.
- `QGIS-TRAINING`: resource exists, but no lessons/projects.
- `AWS-EDUCATE-DATABASES`: lesson exists, but no direct project/resource.
- `AWS-EDUCATE-CLOUD-OPERATIONS`: lesson exists, but no direct project/resource.
- `AWS-EDUCATE-NETWORKING`: lesson/resource exist, but no direct project.

## Highest-Priority Remaining Build Slices

1. Add missing schema backbone tables: objective versions, labs, project milestones, study plans, weekly availability, vouchers, notifications, audit logs, tags, and progress snapshots.
2. Add GIS/search/data amendment tables: specialisations, datasets, ontology resources, search indexes, analytics properties.
3. Build first-class labs and project milestones in the UI.
4. Build objective versioning for PMP 2026 and other exam outlines.
5. Expand specialist study content for ArcGIS, FME, GISP, PCAD, Elastic, Solr, datasets, PostGIS, R analytics, TOGAF split credentials, EO College, and QGIS.
6. Add planner generation from weekly availability, active tracks, revision needs, rest days, and project sessions.
7. Add vouchers and exam budget records separate from the certification row.
8. Add notifications for due reviews, study sessions, exam dates, voucher expiry, and free credential deadlines.
9. Add audit logging for activation, primary change, budget changes, attempt submission, evidence upload, and credential recording.
10. Add visual/browser QA evidence for `DESIGN.md`, `design_ref.md`, and `refrence_apps.md`.
11. Build the first-class Study Planner UI and intelligence: Today, timetable, daily targets, weekly goals, monthly milestones, availability, missed sessions, dynamic rescheduling, streaks, quests, and recommendation acceptance.
12. Build the Private Tutor workflows: source-grounded explanations, knowledge checks, incorrect-answer review, tutor history, feedback UI, guardrails, and planner integration.

## Current Verdict

The app is roughly at a strong MVP level, not full rule completion.

Estimated compliance:

- Catalogue/content identity: 90%
- Study-material source policy: 85%
- Core PRD workflows: 65%
- Architecture organization: 75%
- Schema completeness: 55%
- GIS/search/knowledge amendment depth: 60%
- Design/reference-app standard: 70% pending browser QA
- Study Planner module: 35%
- Private Tutor module: 15%
- Overall: about 65%

To honestly call it 100%, the schema and UI gaps above need to be implemented, tested, and verified.
