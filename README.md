# Fiddler

Complement to Composer to build monolithic repositories.

Composer currently focuses alot on reusability of third party libraries,
but is painful when considering company internal code-bases.
Projects using 10th of Git repositories because
of Composer dependencies are massively hurting developer productivity
because of Pull-Requests, composer.lock issues, Cross-Repository changes...

Fiddle draws inspiration from Google [Blaze/Bazel](http://bazel.io/) and
Facebook [Buck](http://facebook.github.io/buck/) implementing a single
monolithic repository for whole projects/company. Its the missing piece for
the monolithic repository workflow using PHP and Composer.

More details about reasoning on Gregory Szorc's blog:

- [On Monlithic Repositories](http://gregoryszorc.com/blog/2014/09/09/on-monolithic-repositories/)
- [Notes from Facebooks Developer Infrastructure at Scale F8 talk](http://gregoryszorc.com/blog/2015/03/28/notes-from-facebook's-developer-infrastructure-at-scale-f8-talk/)

## Benefits

1. You can just `require "vendor/autoload.php;` in every component as if using composer.
   But prevents grabbing any class by just autoloading by generating custom autoloaders.
   Explicit dependencies necessary in `fiddle.json`.
2. No one-to-one git repository == composer package requirement anymore,
   increasing productivity using Google/Facebook development model.
3. No composer.lock/Pulol Request issues that block your productivity with multi repository projects.
4. If you commit `vendor/` no dependency on Github and Packagist anymore for fast builds.
5. Much higher Reproducibility of builds
6. Detect components that changed since a given commit and their dependants to allow efficient
   build process on CI systems (only test components that changed, only regenerate assets for components that changed, ...)

## Implementation

This project assumes you have a single monolithic repository with
several components as well as third party dependencies using Composer.

You would create a `composer.json` file in the root of your project and use
this single source of vendor libraries accross all your own libraries.

This sounds counter-intuitive to the Composer approach at first, but
it simplifies dependency management for a big project massively. Usually
if you are using a composer.json per component, you have mass update sprees
where you upate some basic library like "symfony/dependency-injection" in
10-20 components or worse, have massively out of date components and
many different versions everywhere.

Then every of your own components contains a `fiddle.json` using almost
the same syntax as Composer:

    {
        "name": "my-component",
        "deps": [
            "components/Foo"
            "vendor/symfony/symfony"
        ],
        "autoload": {
            "psr-0": {"Foo\\": "src/"}
        }
    }

you can then run `fiddle build` in the root directory next to composer.json
and it will generate a custom autoloader for each component by running
`composer dump-autoload` as if a composer.json were present.

Fiddle will resolve all dependencies (without version constraints, because the
code is always present in the correct versions in a monolithic repository).

Package names in `deps` are the relative directory names from the project root.
From the vendor directory `composer.json` are loaded to find out the dependency graph
and the autoload configuration.
