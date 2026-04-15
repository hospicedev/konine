# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Konine** is the successor to Koseven, which was the successor to Kohana. It is a PHP 8.4+ HMVC framework.

Koseven was archived on 2026-04-14. Konine exists as **end-of-life support** — maintenance and security fixes only. No new features. The goal is to keep the framework running until PHP 9 reaches end of life, or until the maintainers are no longer able to continue.

- **GitHub:** https://github.com/hospicedev/konine
- **Website:** https://konine.dev
- **PHP version:** >=8.4
- **Current version:** 3.4.0 (in progress, branched from koseven 3.3.10)
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
| encrypt | Encryption (OpenSSL, Mcrypt) |
| image | Image manipulation (GD, Imagick) |
| minion | CLI task runner |
| orm | Object Relationship Mapping |
| pagination | Pagination helpers |
| unittest | PHPUnit integration |
| userguide | Documentation and API browser |

### Key Framework Classes (system/)

Core classes live in `system/classes/Kohana/`: Core, Request, Response, Route, Controller, Model, View, Session, Cookie, Config, Log, Validation, Form, HTML, URL, HTTP, I18n, Security, Database, ORM, etc.

## Scope of Changes

Konine is maintenance-only. Changes must be limited to:
- PHP version compatibility fixes (deprecations, removed features, type changes)
- Security patches
- Bug fixes for existing functionality
- CI/tooling updates
- Documentation updates for the fork

Do NOT add new features, refactor for style, or modernize patterns beyond what is required for PHP compatibility.

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
- **Minor versions** (3.5.0) -- if future PHP versions require further compatibility work

### Commit Messages

```
<type>: <description>

<optional body>

Refs #<issue-number>
```

Types: `feat`, `fix`, `refactor`, `docs`, `test`, `chore`, `perf`, `ci`

## Testing

Tests use PHPUnit (currently v7.5.x) bootstrapped via `modules/unittest/bootstrap.php`.

- Test files live in `system/tests/` and `modules/*/tests/`
- Test suite is defined in `phpunit.xml` at the project root
- CI runs on push and PR via `.github/workflows/tests.yml`
- CI matrix currently targets PHP 7.1-7.4 (needs updating to 8.1-8.4 as part of #2)

### Running Tests Locally

```bash
composer install
vendor/bin/phpunit
```

Some tests require memcached (port 11211) and redis (port 6379) services running locally.

## Known Issues and Remaining Work

Tracked in https://github.com/hospicedev/konine/issues/2:

- CI workflow matrix needs updating from PHP 7.x to 8.1-8.4
- PHPUnit and dev dependencies need upgrading for PHP 8.x compatibility
- `composer.json` package name still says `koseven/koseven`
- Static analysis (PHPStan/Psalm) not yet configured
- `ROADMAP.md` still contains upstream koseven versioning plan
