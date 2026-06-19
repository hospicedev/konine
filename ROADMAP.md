# Konine Roadmap

Konine is **actively maintained**. Stability for existing applications comes first, and the project also accepts feature requests and implements requested enhancements.

## Scope of Work

Ongoing work covers:
- PHP version compatibility fixes (deprecations, removed functions, type changes)
- Security patches
- Bug fixes for existing functionality
- CI/tooling updates
- Documentation updates
- Requested enhancements and new features, tracked via GitHub issues

Stability is the priority: enhancements are added in a way that aims to be backwards compatible and to keep new behaviour opt-in where it could affect existing applications.

## Version Plan

### 3.4.x — PHP 8.4 Compatibility (Current)

Branched from Koseven 3.3.10. The primary goal is to ensure the framework runs cleanly on PHP 8.1–8.4.

- Fix deprecation warnings and removed functions
- Update CI matrix to PHP 8.1–8.4
- Upgrade dev dependencies (PHPUnit, etc.) for PHP 8.x compatibility
- Security patches as needed

**Patch versions** (3.4.1, 3.4.2, ...) are released for security fixes and bug fixes.

### Future Minor Versions

A new minor version (e.g. 3.5.0) is cut when requested enhancements or new features are ready to ship, or when a future PHP version introduces breaking changes that require further compatibility work.

## Planned & Requested Enhancements

These have been requested and are candidates for an upcoming minor release. Track and discuss them on the issue tracker:

- **Response streaming** — stream `Response` output rather than buffering it in full.
- **CSRF protection** — first-class cross-site request forgery protection helpers.

Have a request? [Open a feature request](https://github.com/hospicedev/konine/issues/new?template=feature_request.md) and we will discuss scope before any code is written.

## End of Life

Konine will be maintained until:
- PHP 9 reaches end of life, **or**
- The maintainers are no longer able to continue

## Tracked Work

For open issues and planned work, see:
https://github.com/hospicedev/konine/issues

The main tracking issue for the PHP 8.x compatibility work is:
https://github.com/hospicedev/konine/issues/2
