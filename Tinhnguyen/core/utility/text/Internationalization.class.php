<?php

namespace Lza\LazyAdmin\Utility\Text;


use Lza\LazyAdmin\Utility\Data\DatabasePool;
use Lza\LazyAdmin\Utility\Data\SessionHandler;

/**
 * Internationalization provides function to get localized texts
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class Internationalization
{
    /**
     * @var SessionHandler
     */
    private $session = [];

    /**
     * @var array List of Localed texts string from file
     */
    private $texts = [];

    /**
     * @var array List of Localed texts string from database
     */
    private $dbTexts = [];

    /**
     * @throws
     */
    public function __construct($path)
    {
        $ds = DIRECTORY_SEPARATOR;
        $model = DatabasePool::getDatabase()->lzalanguage();
        $langs = $model->select('code');
        foreach ($langs as $lang)
        {
            $this->texts[$lang['code']] = new Property(
                "{$path}lang{$lang['code']}.txt",
                dirname(dirname(dirname(__DIR__))) . "{$ds}config{$ds}assets{$ds}lang{$lang['code']}.txt"
            );
        }
        $this->session = SessionHandler::getInstance();
    }

    /**
     * @throws
     */
    public function __get($key)
    {
        return $this->texts[$this->session->lzalanguage]->$key;
    }

    /**
     * @throws
     */
    public function __call($method, $params)
    {
        return call_user_func_array('sprintf',
            array_merge([$this->texts[$this->session->lzalanguage]->$method], $params)
        );
    }

    /**
     * @throws
     */
    public function lang($lang)
    {
        if (!isset($lang))
        {
            $lang = $this->session->lzalanguage;
        }

        return $this->texts[$lang];
    }

    /**
     * @throws
     */
    public function get()
    {
        return func_num_args() === 0 ? false : call_user_func_array(
            [$this, 'getBylang'], array_merge([$this->session->lzalanguage], func_get_args())
        );
    }

    /**
     * @throws
     */
    public function getBylang()
    {
        $argCount = func_num_args();
        if ($argCount < 2)
        {
            return false;
        }
        $args = func_get_args();

        if (!isset($this->dbTexts[$args[0]]))
        {
            $this->dbTexts[$args[0]] = [];
        }
        if (!isset($this->dbTexts[$args[0]][$args[1]]))
        {
            $lzatextss = DatabasePool::getDatabase()->lzatext("name", $args[1]);
            $lzatexts = $lzatextss->select("content{$args[0]}, content")->fetch();
            if (!$lzatexts)
            {
                $this->dbTexts[$args[0]][$args[1]] = $args[1];
                return $args[1];
            }
            $this->dbTexts[$args[0]][$args[1]] = strlen($lzatexts["content{$args[0]}"]) > 0
                    ? $lzatexts["content{$args[0]}"]
                    : $lzatexts["content"];
        }
        $template = $this->dbTexts[$args[0]][$args[1]];

        $result = $template;
        if ($argCount > 2)
        {
            unset($args[0]);
            unset($args[1]);
            $result = vsprintf($template, $args);
        }
        return $result;
    }

    public function dateStr($string)
    {
        if ($this->session->lzalanguage === '_vi')
        {
            setlocale(LC_ALL, 'vi_VN.utf8');
            $str_co = strftime('%d-%B-%Y', strtotime($string));
        }
        else
        {
            setlocale(LC_ALL, 'en_US.utf8');
            $str_co = strftime('%d-%b-%Y', strtotime($string));
        }

        return $str_co;
    }
}