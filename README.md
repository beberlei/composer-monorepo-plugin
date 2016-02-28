# Fiddler - The Composer Monorepo Plugin

    Note: this project is still experimental. Please provide feedback!

Fiddler adds support for Monorepos when using Composer package manager. It
introduces a maintainable approach to managing dependencies for multiple
packages in a single repository, without losing the benefits of having explicit
dependencies for each separate package.

Repositories managed with Fiddler contain two kinds of packages:

1. Composer packages defined by a single global `composer.json` with all external dependencies at the root of the repository.
2. Many Fiddler packages in sub-folders of the project, each with its own
   `fiddler.json`, a simplified `composer.json` file.

Dependencies in Fiddler can be either a third party Composer package that
is listed in the ``composer.json`` or a Fiddler package contained in the project.

Fiddler's build step generates autoloaders with `vendor/autoload.php` files for
each package with access to the explicitly specified dependencies only.

The following steps are performed by fiddler when building the autoloads:

1. It detects `fiddler.json` files in subdirectories excluding `vendor/` and marks
   them as roots of packages.
2. It then fetches all composer packages from the locally installed packages.
3. Finally for each package with `fiddler.json` it generates a
   `vendor/autoload.php` file using all the dependencies defined in that
   package from either other Fiddler or Composer packages.

Fiddler draws inspiration from Google [Blaze/Bazel](http://bazel.io/) and
Facebook [Buck](http://facebook.github.io/buck/) implementing a single
monolithic repository for whole projects/company. It's the missing piece for
the monolithic repository workflow using PHP and Composer.

More details about reasoning on Gregory Szorc's blog:

- [On Monlithic Repositories](http://gregoryszorc.com/blog/2014/09/09/on-monolithic-repositories/)
- [Notes from Facebooks Developer Infrastructure at Scale F8 talk](http://gregoryszorc.com/blog/2015/03/28/notes-from-facebook's-developer-infrastructure-at-scale-f8-talk/)

## Installation

### Composer Plugin (recommended)

Add the composer monorepo plugin to your root composer.json with:

    $ composer require beberlei/composer-monorepo-plugin

Now whenever Composer generates autoload files (during install, update or
dump-autoload) it will find all sub-directories with `fiddler.json` files and
generate sub-package autoloaders for them.

### Standalone (not recommended)

Grab the latest PHAR from the [Releases
page](https://github.com/beberlei/fiddler/releases) directly or download the
tarball containing it.

Fiddler offers a very simple command line interface for now:

    $ php fiddler.phar build

Run this in project root, next to the `composer.json` file.

## Usage

This project assumes you have a single monolithic repository containing
multiple packages as well as third-party dependencies using Composer.

You would create a `composer.json` file in the root of your project and use
this single source of vendor libraries across all of your own packages.

This sounds counter-intuitive to the Composer approach at first, but
it simplifies dependency management for a big project massively. Usually
if you are using a composer.json per package, you have mass update sprees
where you upate some basic library like "symfony/dependency-injection" in
10-20 packages or worse, have massively out of date packages and
many different versions everywhere.

Then, each of your own package contains a `fiddler.json` using almost
the same syntax as Composer:

    {
        "deps": [
            "components/Foo",
            "vendor/symfony/symfony"
        ],
        "autoload": {
            "psr-0": {"Foo\\": "src/"}
        }
    }

You can then run `fiddler build` in the root directory next to composer.json and
it will detect all packages, generate a custom autoloader for each one by
simulating `composer dump-autoload` as if a composer.json were present.

Fiddler will resolve all dependencies (without version constraints, because it
is assumed the code is present in the correct versions in a monolithic
repository).

Package names in `deps` are the relative directory names from the project root,
*not* Composer package names.

## Configuration (fiddler.json)

For each package in your monolithic repository you have to add `fiddler.json`
that borrows from `composer.json` format. The following keys are usable:

- `autoload` - configures the autoload settings for the current package classes and files.
- `autoload-dev` - configures dev autoload requirements. Currently *always* evalauted.
- `deps` - configures the required dependencies in an array (no key-value pairs with versions)
  using the relative path to the project root directory as a package name.
- `deps-dev` - configures the required dev dependencies

## Benefits

1. You can just `require "vendor/autoload.php;` in every package as if you were using Composer.
   Only autoloads from the `fiddler.json` are included, which means all dependencies must be explicitly
   specified.
2. No one-to-one git repository == composer package requirement anymore,
   increasing productivity using Google/Facebook development model.
3. No composer.lock/Pull Request issues that block your productivity with multi-repository projects.
4. If you commit `vendor/` no dependency on Github and Packagist anymore for fast builds.
5. Much higher Reproducibility of builds.
6. Not yet: Detect packages that changed since a given commit and their dependents to allow efficient
   build process on CI systems (only test packages that changed, only regenerate assets for packages that changed, ...)
