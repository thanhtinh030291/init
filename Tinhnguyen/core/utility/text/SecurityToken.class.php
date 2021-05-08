<?php

namespace Lza\LazyAdmin\Utility\Text;


use Lza\LazyAdmin\Exception\ValidateSecurityTokenException;
use Lza\LazyAdmin\Utility\Pattern\Singleton;

/**
 * Security Token provides function to generate Security Token and validate it
 *
 * @var session
 * @var request
 *
 * @singleton
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class SecurityToken
{
    use Singleton;

    private $tokens = [];

    /**
     * @throws
     */
    public function generateField($name)
    {
        $name = snake_case($name);
        return sprintf('<input type="hidden" name="%s" value="%s"/>', "{$name}_token", $this->generate($name));
    }

    /**
     * @throws
     */
    public function generate($name)
    {
        $name = snake_case($name);
        $date = date('Ymd');
        $token = $date . sha1(time() . mt_rand());

        $this->session->set("form_security_tokens.{$name}.{$date}.{$token}", true);
        return $token;
    }

    /**
     * @throws
     */
    public function validate($name)
    {
        $name = snake_case($name);
        $tokens = $this->session->get("form_security_tokens.{$name}");
        if ($tokens === null || !is_array($tokens) || count($tokens) < 1)
        {
            return false;
        }

        $tokenName = "{$name}_token";
        $tokenValue = isset($this->request->$tokenName) ? $this->request->$tokenName : null;
        if (!strlen($tokenValue))
        {
            return false;
        }

        $date = substr($tokenValue, 0, 8);
        if (!isset($tokens[$date][$tokenValue]))
        {
            return false;
        }
        return true;
    }

    /**
     * @throws
     */
    public function purge($name)
    {
        $name = snake_case($name);
        $tokens = $this->session->get("form_security_tokens.{$name}");
        if ($tokens === null || !is_array($tokens) || count($tokens) < 1)
        {
            return;
        }

        $tokenName = "{$name}_token";
        $tokenValue = isset($this->request->$tokenName) ? $this->request->$tokenName : null;
        $date = mb_substr($tokenValue, 0, 8);
        $purgeDate = date('Ymd', time() - (3 * 86400));

        $this->session->remove("form_security_tokens.{$name}.{$date}.{$tokenValue}");

        foreach ($this->session->formSecurityTokens as $formName => $dates)
        {
            foreach($dates as $date => $tokens)
            {
                if ($date < $purgeDate)
                {
                    $this->session->remove("form_security_tokens.{$formName}.{$date}");
                }
            }
        }
    }
}
