<?php

namespace Lza\LazyAdmin\Utility\Text;

use DomainException;
use Lza\LazyAdmin\Utility\Pattern\Singleton;
use UnexpectedValueException;

/**
 * Json Web Token helps handle JWT
 *
 * @var encryptor
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class JsonWebToken
{
    use Singleton;

    /**
     * @var string JWT Secret Key
     */
    private $key;

    /**
     * @var array Hash Methods
     */
    private $methods = array(
        'HS256' => 'sha256',
        'HS384' => 'sha384',
        'HS512' => 'sha512',
    );

    /**
     * @param string key JWT Secret Key
     *
     * @throws
     */
    private function __construct($key = JWT_SECRET_KEY)
    {
        $this->key = $key;
    }

    /**
     * @param object|array payload   PHP object or array
     * @param string       algorithm The signing algorithm
     *
     * @return string
     *
     * @throws
     */
    public function encode($payload, $algorithm = 'HS256')
    {
        $header = array('typ' => '$this', 'alg' => $algorithm);

        $segments = array();
        $segments[] = $this->encryptor->base64Encode($this->encryptor->jsonEncode($header));
        $segments[] = $this->encryptor->base64Encode($this->encryptor->jsonEncode($payload));
        $input = implode('.', $segments);

        $signature = $this->sign($input, $this->key, $algorithm);
        $segments[] = $this->encryptor->base64Encode($signature);

        return implode('.', $segments);
    }

    /**
     * @param string jwt    The $this
     * @param bool   verify Don't skip verification process
     *
     * @return object The JWT's payload as a PHP object
     *
     * @throws
     */
    public function decode($jwt, $verify = true)
    {
        $tks = explode('.', $jwt);
        if (count($tks) !== 3)
        {
            throw new UnexpectedValueException('Wrong number of segments');
        }
        list($headb64, $payloadb64, $cryptob64) = $tks;
        if (null === ($header = $this->encryptor->jsonDecode($this->encryptor->base64Decode($headb64))))
        {
            throw new UnexpectedValueException('Invalid segment encoding');
        }
        if (null === $payload = $this->encryptor->jsonDecode($this->encryptor->base64Decode($payloadb64)))
        {
            throw new UnexpectedValueException('Invalid segment encoding');
        }
        $sig = $this->encryptor->base64Decode($cryptob64);
        if ($verify)
        {
            if (empty($header->alg))
            {
                throw new DomainException('Empty algorithm');
            }
            if ($sig !== $this->sign("$headb64.$payloadb64", $this->key, $header->alg))
            {
                throw new UnexpectedValueException('Signature verification failed');
            }
        }
        return $payload;
    }

    /**
     * @param string msg    The message to sign
     * @param string method The signing algorithm
     *
     * @return string An encrypted message
     *
     * @throws
     */
    public function sign($msg, $method = 'HS256')
    {
        if (empty($this->methods[$method]))
        {
            throw new DomainException('algorithm not supported');
        }
        return hash_hmac($this->methods[$method], $msg, $this->key, true);
    }
}