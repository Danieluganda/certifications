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
| `PRD.md` | Partial, stronger MVP | Core workflows exist. Objective versions, labs, vouchers, notifications, study plans, weekly availability, quiz blueprints, audit logs, tags, and progress snapshots now have schema/model foundations. Remaining gaps: UI workflows for these foundations, first-class planner generation, rest-day scheduling, AI-assisted content workflow, reports, full-text notes search, and Markdown note export. |
| `ARCHITECTURE.md` | Mostly complete | Domain folders, controllers, actions, models, migrations, seeders, and tests exist. Remaining gaps: dedicated Revision domain, notification service implementation, AI boundary service, search service, documented ADR files, and production backup/restore mechanics. |
| `SCHEMA.md` | Mostly complete | Required backbone tables are now present. Remaining gaps are mostly alias/compatibility decisions, stronger DB constraints, and UI/service usage of the tables. |
| `RULES.md` | Partial, good engineering hygiene | Tests and domain organization are solid. Remaining gaps include audit logs, rate limiting, signed private file URLs, stricter database constraints, full accessibility verification, and question validation for all question types. |
| `DESIGN.md` | Mostly complete | Rich CSS and responsive layout exist. Needs browser-based visual QA against all stated design expectations before claiming 100%. |
| `design_ref.md` | Mostly complete | App is no longer a one-page shell; dashboard and certification workspaces have separate pages. Needs detailed visual parity pass against reference standards. |
| `refrence_apps.md` | Partial | The app borrows the correct standard: structured learning, references, examples, quizzes, and progress. Still weaker than mature reference apps on depth, search, explanations, exercises, and content browsing. |
| `app_tree.md` | Partial | Uses database as primary app storage and JSON only as import seeds. Uses domain folders, but the exact recommended tree is not fully built, and PostgreSQL was superseded by the user's MySQL instruction. |
| `all_certs.md` | Complete for catalogue | All 40 listed cert/credential items are represented, plus 8 skill specialisations from the amendment. |
| `AMENDMENT_GIS_KNOWLEDGE_SYSTEMS.md` | Mostly complete foundation | Catalogue, core projects, dedicated amendment tables, seeded specialisation records, starter datasets, ontology resources, search index metadata, analytics property metadata, and a Specialisations dashboard page now exist. Remaining gaps are create/edit workflows, specialised workspace tabs, and deeper lessons/resources/projects for several specialist tracks. |
| `MATERIALS_AND_PROJECTS.md` | Mostly complete | Official sources, priority content seed, and main projects are imported. Some long-tail project depth can still be expanded. |
| `study_materials.md` | Mostly complete | Extra projects/resources from this file are now imported, including PMP 2026 outline, AI agent research project, explainable classifier, and LFD121 CI/CD pipeline. |
| `Private_Tutor_module.md` | Foundation started | Tutor domain models and required foundation tables now exist: sessions, messages, recommendations, misconceptions, and feedback. Remaining gaps: tutor workspace, Ask Tutor buttons, provider-independent AI boundary, approved-source retrieval service, guardrails, knowledge checks, incorrect-answer review workflow, and planner integration UI. |
| `first-class_Study_Planner_module.md` | Partial, improving | Study goals, richer study-session fields, session tasks, study plans, weekly availability, session events, project milestones, study streaks, planner recommendations, Today entry point, Continue action, timetable list, goal form, milestone list, workload summary, and recommendation display now exist. Remaining gaps: full week/month views, availability UI, missed-session rescheduling, revision queue, dynamic scheduling, quest UI, recommendation acceptance, and tutor integration. |

## Schema Gaps

Tables required by `SCHEMA.md` are now created for the main schema backbone:

- `audit_logs`
- `certification_objective_versions`
- `exam_budgets`
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

Remaining schema compatibility notes:

- `earned_credentials` is still implemented as `credentials`; either an alias migration or a rename decision is needed before claiming literal table-name parity.
- The app uses MySQL per user instruction, while `SCHEMA.md` originally recommended PostgreSQL. MySQL-compatible JSON and indexes are used.
- Several business rules still need stronger database-level checks or service enforcement, including time ranges, one current objective version, one active budget, and confidence ranges.

Tables required by `AMENDMENT_GIS_KNOWLEDGE_SYSTEMS.md` are now created:

- `specialisations`
- `certification_specialisation`
- `datasets`
- `ontology_resources`
- `search_indexes`
- `analytics_properties`

Remaining GIS/search/data compatibility notes:

- The tables are present with domain models, seeded default records, and relationship tests.
- The dashboard has a Specialisations page showing pathways, datasets, ontology resources, search lab metadata, and analytics properties.
- Create/edit workflows and certification workspace tabs for Datasets, Maps, Ontology, and Search Lab still need implementation.

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

1. Build first-class labs, weekly availability, vouchers, notifications, quiz blueprints, progress snapshots, tags, and audit logs into the UI/service workflows.
2. Build objective versioning screens and seed PMP 2026 plus other exam objective versions.
3. Expand specialist study content for ArcGIS, FME, GISP, PCAD, Elastic, Solr, datasets, PostGIS, R analytics, TOGAF split credentials, EO College, and QGIS.
4. Add planner generation from weekly availability, active tracks, revision needs, rest days, and project sessions.
5. Add notification generation for due reviews, study sessions, exam dates, voucher expiry, and free credential deadlines.
6. Add audit logging calls for activation, primary change, budget changes, attempt submission, evidence upload, and credential recording.
7. Add visual/browser QA evidence for `DESIGN.md`, `design_ref.md`, and `refrence_apps.md`.
8. Expand the first-class Study Planner intelligence: week/month views, availability UI, missed sessions, dynamic rescheduling, revision queue, quests, and recommendation acceptance.
9. Build the Private Tutor workflows: source-grounded explanations, knowledge checks, incorrect-answer review, tutor history, feedback UI, guardrails, and planner integration.
10. Add create/edit workflows and specialised certification workspace tabs for datasets, maps, ontology, and search lab records.

## Current Verdict

The app is roughly at a strong MVP level, not full rule completion.

Estimated compliance:

- Catalogue/content identity: 90%
- Study-material source policy: 85%
- Core PRD workflows: 70%
- Architecture organization: 75%
- Schema completeness: 78%
- GIS/search/knowledge amendment depth: 78%
- Design/reference-app standard: 70% pending browser QA
- Study Planner module: 52%
- Private Tutor module: 15%
- Overall: about 76%

To honestly call it 100%, the schema and UI gaps above need to be implemented, tested, and verified.
