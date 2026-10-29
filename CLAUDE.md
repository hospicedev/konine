# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Konine** is the successor to Koseven, which was the successor to Kohana. It is a PHP 8.4+ HMVC framework.

Koseven was archived on 2026-04-14. Konine is an **actively maintained** continuation. Its first priority is stability for existing applications — PHP compatibility, security patches, and bug fixes always come first. Beyond that baseline, Konine also accepts feature requests and implements requested enhancements (for example response streaming and CSRF protection) when they benefit the community and do not destabilise existing applications. The framework will be maintained until PHP 9 reaches end of life, or until the maintainers are no longer able to continue.

- **GitHub:** https://github.com/hospicedev/konine
- **Website:** https://konine.dev
- **PHP version:** >=8.4 (tested on 8.4 and 8.5)
- **Current version:** 3.4.0 (released 2026-04-15, tagged `v3.4.0`)
- **License:** BSD-3-Clause
- **Lineage:** Kohana 3.3.x -> Koseven 3.3.x -> Konine 3.4.x

## Common Commands

```bash
# Install dependencies
composer install

# Run unit tests
vendor/bin/phpunit

# Run tests with coverage
vendor/bin/phpunit --coverage-html build/logs/

# PHP syntax check on a file
php -l path/to/file.php

# Run a specific test file
vendor/bin/phpunit modules/unittest/tests.php
```

There is no configured linter (no phpcs.xml, eslint, etc.).

## Architecture

### HMVC Pattern

Konine uses the Hierarchical Model-View-Controller pattern. Requests can be either external (from the browser) or internal (sub-requests within the application).

### Directory Layout

- `system/` -- Framework core (Kohana/Koseven classes). This is what Konine primarily maintains.
- `modules/` -- Bundled modules (auth, cache, database, encrypt, image, minion, orm, pagination, unittest, codebench, userguide)
- `application/` -- Application skeleton (bootstrap, config, empty class/view directories)
- `public/` -- Web root (index.php entry point)
- `phpunit.xml` -- Test configuration

### Entry Point and Bootstrap

`public/index.php` -> `application/bootstrap.php`

Bootstrap handles: core class loading, timezone/locale, autoloader registration, Kohana initialization, module loading, cookie config, route definitions. Environment is controlled by the `KOHANA_ENV` server variable.

### Class Naming and Autoloading

Classes use underscores as directory separators for autoloading:
- `Kohana_Core` -> `classes/Kohana/Core.php`
- `Database_MySQLi_Result` -> `classes/Database/MySQLi/Result.php`
- `Model_Auth_User` -> `classes/Model/Auth/User.php`

Transparent extension pattern: `Kohana_ORM` (in system/modules) is extended by an empty `ORM` class (in application), allowing applications to override framework behavior.

### Bundled Modules

| Module | Purpose |
|--------|---------|
| auth | Authentication (File and ORM drivers) |
| cache | Caching backends (file, sqlite, memcache, redis, apcu) |
| codebench | Benchmarking tool |
| database | Database abstraction (MySQLi, PDO) |
| encrypt | Encryption (OpenSSL only — Mcrypt removed) |
| image | Image manipulation (GD, Imagick) |
| minion | CLI task runner |
| orm | Object Relationship Mapping |
| pagination | Pagination helpers |
| unittest | PHPUnit integration |
| userguide | Documentation and API browser |

### Key Framework Classes (system/)

Core classes live in `system/classes/Kohana/`: Core, Request, Response, Route, Controller, Model, View, Session, Cookie, Config, Log, Validation, Form, HTML, URL, HTTP, I18n, Security, Database, ORM, etc.

## Scope of Changes

Konine's first priority is the stability of existing applications. The bulk of work is:
- PHP version compatibility fixes (deprecations, removed features, type changes)
- Security patches
- Bug fixes for existing functionality
- CI/tooling updates
- Documentation updates for the fork

In addition, **requested enhancements and new features are in scope** when they are tracked by a GitHub issue (typically a feature request) and the approach has been agreed there. When implementing a feature:
- Confirm an issue exists describing the requested behaviour, and reference it in the PR.
- **All new features and functionality MUST be built in a backwards-compatible manner.** Existing applications must continue to work unchanged; do not alter or remove existing public behaviour, signatures, or defaults.
- Keep new functionality opt-in where it could change existing behaviour.

