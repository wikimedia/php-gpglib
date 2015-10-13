[![Latest Stable Version](https://img.shields.io/packagist/v/wikimedia/gpglib.svg?style=flat)](https://packagist.org/packages/wikimedia/gpglib) [![License](https://img.shields.io/packagist/l/wikimedia/gpglib.svg?style=flat)](https://github.com/wikimedia/php-gpglib/blob/master/LICENSE)
[![Build
Status](https://img.shields.io/travis/wikimedia/php-gpglib.svg?style=flat)](https://travis-ci.org/wikimedia/php-gpglib)

# php-gpglib

A wrapper around GPG for stateless operations. Text and keys are passed as strings and the output
is returned likewise. In the background, GpgLib creates a temporary home directory for GPG and
deletes it when it is finished.

    $factory = new \GpgLib\ShellGpgLibFactory();
    $gpgLib = $factory->create();
    $ciphertext = $gpgLib->encrypt( $cleartext, $key );
