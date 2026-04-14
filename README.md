[![Konine Logo](docs/konine.png)](https://konine.dev)

# Konine — PHP 8.4+ HMVC Framework

**Successor to Koseven · Successor to Kohana · Built on the CodeIgniter lineage**

> *Keeping legacy applications alive until their time is done.*

---

## Project Status: Maintenance Mode

Konine exists as **end-of-life support** for existing Kohana/Koseven applications. It provides:

- PHP 8.x / 8.4 compatibility fixes
- Security patches
- Bug fixes for existing functionality

**No new features will be added.** The goal is to keep the framework running until PHP 9 reaches end of life, or until the maintainers are no longer able to continue.

If you are on Koseven or Kohana and need to stay on this stack while you plan a migration, Konine is for you.

---

## Looking to Start a New Project?

**Please look at [CodeIgniter](https://codeigniter.com/) first.**

CodeIgniter is a lightweight PHP HMVC framework that shares much of the same spirit as Kohana — simple, fast, and opinionated. It is actively maintained, regularly updated, and has a healthy community. If you are evaluating frameworks for a new application and want something in the Kohana tradition, CodeIgniter is far better suited to that purpose than starting fresh on Konine.

Konine is maintenance-only. It is not the right foundation for a new project.

---

## Goals & Motto

**Goal:** Provide a stable, security-maintained path forward for existing Kohana and Koseven applications as PHP evolves — without abandoning the developers who built on this stack.

**Motto:** *Keeping legacy applications alive until their time is done.*

We do not aim to compete with modern frameworks. We aim to give developers breathing room to migrate on their own terms, without being forced off a working stack before they are ready.

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

Konine is the latest chapter — not a rebirth, but an honest acknowledgement that the work is not quite finished. As long as existing applications need it, we will be here.

**To every developer who ever opened an issue, submitted a pull request, wrote documentation, answered a forum question, or simply used this framework and reported a bug: thank you. This community built something worth carrying forward.**

---

## Requirements

- PHP >= 8.1 (PHP 8.4 is the primary target)
- A web server (Apache, Nginx, etc.)

---

## Installation

```bash
git clone https://github.com/hospicedev/konine.git
cd konine
composer install
```

Configure your web server to point to the `public/` directory.

---

## Quick Start

See the [documentation](https://konine.dev/documentation) for full setup instructions.

---

## Versioning

| Version | PHP Target | Status |
|---------|-----------|--------|
| 3.4.x   | PHP 8.4   | Active (maintenance only) |

Semantic versioning from 3.4.0 onward:
- **Patch versions** (3.4.1, 3.4.2, ...) — security fixes and bug fixes only
- **Minor versions** (3.5.0) — only if a future PHP version requires further compatibility work

---

## Contributing

Contributions are welcome for:
- Security vulnerabilities
- PHP compatibility bugs
- Regressions from PHP version changes

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
- **CodeIgniter (recommended for new projects):** https://codeigniter.com/
