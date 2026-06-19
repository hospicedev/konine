# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Konine** is the successor to Koseven, which was the successor to Kohana. It is a PHP 8.4+ HMVC framework.

Koseven was archived on 2026-04-14. Konine is an **actively maintained** continuation. Its first priority is stability for existing applications — PHP compatibility, security patches, and bug fixes always come first. Beyond that baseline, Konine also accepts feature requests and implements requested enhancements (for example response streaming and CSRF protection) when they benefit the community and do not destabilise existing applications. The framework will be maintained until PHP 9 reaches end of life, or until the maintainers are no longer able to continue.

- **GitHub:** https://github.com/hospicedev/konine
- **Website:** https://konine.dev
- **PHP version:** >=8.4
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
- Prefer changes that are backwards compatible and do not destabilise existing applications.
- Keep new functionality opt-in where it could change existing behaviour.

Do NOT make speculative feature additions, large refactors for style, or pattern modernisation that is not tied to a tracked request.

## Git Workflow

### Branches

| Branch | Purpose |
|--------|---------|
| `master` | Stable release branch. Tagged releases are cut from here. |
| `feature/*` | Feature/fix branches. Created from `master`, merged back via PR. |

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

### Pull Requests

- PRs target `master`
- All changes require a PR -- no direct pushes to `master`
- PR descriptions should reference the relevant GitHub issue (e.g. `Refs #2`)
- Keep PRs focused -- one concern per PR

### Versioning

Semantic versioning from 3.4.0 onward:
- **3.4.x** -- PHP 8.4 compatibility release (current)
- **Patch versions** (3.4.1, 3.4.2) -- security fixes, bug fixes
- **Minor versions** (3.5.0) -- requested enhancements, new features, or further PHP compatibility work

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
- CI matrix targets PHP 8.4

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
