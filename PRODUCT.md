# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

- Students organize personal learning materials and join classrooms.
- Teachers share materials, manage assignments, and assess submissions.
- Administrators manage system-wide accounts and support the learning platform.

## Product Purpose

TataKelas combines a personal learning workspace with classroom tools for sharing learning materials, assignments, and assessment.

## Positioning

Personal resource organization and classroom learning workflows are available in one web application, with private workspaces kept separate from classroom content.

## Operating Context

The application is used by students, teachers, and administrators through a responsive web interface. Its product requirements and terminology are documented in `docs/PRD.md`.

## Capabilities and Constraints

- Public registration creates student accounts; teacher and admin roles are assigned administratively.
- Students and teachers can use private personal workspaces; administrators do not access personal folders, resources, notes, or photos. Classroom content remains available to eligible classroom members, the owning teacher, and co-teachers.
- The application uses Laravel, Blade, Tailwind CSS, and MySQL/MariaDB or SQLite.
- Email-based password reset, email verification, and two-factor authentication are not implemented.
- System-wide account management and an admin audit log are implemented: role changes, suspension, password resets, bulk actions, and CSV exports are available in the admin panel with every action recorded.
- Suspending an account blocks further logins and ends the account's active session immediately.
- Documents uploaded as learning materials can be previewed in the browser without downloading.

## Evidence on Hand

- Product requirements: `docs/PRD.md`.
- Existing interface and workflows: Laravel routes, controllers, models, and Blade views.

## Product Principles

- Protect private learning content and enforce least privilege.
- Keep personal workspaces distinct from shared classroom content.
- Give each role only the access needed for its work.
- Use clear, responsive workflows for account and classroom tasks.
