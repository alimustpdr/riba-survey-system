# Modern UI Layer (`/modern`)

This folder contains a **new, additive UI layer** for `riba-survey-system`.

## Goals
- Keep existing RİBA surveys + install logic **immutable**
- Add a modern, mobile-first UI without replacing existing pages
- Reuse existing DB structure (`surveys`, `questions`, `responses`, etc.)

## Entry points
- `modern/index.php` — role-aware landing (redirects to modern dashboards)
- `modern/survey/fill.php` — modern survey filling experience (READS surveys/questions, WRITES to `responses`)

## Data safety
- Existing survey definitions (`form_templates`, `questions`, and `database/forms_data.sql`) are **not modified**
- Existing `install.php` logic is **not modified**
- New context metadata is stored in **additive** tables (created via SQL under `modern/migrations/`)

