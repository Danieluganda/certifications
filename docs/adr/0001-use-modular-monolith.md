# ADR 0001: Use A Modular Monolith

## Status

Accepted

## Context

CertPath 123 is a private single-user MVP that must grow into a larger certification operating system without adding unnecessary operational complexity.

## Decision

Use a Laravel modular monolith. Organise business logic by domain under `app/Domains`, with controllers as the interface layer and action classes for workflows that enforce business rules.

## Alternatives

- Microservices: rejected because the MVP does not need distributed deployment complexity.
- Static JSON app: rejected because the product requires relational history, ownership, attempts, progress, and reporting.

## Consequences

The application can use relational transactions and simple deployment while keeping a clean path to future extraction if evidence requires it.
