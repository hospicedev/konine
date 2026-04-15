# Konine Roadmap

Konine is in **maintenance mode only**. No new features will be added.

## Scope of Work

All ongoing work is limited to:
- PHP version compatibility fixes (deprecations, removed functions, type changes)
- Security patches
- Bug fixes for existing functionality
- CI/tooling updates
- Documentation updates

## Version Plan

### 3.4.x — PHP 8.4 Compatibility (Current)

Branched from Koseven 3.3.10. The primary goal is to ensure the framework runs cleanly on PHP 8.1–8.4.

- Fix deprecation warnings and removed functions
- Update CI matrix to PHP 8.1–8.4
- Upgrade dev dependencies (PHPUnit, etc.) for PHP 8.x compatibility
- Security patches as needed

**Patch versions** (3.4.1, 3.4.2, ...) are released for security fixes and bug fixes only.

### Future Minor Versions

A new minor version (e.g. 3.5.0) will only be created if a future PHP version introduces breaking changes that require further compatibility work. There is no scheduled release.

### End of Life

Konine will be maintained until:
- PHP 9 reaches end of life, **or**
- The maintainers are no longer able to continue

There will be no major feature releases or architectural changes.

## Tracked Work

For open issues and planned fixes, see:
https://github.com/hospicedev/konine/issues

The main tracking issue for the PHP 8.x compatibility work is:
https://github.com/hospicedev/konine/issues/2
