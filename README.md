[![Latest Stable Version](https://img.shields.io/packagist/v/wikimedia/gpglib.svg?style=flat)](https://packagist.org/packages/wikimedia/gpglib) [![License](https://img.shields.io/packagist/l/wikimedia/gpglib.svg?style=flat)](https://github.com/wikimedia/php-gpglib/blob/master/LICENSE)
[![Build
Status](https://img.shields.io/travis/wikimedia/php-gpglib.svg?style=flat)](https://travis-ci.org/wikimedia/php-gpglib)

php-gpglib
==========

A wrapper around GPG for stateless operations. Text and keys are passed as strings and the output
is returned likewise. In the background, GpgLib creates a temporary home directory for GPG and
deletes it when it is finished.

Installation
------------
```
$ composer require wikimedia/gpglib
```


Usage
-----
```
$factory = new \GpgLib\ShellGpgLibFactory();
$gpgLib = $factory->create();
$ciphertext = $gpgLib->encrypt( $cleartext, $key );
```


Running tests
-------------

```
$ composer install
$ composer test
```


Contributing
------------

Bug, feature requests and other issues should be reported to the [GitHub
project]. We accept code and documentation contributions via Pull Requests on
GitHub as well.

- [MediaWiki coding conventions][] are used by the project. The included test
  configuration uses [PHP Code Sniffer][] to validate the conventions.
- Tests are encouraged. Our test coverage isn't perfect but we'd like it to
  get better rather than worse, so please try to include tests with your
  changes.
- Keep the documentation up to date. Make sure `README.md` and other
  relevant documentation is kept up to date with your changes.
- One pull request per feature. Try to keep your changes focused on solving
  a single problem. This will make it easier for us to review the change and
  easier for you to make sure you have updated the necessary tests and
  documentation.


License
-------

php-gpglib is licensed under the MIT license. See the `LICENSE` file for more
details.


---
[GitHub project]: https://github.com/wikimedia/php-gpglib
[MediaWiki coding conventions]: https://www.mediawiki.org/wiki/Manual:Coding_conventions/PHP
[PHP Code Sniffer]: http://pear.php.net/package/PHP_CodeSniffer
