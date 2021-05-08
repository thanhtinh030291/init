<?php

namespace Lza\LazyAdmin\Utility\Text;


use ErrorException;
use Lza\LazyAdmin\Utility\Pattern\Singleton;

/**
 * Encryptor helps encrypt and decrypt data and files
 *
 * @singleton
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class Encryptor
{
    use Singleton;

    const METHOD = "AES-256-CBC";
    const KEY = "!@#$%^&*93800988";
    const IV = "CBC-256-AES";

    /**
     * @throws
     */
    public function encrypt($data, $recursive = 1)
    {
        $key = hash('sha256', self::KEY);
        $iv = substr(hash('sha256', self::IV), 0, 16);

        $data = openssl_encrypt($data, self::METHOD, self::KEY, 0, $iv);
        return $recursive <= 1 ? $data : $this->encrypt($data, $recursive - 1);
    }

    /**
     * @throws
     */
    public function decrypt($data, $recursive = 1)
    {
        $key = hash('sha256', self::KEY);
        $iv = substr(hash('sha256', self::IV), 0, 16);

        $data = openssl_decrypt($data, self::METHOD, self::KEY, 0, $iv);
        return $recursive <= 1 ? $data : $this->decrypt($data, $recursive - 1);
    }

    /**
     * @throws
     */
    public function hash($data, $recursive = 1)
    {
        $data = hash_hmac('SHA256', $data, self::KEY);
        return $recursive <= 1 ? $data : $this->hash($data, $recursive - 1);
    }

    /**
     * @throws
     */
    public function compareHash($value, $expected, $recursive = 1)
    {
        return $this->hash($value, $recursive) === $this->hash($expected, $recursive);
    }

    /**
     * @throws
     */
    public function jsonEncode($input, $options = 0, $depth = 512)
    {
        $json = json_encode($input, $options, $depth);
        if (function_exists('json_last_error') && $errno = json_last_error())
        {
            throw new ErrorException('Encode JSON Error: ' . $errno . ' - ' . json_last_error_msg() . ': ' . $input);
        }
        elseif ($json === 'null' && $input !== null)
        {
            throw new ErrorException('Encode JSON Error: Null result with non-null input');
        }
        return $json;
    }

    /**
     * @throws
     */
    public function jsonDecode($input, $assoc = false, $depth = 512, $options = 0)
    {
        $obj = json_decode($input, $assoc, $depth, $options);
        if (function_exists('json_last_error') && $errno = json_last_error())
        {
            throw new ErrorException('Decode JSON Error: ' . $errno . ' - ' . json_last_error_msg() . ': ' . $input);
        }
        elseif ($obj === null && $input !== 'null')
        {
            throw new ErrorException('Decode JSON Error: Null result with non-null input');
        }
        return $obj;
    }

    /**
     * @throws
     */
    public function base64Encode($input)
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }

    /**
     *@throws
     */
    public function base64Decode($input, $strict = false)
    {
        $remainder = strlen($input) % 4;
        if ($remainder)
        {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($input, '-_', '+/'), $strict);
    }
}
