# ADR 0003: Separate Paid And Free Tracks In One Model

## Status

Accepted

## Context

The PRD requires one paid professional certification to be primary and up to two active free credentials, while both tracks share core certification data.

## Decision

Use one `certifications` table with a `track_type` field. Track-specific behaviour is enforced through domain actions and constraints.

## Alternatives

- Separate paid and free tables: rejected because shared behaviour would be duplicated.
- Untyped certifications: rejected because activation and planning rules depend on the track.

## Consequences

Paid/free differences must be explicit in action classes, policies, views, and tests.
