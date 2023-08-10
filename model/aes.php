<?php
    ini_set('display_errors',1);
    error_reporting(E_ALL);
    // DEFINE our cipher
    define('AES_256_CBC', 'aes-256-cbc');

    class AES
    {
        public $encryption_key = "3s6v9yB&E)H@McQfTjWmZq4t7w!z%C*r";
        public $iv = "3s6v9yB&E)H@McQf";
        public $deault_key;

        public function __construct($key)
        {
            // $this->deault_key = $key;
            // Generates a string of pseudo-random bytes, with the number of bytes determined by the length parameter.

            // It also indicates if a cryptographically strong algorithm was used to produce the pseudo-random bytes, and does this via the optional crypto_strong parameter. It's rare for this to be FALSE, but some systems may be broken or old.

            // @param int $length
            // The length of the desired string of bytes. Must be a positive integer. PHP will try to cast this parameter to a non-null integer to use it.
            // @param bool &$strong_result
            // [optional]
            // If passed into the function, this will hold a boolean value that determines if the algorithm used was "cryptographically strong", e.g., safe for usage with GPG, passwords, etc. true if it did, otherwise false
            // @return string|false
            // the generated string of bytes on success, or false on failure.
            // @link https://php.net/manual/en/function.openssl-random-pseudo-bytes.php
            // Generate a pseudo-random string of bytes

            // openssl_random_pseudo_bytes( int $length [, bool $crypto_strong ]): string

            // Generate a 256-bit encryption key
            // This should be stored somewhere instead of recreating it each time
            $this->encryption_key = $this->encryption_key;


            // Generate an initialization vector
            // This *MUST* be available for decryption as well
            $this->iv = $this->iv;
            // $this->iv = substr($key,0,16);

        }

        function unsetData($data)
        {
            unset($data["auth_username"]);
            unset($data["auth_email"]);
            unset($data["auth_firstname"]);
            unset($data["auth_lastname"]);
            unset($data["auth_role_id"]);
            unset($data["auth_station_code"]);
            unset($data["auth_company_id"]);
            unset($data["usage_channel"]);

            return true;
        }

        function encryptData($data)
        {
            // $this->unsetData($data);
            unset($data["auth_username"]);
            unset($data["auth_email"]);
            unset($data["auth_firstname"]);
            unset($data["auth_lastname"]);
            unset($data["auth_role_id"]);
            unset($data["auth_station_code"]);
            unset($data["auth_company_id"]);
            unset($data["usage_channel"]);
            
            $array = array();

            foreach($data as $key => $value){
                
                if (is_array($value)) {
                    // recall the encryptData() to loop through the array again
                    $array[$key] = $this->encryptData($value);
                } else {
                    $array[$key] = $this->encrypt($value);
                }
            }

            return $array;
        }
        
        function decryptData($data)
        {
            unset($data["auth_username"]);
            unset($data["auth_email"]);
            unset($data["auth_firstname"]);
            unset($data["auth_lastname"]);
            unset($data["auth_role_id"]);
            unset($data["auth_station_code"]);
            unset($data["auth_company_id"]);
            unset($data["usage_channel"]);
            // $this->unsetData($data);
            
            $array = array();

            foreach($data as $key => $value){
                if (is_array($value)) {
                    $array[$key] = $this->decryptData($value);
                } else {
                    $array[$key] = $this->decrypt($value);
                }
            }

            return $array;
        }

        function encrypt($data)
        {   

            // Encrypt $data using aes-256-cbc cipher with the given encryption key and
            // our initialization vector. The 0 gives us the default options, but can
            // be changed to OPENSSL_RAW_DATA or OPENSSL_ZERO_PADDING
            
            return openssl_encrypt($data, AES_256_CBC, $this->encryption_key, 0, $this->iv);
        }

        function decrypt($data)
        {   
            // If we lose the $iv variable, we can't decrypt this, so:
            // - $data is already base64-encoded from openssl_encrypt
            // - Append a separator that we know won't exist in base64, ":"
            // - And then append a base64-encoded $iv
            $data = $data . ':' . base64_encode($this->iv);

            // To decrypt, separate the encrypted data from the initialization vector ($iv).
            $parts = explode(':', $data);
            // $parts[0] = encrypted data
            // $parts[1] = base-64 encoded initialization vector

            // Don't forget to base64-decode the $iv before feeding it back to
            //openssl_decrypt
            return openssl_decrypt($parts[0], AES_256_CBC, $this->encryption_key, 0, base64_decode($parts[1]));

        }
    }

?>