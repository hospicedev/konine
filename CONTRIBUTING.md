# Contributing to Konine

Konine is **actively maintained**. Stability for existing applications comes first, but contributions are welcome across a broad range:
- Security vulnerabilities (report these **privately**: see [SECURITY.md](SECURITY.md), not public issues)
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

The default branch is `master`. All changes branch from `master` and merge back via PR. See [Branches and Releases](#branches-and-releases) for how maintenance branches and backports work.

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
- **All new features and functionality must be built in a backwards-compatible manner** — existing applications must continue to work unchanged, and new behaviour must be kept opt-in where it could affect them

## Bug Fixes

Open an issue first with as much detail as possible. Then submit a PR referencing that issue.

## Feature Requests

Open a feature request describing the problem and the behaviour you would like. Once the approach is agreed on the issue, a PR referencing it can be submitted.

## Branches and Releases

| Branch | Purpose |
|--------|---------|
| `master` | Integration branch for the next release. All PRs target `master` first. |
| `3.4.x` (and later `3.5.x`, ...) | Maintenance branch for patch releases of a released minor version. Only created when needed (see below). |
| `fix/*`, `feature/*`, `docs/*`, ... | Short-lived topic branches created from `master` and merged back via PR. |

### Before a maintenance branch exists

While everything merged since the last release is patch-level (bug fixes, security fixes, docs, CI), patch releases are tagged directly from `master`.

### Creating a maintenance branch

When the first change that belongs in the next **minor** version (a new feature, enhancement or deprecation) is ready to merge, a maintainer first creates the maintenance branch from the latest patch tag of the current minor version:

```bash
git fetch origin --tags
git checkout -b 3.4.x v3.4.1   # latest 3.4 tag
git push -u origin 3.4.x
```

The minor-level PR is then merged to `master`, which from that point on is the next minor version (e.g. 3.5.0).

### Backporting fixes

Once a maintenance branch exists:

1. Bug fixes, security fixes and CI fixes are merged to `master` first, as usual.
2. If the fix should also ship in a patch release, it is backported to the maintenance branch in a separate PR, using `git cherry-pick -x` so the original commit is recorded:

   ```bash
   git checkout -b backport/3.4.x/issue-16 origin/3.4.x
   git cherry-pick -x <commit-sha>
   git push -u origin backport/3.4.x/issue-16
   ```

   Open the PR against `3.4.x`, and reference both the issue and the original PR.
3. New features, enhancements and deprecations are **not** backported.

Security fixes may be prepared privately (for example in a GitHub Security Advisory temporary private fork) so that they're published to `master` and the maintenance branch at the same time. They still go through reviewed PRs, including the mandatory security review. Patch tags for both branches are then cut together.

### Tagging Releases

Tag names are prefixed with `v` (e.g. `v3.4.1`).

- **Patch releases** (`v3.4.2`) are tagged from the maintenance branch (`3.4.x`), or from `master` if no maintenance branch exists yet.
- **Minor releases** (`v3.5.0`) are tagged from `master`.

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
