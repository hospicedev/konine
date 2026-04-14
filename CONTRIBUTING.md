# Developing locally
Clone the repo and develop on the "develop" branch for new features and version branches (eg. v3.3) for bug fixes.

# Contributing to the project
All features and bugfixes must be fully tested and must have a reference to an issue in [GitHub](https://github.com/hospicedev/konine/issues), **there are absolutely no exceptions**.

It's highly recommended that you write/run unit tests during development as it can help you pick up on issues early on.  See the Unit Testing section below.

## Cloning repo

Clone the project and use the devel branch:
```
git clone git@github.com:hospicedev/konine.git
cd konine
git checkout devel
```

## GitHub CLI setup

This repo is a fork of `koseven/koseven`. The `gh` CLI may default to the upstream repo for commands like `gh pr create`. To ensure it targets `hospicedev/konine`, run:
```
gh repo set-default hospicedev/konine
```

## How to commit
If you have made modifications to the code.

```
git status # to see what's going on
git commit -a -m 'message here, this will commit the changes on the tracked files'
git push origin devel # will "upload" the changes to your repo
```

Tricks
```
git add . # will add all the files, even new ones
git add -u # will add all the tracked files even the deleted ones
git commit -a -m 'working closed etc  #725' # this will commit and mention an issue in the repo
```

## Pull Requests
Create a pull request against the `devel` branch at https://github.com/hospicedev/konine.

Notes:
- Try to submit pull requests against devel branch for easier merging
- Try not to pollute your pull request with unintended changes--keep them simple and small
- Try to share which browsers your code has been tested in before submitting a pull request

## Keep sync with upstream (koseven)
If you need to pull in changes from the original koseven project, add it as a remote:
```
git remote add upstream https://github.com/koseven/koseven.git
```

Then sync:
```
git fetch upstream
git merge upstream/devel
```

Remember to be on your devel branch!

## Bug fixing 
Make a PR with the fix, explain as in detail as possiblle.

## Tagging releases
Tag names should be prefixed with a `v`, this helps to separate tag references from branch references in Git.

For example, if you were creating a tag for the `3.1.0` release the tag name would be `v3.1.0`

# Unit Testing
Koseven currently uses PHPUnit for unit testing. This is installed with composer.

## How to run the tests
 * Install [Composer](http://getcomposer.org)
 * Run `php composer.phar install` from the root of this repository
 * Finally, run `vendor/bin/phpunit --bootstrap=modules/unittest/bootstrap.php modules/unittest/tests.php`

This will run the unit tests for core and all the modules and tell you if anything failed. If you haven't changed anything and you get failures, please create a new issue on [GitHub](https://github.com/hospicedev/konine/issues) and paste the output (including the error) in the issue. Please note that a few tests only pass on linux systems.
