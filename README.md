[![Konine Logo](docs/konine.png)](https://konine.dev)

# Konine — PHP 8.4+ HMVC Framework

**Successor to Koseven · Successor to Kohana · Built on the CodeIgniter lineage**

> *Keeping legacy applications alive — and moving them forward.*

---

## Project Status: Actively Maintained

Konine keeps existing Kohana/Koseven applications running on modern PHP. Its first duty is still stability for the applications that depend on it, so that work always comes first:

- PHP 8.4 compatibility fixes
- Security patches
- Bug fixes for existing functionality

Beyond that baseline, **Konine now accepts feature requests and implements requested enhancements** (for example response streaming and CSRF protection) where they benefit the community and do not destabilise existing applications. We are no longer strictly maintenance-only.

**All new features and functionality must be built in a backwards-compatible manner.** Existing applications must continue to work without changes; behaviour that could affect them is kept opt-in.

If you are on Koseven or Kohana, Konine is a safe place to stay — and a place that can grow with you.

To request an enhancement, [open a feature request](https://github.com/hospicedev/konine/issues/new?template=feature_request.md). We discuss scope on the issue before any code is written.

---

## Considering Konine for a New Project?

Konine is a stable HMVC framework in the Kohana tradition — strict OOP, HMVC, and the transparent extension system Kohana developers know well. It is a reasonable choice if that lineage is what you want.

That said, [CodeIgniter](https://codeigniter.com/) shares much of the same spirit — simple, fast, and opinionated — and has a larger, more active community. If you are evaluating frameworks for a brand-new application and want the biggest ecosystem in this tradition, it is well worth a look alongside Konine.

---

## Goals & Motto

**Goal:** Provide a stable, security-maintained, and actively improved path forward for existing Kohana and Koseven applications as PHP evolves — without abandoning the developers who built on this stack.

**Motto:** *Keeping legacy applications alive — and moving them forward.*

We do not aim to compete with modern frameworks. We aim to give developers a working stack they can stay on, improve, and migrate from on their own terms.

---

## A Heartfelt Thank You

This project stands on the shoulders of an enormous amount of volunteer work spanning nearly two decades.

### CodeIgniter

It all started here. CodeIgniter, created by EllisLab, showed the PHP world what a clean, lightweight, fast framework could look like. Its influence on the entire ecosystem — including Kohana — cannot be overstated. Thank you to EllisLab and everyone who contributed to CodeIgniter over the years.

### Kohana

Kohana was born from a fork of CodeIgniter, built by a passionate community who wanted to push things further — stricter OOP, HMVC, a transparent extension system. For years, Kohana was the choice of developers who valued elegance and power over convention. Thank you to every contributor, maintainer, and user who made Kohana what it was.

### Koseven

When Kohana went end-of-life, a small group of developers refused to let it die. Koseven was their answer — a community-maintained fork that kept the framework alive through PHP 7 and beyond, buying time for thousands of legacy applications. It outlived its original purpose many times over. Particular thanks to **Tobias** and everyone else who poured years of unpaid work into keeping Koseven alive. You kept the lights on for a lot of people.

### Konine

Konine is the latest chapter — carrying the framework forward for the applications and developers who still rely on it. As long as those applications need it, we will be here, keeping it stable and improving it where it helps.

**To every developer who ever opened an issue, submitted a pull request, wrote documentation, answered a forum question, or simply used this framework and reported a bug: thank you. This community built something worth carrying forward.**

---

## Requirements

- PHP >= 8.4
- A web server (Apache, Nginx, etc.)
- `ext-mbstring` enabled

---

## Installation

### Fresh clone

```bash
git clone https://github.com/hospicedev/konine.git
cd konine
composer install
```

Configure your web server to point to the `public/` directory.

### Migrating an existing Koseven / Kohana project

If you have an existing project on Koseven or Kohana, you can pull Konine's changes in directly rather than starting from scratch:

```bash
# Add Konine as a remote
git remote add konine https://github.com/hospicedev/konine.git

# Fetch all Konine branches
git fetch konine

# Merge into your current branch
git merge konine/master --allow-unrelated-histories
```

Resolve any merge conflicts — in general, keep your application code and favour Konine's versions of anything under `system/` and the bundled `modules/`, unless you have intentional local overrides in those directories.

To pull future patches once the remote is set up:

```bash
git fetch konine && git merge konine/master
```

See the [upgrading guide](https://konine.dev/documentation/kohana/upgrading-from-kohana) for a full list of breaking changes introduced in 3.4.x.

---

## Quick Start

See the [documentation](https://konine.dev/documentation) for full setup instructions.

---

## Versioning

| Version | PHP Target | Status |
|---------|-----------|--------|
| 3.4.x   | PHP 8.4   | Actively maintained |

Semantic versioning from 3.4.0 onward:
- **Patch versions** (3.4.1, 3.4.2, ...) — security fixes and bug fixes
- **Minor versions** (3.5.0, ...) — requested enhancements, new features, and PHP compatibility work

---

## Contributing

Contributions are welcome for:
- Security vulnerabilities
- PHP compatibility bugs
- Regressions from PHP version changes
- Bug fixes for existing functionality
- Enhancements and new features (please open a feature request first so scope can be agreed; all new functionality must be backwards compatible)

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

All contributions must reference a [GitHub issue](https://github.com/hospicedev/konine/issues).

---

## License

Konine is released under the [BSD-3-Clause license](LICENSE.md). You can use it for open source, commercial, or personal projects.

---

## Links

- **Website:** https://konine.dev
- **GitHub:** https://github.com/hospicedev/konine
- **Issues:** https://github.com/hospicedev/konine/issues
- **Feature requests:** https://github.com/hospicedev/konine/issues/new?template=feature_request.md
- **CodeIgniter (a great option in the same lineage):** https://codeigniter.com/
