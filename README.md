# php-gpglib

A wrapper around GPG for stateless operations. Text and keys are passed as strings and the output
is returned likewise. In the background, GpgLib creates a temporary home directory for GPG and
deletes it when it is finished.

    $factory = new \GpgLib\ShellGpgLibFactory();
    $gpgLib = $factory->create();
    $ciphertext = $gpgLib->encrypt( $cleartext, $key );
