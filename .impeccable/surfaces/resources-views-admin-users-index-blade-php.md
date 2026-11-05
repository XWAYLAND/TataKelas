---
version: 1
slug: "resources-views-admin-users-index-blade-php"
primary_target: "resources/views/admin/users/index.blade.php"
related_targets: ["resources/views/admin/audit-logs/index.blade.php"]
---

# Admin account directory

## Scope and visitor mode

Admin-only directory for system-wide account management and audit review. Operate.

## Direction contract

THESIS: Make account work direct; refuse the analytics-first admin template.
OWN-WORLD: Inherit TataKelas's existing sidebar, compact typography, neutral surfaces, and primary action color.
STORY: Admin finds an account, changes access or creates a one-time password, then verifies the recorded action.
FIRST VIEWPORT: Page title and audit link lead; compact role totals sit above search/filter controls and a full-width account directory.
FORM: Directory-first, selected structure 4; seed key 3f7161bf. Audit history has its own searchable page.
FINISH: unreviewed and undocumented is unfinished; this build ends with the finish review, the verdict, DESIGN.md, and every shipping raster carrying its provenance

## Constraints and open decisions

Deleting accounts with folders, classrooms, memberships, or submissions is blocked. Prevent self-role changes, self-deletion, and removal of the last admin. Reset passwords are generated and shown once; email delivery is unavailable.
