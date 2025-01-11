#!/bin/bash

mkdir -p config/jwt
export JWT_PASSPHRASE=0136ba3952398c43ddd5d0c75cc6f3fb0ab3954d291fde1dc2511a217b49f9b8

openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096 -pass env:JWT_PASSPHRASE
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout -passin env:JWT_PASSPHRASE
