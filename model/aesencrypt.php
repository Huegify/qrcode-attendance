<?php
//FOR PHP 7.3.7 :: USES open_ssl
class AESEncryption
{
    public function encrypt($data, $secret="mykey") {
        $method = 'AES-256-CBC';
        $secret .= "?coremfi@NG.19";
        $key = hash('sha256', $secret);

        $ivSize = openssl_cipher_iv_length($method);
        $iv = openssl_random_pseudo_bytes($ivSize);
        $iv = base64_encode($iv);

        $encrypted = openssl_encrypt($data, $method, $key, OPENSSL_RAW_DATA, $iv);
        $encrypted = base64_encode($iv . $encrypted);

        return $encrypted;
    }

    //FOR PHP 7.3.7 :: USES open_ssl
    public function decrypt($data, $secret="mykey") {
        $method = 'AES-256-CBC';
        $secret .= "?coremfi@NG.19";
        $key = hash('sha256', $secret);

        $data = base64_decode($data);
        $ivSize = openssl_cipher_iv_length($method);
        $iv = substr($data, 0, $ivSize);
        $iv = base64_decode($iv);
        $data = openssl_decrypt(substr($data, $ivSize), $method, $key, OPENSSL_RAW_DATA, $iv);

        return $data;
    }
}
?>