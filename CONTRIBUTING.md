# Contributing to Konine

Konine is in **maintenance mode** — contributions are accepted for:
- Security vulnerabilities
- PHP version compatibility bugs (deprecated or removed functionality)
- Regressions in existing functionality

**New features and refactors are out of scope.** Please open an issue before submitting a pull request so the scope can be confirmed.

---

## Cloning the Repo

```bash
git clone git@github.com:hospicedev/konine.git
cd konine
```

The default branch is `master`. All fixes branch from `master` and merge back via PR.

## GitHub CLI Setup

This repo is a fork of `koseven/koseven`. The `gh` CLI may default to the upstream repo. To ensure it targets `hospicedev/konine`:

```bash
gh repo set-default hospicedev/konine
```

## Making Changes

```bash
git checkout -b fix/description-of-fix
# ... make your changes ...
git status
git add path/to/changed/file.php
git commit -m 'fix: description of fix

Refs #<issue-number>'
git push origin fix/description-of-fix
```

Then open a pull request against `master` at https://github.com/hospicedev/konine.

## Pull Request Rules

- All PRs must reference a GitHub issue (e.g. `Refs #12`)
- Keep PRs focused — one concern per PR
- All changes must be tested via the unit test suite
- Fix the issue, not surrounding style

## Bug Fixes

Open an issue first with as much detail as possible. Then submit a PR referencing that issue.

## Tagging Releases

Tag names are prefixed with `v` (e.g. `v3.4.1`).

---

## Unit Testing

Konine uses PHPUnit for unit testing, installed via Composer.

### Running Tests

```bash
# Install dependencies
composer install

# Run the full test suite
vendor/bin/phpunit

# Run tests with coverage report
vendor/bin/phpunit --coverage-html build/logs/

# Run a specific module's tests
vendor/bin/phpunit --bootstrap=modules/unittest/bootstrap.php modules/unittest/tests.php
```

If you see failures on an unmodified checkout, please open a [GitHub issue](https://github.com/hospicedev/konine/issues) with the full output.

Note: a few tests only pass on Linux systems.

---

## Upstream Reference

The upstream `koseven/koseven` repository is archived. It is available as a reference remote but will not receive updates:

```bash
git remote add upstream https://github.com/koseven/koseven.git
git fetch upstream
```

---

> **Not sure if your change is in scope?** Open an issue and ask. We would rather discuss it first than reject a PR after effort has been spent.
