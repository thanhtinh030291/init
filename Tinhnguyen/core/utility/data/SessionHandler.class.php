<?php

namespace Lza\LazyAdmin\Utility\Data;


use Exception;
use Lza\LazyAdmin\Utility\Data\DatabasePool;
use Lza\LazyAdmin\Utility\Pattern\Singleton;
use SessionHandlerInterface;

/**
 * SessionHandler Singleton
 * Write session to database and read session from database
 * Support PHP Cluster
 *
 * @singleton
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class SessionHandler implements SessionHandlerInterface
{
    use Singleton;

    /**
     * @var object Database Handler
     */
    private $db;

    /**
     * @throws
     */
    private function __construct()
    {
        ini_set('session.gc-maxlifetime', SESSION_LIFETIME);

        if (STORE_SESSION_IN_DATABASE)
        {
            session_set_save_handler($this, true);
        }
        else
        {
            session_save_path(SESSION_SAVE_PATH);
        }
    }

    /**
     * @throws
     */
    public function start()
    {
        if (session_id() === '')
        {
            session_start();
        }
    }

    /**
     * @throws
     */
    public function open($path, $name)
    {
        $this->db = DatabasePool::getDatabase();
        return true;
    }

    /**
     * @throws
     */
    public function __set($key, $value)
    {
        $key = snake_case($key);
        $this->set($key, $value);
    }

    /**
     * @throws
     */
    public function set($key, $value)
    {
        $item = &$_SESSION;
        $keys = explode('.', $key);
        foreach ($keys as $key)
        {
            if (!isset($item[$key]))
            {
                $item[$key] = [];
            }
            $item = &$item[$key];
        }
        $item = $value;
    }

    /**
     * @throws
     */
    public function __unset($key)
    {
        $key = snake_case($key);
        $this->remove($key);
    }

    /**
     * @throws
     */
    public function remove($key)
    {
        $item = &$_SESSION;
        $keys = explode('.', $key);
        foreach ($keys as $key)
        {
            if (!isset($item[$key]))
            {
                $item[$key] = [];
            }
            $item = &$item[$key];
        }
        $item = null;
    }

    /**
     * @throws
     */
    public function add($key, $value)
    {
        $item = &$_SESSION;
        $keys = explode('.', $key);
        foreach ($keys as $key)
        {
            if (!isset($item[$key]))
            {
                $item[$key] = [];
            }
            $item = &$item[$key];
        }
        $item[] = $value;
    }

    /**
     * @throws
     */
    public function write($id, $data)
    {
        try
        {
            $data = base64_encode($data);
            $now = date('Y-m-d H:i:s');
            $result = $this->db->lzasession("id", $id)->fetch();
            if ($result)
            {
                $result->update([
                    'access' => $now,
                    'data' => $data
                ]);
            }
            else
            {
                $this->db->lzasession()->insert([
                    'id' => $id,
                    'start' => $now,
                    'access' => $now,
                    'data' => $data
                ]);
            }
        }
        catch (Exception $e)
        {
            // return false;
        }
        return true;
    }

    /**
     * @throws
     */
    public function __get($key)
    {
        $key = snake_case($key);
        return $this->get($key);
    }

    /**
     * @throws
     */
    public function get($key)
    {
        $item = $_SESSION;
        $keys = explode('.', $key);
        foreach ($keys as $key)
        {
            $item = isset($item[$key]) ? $item[$key] : null;
        }
        return $item;
    }

    /**
     * @throws
     */
    public function read($id)
    {
        $result = $this->db->lzasession("id", $id)->fetch();
        return $result ? base64_decode($result['data']) : '';
    }

    /**
     * @throws
     */
    public function close()
    {
        $this->db = null;
        return true;
    }

    /**
     * @throws
     */
    public function destroy($id)
    {
        try
        {
            $this->db->lzasession("id", $id)->delete();
        }
        catch (Exception $e)
        {
            // return false;
        }
        return true;
    }

    /**
     * @throws
     */
    public function gc($ttl)
    {
        $sessions = $this->db->lzasession(
            "access < ?",
            date('Y-m-d H:i:s', time() - $ttl)
        );
        $sessions->delete();
        return false;
    }

    /**
     * @throws
     */
    public function __destruct()
    {
        session_write_close();
    }
}
