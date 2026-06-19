# Contributing to Konine

Konine is **actively maintained**. Stability for existing applications comes first, but contributions are welcome across a broad range:
- Security vulnerabilities
- PHP version compatibility bugs (deprecated or removed functionality)
- Regressions in existing functionality
- Bug fixes for existing functionality
- Enhancements and new features

**Please open an issue before submitting a pull request** so the scope can be confirmed. For new features, [open a feature request](https://github.com/hospicedev/konine/issues/new?template=feature_request.md) and we will agree the approach on the issue before any code is written. We would rather discuss a change first than reject a PR after effort has been spent.

---

## Cloning the Repo

```bash
git clone git@github.com:hospicedev/konine.git
cd konine
```

The default branch is `master`. All changes branch from `master` and merge back via PR.

## GitHub CLI Setup

This repo is a fork of `koseven/koseven`. The `gh` CLI may default to the upstream repo. To ensure it targets `hospicedev/konine`:

```bash
gh repo set-default hospicedev/konine
```

## Making Changes

```bash
git checkout -b fix/description-of-change
# ... make your changes ...
git status
git add path/to/changed/file.php
git commit -m 'fix: description of change

Refs #<issue-number>'
git push origin fix/description-of-change
```

Then open a pull request against `master` at https://github.com/hospicedev/konine.

## Pull Request Rules

- All PRs must reference a GitHub issue (e.g. `Refs #12`)
- Keep PRs focused — one concern per PR
- All changes must be covered by the unit test suite where practical
- New features should be backwards compatible and keep new behaviour opt-in where it could affect existing applications

## Bug Fixes

Open an issue first with as much detail as possible. Then submit a PR referencing that issue.

## Feature Requests

Open a feature request describing the problem and the behaviour you would like. Once the approach is agreed on the issue, a PR referencing it can be submitted.

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
