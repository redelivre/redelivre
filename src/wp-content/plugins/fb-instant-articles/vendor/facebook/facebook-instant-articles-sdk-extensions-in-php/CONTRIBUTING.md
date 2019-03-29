# Contributing to Facebook Instant Articles SDK Extensions in PHP
We want to make contributing to this project as easy and transparent as possible.

We accept contributions via pull requests on [GitHub](https://github.com/facebook/facebook-instant-articles-sdk-extensions-in-php).

## Pull Requests
- **Sign the CLA** - In order to accept your pull request, we need you to submit a [Contributor License Agreement](https://code.facebook.com/cla). You only need to do this once to work on any of Facebook's open source projects.

- **[PSR-2 Coding Standard](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)** - The easiest way to apply the conventions is to run [PHP Code Sniffer](https://github.com/squizlabs/PHP_CodeSniffer) as you code.

- **Add tests** - If you've added code that can be tested, add tests.

- **Document any change in behaviour** - Make sure the README file is updated in your PR. Include any notes for documentation items which needs to be updated on the main [docs on Facebook's Developer site](https://developers.facebook.com/docs/instant-articles/sdk/).

- **Consider our release cycle** - We try to follow [SemVer](http://semver.org/). Randomly breaking public APIs is not an option.

- **Create topic branches** - Don't ask us to pull from your master branch.

- **One pull request per feature** - If you want to do more than one thing, send multiple pull requests.

- **Send coherent history** - Make sure each individual commit in your pull request is meaningful. If you had to make multiple intermediate commits while developing, please squash them before submitting.

- **Ensure tests pass!** - Please [run the tests](https://github.com/facebook/facebook-instant-articles-sdk-extensions-php#testing-and-developing) before submitting your pull request, and make sure they pass. We won't accept a patch until all tests pass.

- **Ensure no coding standards violations** - Please run [PHP Code Sniffer](https://github.com/squizlabs/PHP_CodeSniffer) using the PSR-2 standard before submitting your pull request. A violation will cause the build to fail, so please make sure there are no violations. We can't accept a patch if the build fails.


## Issues
We use GitHub issues to track public bugs. Please ensure your description is clear and has sufficient instructions to be able to reproduce the issue.


## Running Tests

``` bash
$ ./vendor/bin/phpunit
```

When doing a pull request, consider if this diff has a testcase that was covered in a wrong way or if it needs a new test case.


## Running PHP Code Sniffer

Run Code Sniffer against the `src/` and `tests/` directories.

``` bash
$ vendor/bin/phpcs --standard=phpcs.xml -p
```

Give a try for the autofixer for code style

``` bash
$ vendor/bin/phpcbf --standard-phpcs.xml -p
```
**Happy coding**!
