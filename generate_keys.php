<?php

$passphrase = '';

$config = [
    "private_key_bits" => 4096,
    "private_key_type" => OPENSSL_KEYTYPE_RSA,
];

$res = openssl_pkey_new($config);

if (!$res) {
    echo "Erreur OpenSSL\n";
    while ($msg = openssl_error_string()) {
        echo $msg . "\n";
    }
    exit(1);
}

openssl_pkey_export($res, $privateKey, $passphrase);
$publicKey = openssl_pkey_get_details($res)['key'];

if (!is_dir(__DIR__ . '/config/jwt')) {
    mkdir(__DIR__ . '/config/jwt', 0777, true);
}

file_put_contents(__DIR__ . '/config/jwt/private.pem', $privateKey);
file_put_contents(__DIR__ . '/config/jwt/public.pem', $publicKey);

echo "✅ Keys generated successfully\n";