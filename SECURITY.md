# Security Policy

Security fixes are Konine's first priority, alongside PHP compatibility and stability for existing applications.

## Supported Versions

| Version | PHP | Security fixes |
|---------|-----|----------------|
| 3.4.x   | 8.4 | :white_check_mark: Yes |
| < 3.4 (Kohana, Koseven) | — | :x: No (upstream projects are end-of-life) |

Security fixes are released as patch versions (e.g. 3.4.1) of the supported minor versions.

When a new minor version is released (e.g. 3.5.0):

- the newest minor version receives all security and bug fixes;
- each older minor version keeps receiving **security fixes** from its maintenance branch until upstream PHP security support ends for the PHP version it targets (for 3.4.x on PHP 8.4: 31 December 2028), even after newer minor versions are released.

See [Branches and Releases](CONTRIBUTING.md#branches-and-releases) for how maintenance branches work.

## Reporting a Vulnerability

**Do not report security vulnerabilities through public GitHub issues, pull requests or discussions.**

Report them privately through GitHub's private vulnerability reporting:

**[Report a vulnerability](https://github.com/hospicedev/konine/security/advisories/new)** (or go to the repository's **Security** tab and choose **Report a vulnerability**). You need to be signed in to a GitHub account to submit a report.

Please include as much of the following as you can:

- the affected component (e.g. a file such as `system/classes/Kohana/Request.php`, or a module such as `orm`);
- the Konine version and PHP version;
- the type of issue (e.g. SQL injection, cross-site scripting, authentication bypass);
- the conditions needed to exploit it (e.g. configuration, required privileges);
- steps to reproduce, or a minimal proof of concept;
- the impact, as you understand it;
- a suggested fix, if you have one.

Only report vulnerabilities in Konine itself. Issues in your application code, third-party libraries or your server configuration should go to their own maintainers.

## What Happens Next

1. We aim to acknowledge your report within 7 days.
2. We confirm the issue and assess its severity, and may ask you for more information.
3. The fix is prepared privately (for example in the advisory's temporary private fork). It goes through the same code review and security review as any other change, and the full test suite is run before release.
4. Fixed patch versions are released for all supported versions at the same time, and the advisory is published, with a CVE where appropriate.
5. Unless you prefer to stay anonymous, you are credited in the advisory.

Please give us a reasonable amount of time to release a fix before disclosing the issue publicly. We will keep you informed of progress and agree a disclosure date with you.

## Security Updates

Security releases are announced through [GitHub Security Advisories](https://github.com/hospicedev/konine/security/advisories) and the [releases page](https://github.com/hospicedev/konine/releases). Every security fix is published as a GitHub release. To be notified, watch the repository with **Custom → Releases**.