Do NOT make speculative feature additions, large refactors for style, or pattern modernisation that is not tied to a tracked request.

## Git Workflow

### Branches

| Branch | Purpose |
|--------|---------|
| `master` | Integration branch for the next release. All PRs target `master` first. Minor releases are tagged from here, and so are patch releases while no maintenance branch exists. |
| `3.4.x` | Maintenance branch for 3.4 patch releases. **Only created when the first minor-level change (feature, enhancement, deprecation) is ready to merge**, branched from the latest 3.4 tag. Patch releases are tagged from here once it exists. |
| `fix/*`, `feature/*`, `docs/*` | Topic branches. Created from `master`, merged back via PR. |
| `backport/<branch>/*` | Cherry-picks of fixes already merged to `master`, opened as PRs against a maintenance branch. |

`devel` is a stale leftover and is not part of the workflow. Do not target it.

### Backports

Once a maintenance branch exists, bug, security and CI fixes land on `master` first. They're then backported with `git cherry-pick -x` in a separate PR against the maintenance branch, referencing the issue and the original PR. Features, enhancements and deprecations are never backported. Full procedure: `CONTRIBUTING.md` > Branches and Releases.

### Default Remote

This repo is a fork of `koseven/koseven`. The `gh` CLI must be configured to target the fork:
```bash
gh repo set-default hospicedev/konine
```

### Upstream Sync

The upstream `koseven/koseven` is available as a remote for reference but is archived and will not receive updates:
```bash
git remote add upstream https://github.com/koseven/koseven.git
git fetch upstream
```

### Security Review (mandatory before every PR)

A security review is **required** before any pull request is opened -- no PR may be created until it has been completed. This applies to all changes, including documentation, since docs can introduce malicious links, leaked secrets, or misleading guidance.

- Run the review against the full branch diff relative to the PR's base branch (`git diff master...HEAD`, or `git diff 3.4.x...HEAD` for backports), not just the latest commit.
- Use the `/security-review` skill (or the **security-reviewer** agent) to scan for: hardcoded secrets or credentials, unvalidated user input, injection (SQL, command, path), XSS / output-escaping gaps, insecure crypto, CSRF exposure, and any leakage of sensitive data in errors or logs.
- Resolve all **CRITICAL** and **HIGH** findings before opening the PR. Document any accepted **MEDIUM**/**LOW** findings in the PR description with justification.
- Record that the review was performed in the PR description (e.g. a "Security review: completed, no findings" note).

### Pull Requests

- **A security review (above) must be completed before the PR is created.**
- PRs target `master`, except backport PRs, which target the maintenance branch (e.g. `3.4.x`)
- All changes require a PR -- no direct pushes to `master` or maintenance branches
- PR descriptions should reference the relevant GitHub issue (e.g. `Refs #2`)
- Keep PRs focused -- one concern per PR

### Versioning

Semantic versioning from 3.4.0 onward:
- **3.4.x** -- PHP 8.4 compatibility release (current)
- **3.5.x** -- PHP 8.5 support, minimum stays PHP 8.4 (in development, #17)
- **Patch versions** (3.4.1, 3.4.2) -- security fixes, bug fixes. Tagged from `3.4.x` if it exists, otherwise from `master`
- **Minor versions** (3.5.0) -- requested enhancements, new features, deprecations, or further PHP compatibility work. Tagged from `master`

### Commit Messages

```
<type>: <description>

<optional body>

Refs #<issue-number>
```

Types: `feat`, `fix`, `refactor`, `docs`, `test`, `chore`, `perf`, `ci`

## Testing

Tests use PHPUnit `^10.5` bootstrapped via `modules/unittest/bootstrap.php`.

- Test files live in `system/tests/` and `modules/*/tests/`
- Test suite is defined in `phpunit.xml` at the project root
- CI runs on push and PR via `.github/workflows/tests.yml`
- CI matrix targets PHP 8.4 and 8.5

### Running Tests Locally

```bash
composer install
vendor/bin/phpunit
```

Some tests require memcached (port 11211) and redis (port 6379) services running locally.

### Test Results (v3.4.0)

```
PHPUnit 10.5.x / PHP 8.4
Tests: 1472, Assertions: 3067, Skipped: 97
Errors: 0, Failures: 0, Warnings: 0
```

## Known Issues and Remaining Work

- Static analysis (PHPStan/Psalm) not yet configured
