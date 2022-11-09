<?php

namespace App\Service;

class ShopifyMultipass
{
    private $encryptionKey;
    private $signatureKey;

    public function __construct()
    {
        $multipassSecret = env('SHOPIFY_MULTIPASS');

        $keyMaterial = hash('sha256', $multipassSecret, true);
        $this->encryptionKey = substr($keyMaterial, 0, 16);
        $this->signatureKey = substr($keyMaterial, 16, 16);

    }

    /**
     * @param array $customerDataHash
     * @return string
     */
    public function generateToken(array $customerDataHash)
    {
        $ciphertext = $this->encrypt(json_encode($customerDataHash));

        return strtr(base64_encode($ciphertext . $this->sign($ciphertext)), '+/', '-_');
    }

    /**
     * @param string $plaintext
     * @return string
     */
    private function encrypt(string $plaintext){
        $iv = openssl_random_pseudo_bytes(16);

        return $iv . openssl_encrypt($plaintext, 'AES-128-CBC', $this->encryptionKey, OPENSSL_RAW_DATA, $iv);
    }

    /**
     * @param string $data
     * @return string
     */
    private function sign(string $data)
    {
        return hash_hmac('sha256', $data, $this->signatureKey, true);
    }
}
