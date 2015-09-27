This directory contains some test files created with GnuPG 1.4.16:

* public.asc: a public key for heinrichh@duesseldorf.de 
* private.asc: the private key for heinrichh@duesseldorf.de
* cleartext.txt: some text
* ciphertext.asc: cleartext.txt encrypted
  (`gpg --encrypt --armor -r heinrichh@duesseldorf.de test/data/cleartext.txt`)
* signed_text.asc: signed but not encrypted text
  (`gpg --sign --armor test/data/cleartext.txt`)
* signed_ciphertext.asc: signed and encrypted
  (`gpg --encrypt --sign --armor test/data/cleartext.txt`)
* clearsigned_text.txt.asc: cleartext with siganture attached
  (`gpg --clearsign --armor test/data/cleartext.txt`)
* detached_signature.asc: just the signature
  (`gpg --detach-sign --armor test/data/cleartext.txt`)

