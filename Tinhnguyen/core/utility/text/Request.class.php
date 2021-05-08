<?php

namespace Lza\LazyAdmin\Utility\Text;


/**
 * Request stores $_GET, $_POST, $_REQUEST variables after sanitized
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class Request extends Environment
{
    /**
     * @throws
     */
    public function __construct($data)
    {
        parent::__construct();
        $this->data = [];
        foreach ($data as $key => $value)
        {
            $key = camel_case($key);
            $key = strlen($key) > 0 ? $key : '_';
            $this->$key = $this->sanitize($value);
        }
    }

    /**
     * @throws
     */
    public function getClientIp()
    {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
        {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        }
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
        {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        }
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        {
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        }
        else if(isset($_SERVER['HTTP_FORWARDED']))
        {
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        }
        else if(isset($_SERVER['REMOTE_ADDR']))
        {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        }
        else
        {
            $ipaddress = 'UNKNOWN';
        }
        return $ipaddress;
    }

    /**
     * @throws
     */
    protected function sanitize($item)
    {
        if ($item === null)
        {
            return null;
        }
        elseif (is_object($item))
        {
            foreach($item as $key => $value)
            {
                $key = htmlspecialchars($key, ENT_QUOTES,'UTF-8');
                $item->$key = $this->sanitize($value);
            }
        }
        elseif (is_array($item))
        {
            if (array_is_assoc($item))
            {
                foreach($item as $key => $value)
                {
                    $item[htmlspecialchars($key, ENT_QUOTES,'UTF-8')] = $this->sanitize($value);
                }
            }
            else
            {
                foreach($item as $child)
                {
                    $child = $this->sanitize($child);
                }
            }
            return $item;
        }
        else
        {
            return htmlspecialchars($item, ENT_QUOTES,'UTF-8');
        }
    }
}
