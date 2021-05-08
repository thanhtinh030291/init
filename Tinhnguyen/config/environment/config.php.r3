<?php

if (empty($_SERVER['DOCUMENT_ROOT']))
{
    $_SERVER['DOCUMENT_ROOT'] = str_replace(DIRECTORY_SEPARATOR, '/', __DIR__);
}
if (empty($_SERVER['REQUEST_SCHEME']))
{
    $_SERVER['REQUEST_SCHEME'] = 'https';
}
if (empty($_SERVER['REQUEST_URI']))
{
    $_SERVER['REQUEST_URI'] = '';
}
if (empty($_SERVER['HTTP_HOST']))
{
    $_SERVER['HTTP_HOST'] = '';
}


// Available Running Modes: development, staging and production
$httpHost = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? ( !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : __DIR__ );
// if( strpos( $httpHost, 'local' ) !== false || strpos( $httpHost, 'dev' ) !== false ) {
    define('RUNNING_MODE', 'development');
// } elseif( strpos( $httpHost, 'uat' ) !== false || strpos( $httpHost, 'test' ) !== false ) {
    // define('RUNNING_MODE', 'staging');
// } else {
    // define('RUNNING_MODE', 'production');
// }
require_once __DIR__ . DIRECTORY_SEPARATOR . RUNNING_MODE . '.php';

// Get and set file by prameter
define('UPLOAD_PATH',realpath($_SERVER["DOCUMENT_ROOT"])."/root/app/client/res/");
define('UPLOAD_URL', $httpHost."/resources/");

define('VERSION_ID', '133771ac773becf8fc4f73591698829d');
define('DOCUMENT_ROOT', str_replace('/config/public', '', $_SERVER['DOCUMENT_ROOT']) . ROOT_FOLDER);
define('PROTOCOL', strtolower($_SERVER['REQUEST_SCHEME']) . '://');
define('REQUESTED_HOST', isset($_SERVER['HTTP_X_FORWARDED_HOST'])
        ? $_SERVER['HTTP_X_FORWARDED_HOST']
        : $_SERVER['HTTP_HOST']
);
define('WEBSITE_ROOT', PROTOCOL . REQUESTED_HOST . ROOT_FOLDER);
define('WEBSITE_URL', "{$_SERVER['REQUEST_SCHEME']}://" . REQUESTED_HOST . $_SERVER['REQUEST_URI']);
define('RESOURCE_LIBRARY_PATH', ROOT_FOLDER . 'lib/');
define('CONFIG_PATH', DOCUMENT_ROOT . 'config/');
define('LIBRARY_PATH', DOCUMENT_ROOT . 'lib/');
define('CACHE_PATH', DOCUMENT_ROOT . 'temp/cch/');
define('COMPILE_PATH', DOCUMENT_ROOT . 'temp/cpl/');

define('SINGLE_SIGN_ON_PREFIX', 'CC');
define('SINGLE_SIGN_ON_SUFFIX', 'CC');

ini_set('default_charset', CHARSET);
ini_set('post_max_size', POST_SIZE);
ini_set('memory_limit', RAM_LIMIT);
ini_set('max_execution_time', TIMEOUT);

if (DEBUG_ERROR)
{
    ini_set('display_errors', 1);
}

set_error_handler(function($severity, $message, $file, $line)
{
    if (!(error_reporting() & $severity))
    {
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

spl_autoload_register(function($class)
{
    $ds = DIRECTORY_SEPARATOR;

    $class = str_replace('\\\\', '\\', $class);
    if (DEBUG_AUTOLOAD)
    {
        echo "<h1 style=\"display: none\">Loading {$class}</h1>\n";
    }

    $namespaces = explode('\\', $class);
    array_splice($namespaces, 0, 1);
    for ($i = 0, $count = count($namespaces); $i < $count - 1; ++$i)
    {
        $namespaces[$i] = strtolower($namespaces[$i]);
    }

    $needles = [
        "lazyadmin{$ds}",
        "admin{$ds}",
        "client{$ds}",
        "task{$ds}"
    ];

    $haystack = [
        "core{$ds}",
        "admin{$ds}src{$ds}",
        "client{$ds}src{$ds}",
        "task{$ds}src{$ds}"
    ];

    $classpath = str_replace($needles, $haystack, implode($ds, $namespaces));
    require_once dirname(dirname(__DIR__)) . "{$ds}{$classpath}.class.php";
});

function println($text)
{
    echo php_sapi_name() !== 'cli' ? "<pre>{$text}</pre>" : "{$text}\n";
}
function fpath($path)
{
    return str_replace('//', '/', $path);
}

function text_start_with($haystack, $needle)
{
    return substr($haystack, 0, strlen($needle)) === $needle;
}

function text_end_with($haystack, $needle)
{
    $length = strlen($needle);
    return $length === 0 ? true : (substr($haystack, -$length) === $needle);
}

function chain_case($string, $capitalize = false)
{
    if (preg_match('/^[^a-z]*$/', $string))
    {
        return $capitalize ? $string : str_replace('_', '-', strtolower($string));
    }
    if (preg_match('/^[^A-Z]*$/', $string))
    {
        return str_replace('_', '-', $capitalize ? strtoupper($string) : $string);
    }
    $function = $capitalize ? 'strtoupper' : 'strtolower';
    $string = str_replace([' ', '_'], '-', preg_replace('/([a-z0-9])?([A-Z])/', '$1-$2', $string));
    return trim($function($string), '-');
}

function snake_case($string, $capitalize = false)
{
    if (preg_match('/^[^a-z]*$/', $string))
    {
        return $capitalize ? $string : str_replace('-', '_', strtolower($string));
    }
    if (preg_match('/^[^A-Z]*$/', $string))
    {
        return str_replace('-', '_', $capitalize ? strtoupper($string) : $string);
    }
    $function = $capitalize ? 'strtoupper' : 'strtolower';
    $string = str_replace([' ', '-'], '_', preg_replace('/([a-z0-9])?([A-Z])/', '$1_$2', $string));
    return trim($function($string), '_');
}

function camel_case($string, $capitalize = false)
{
    $string = str_replace('-', '', ucwords(str_replace('_', '-', strtolower($string)), '-'));
    return $capitalize ? $string : lcfirst($string);
}

function initcap($string)
{
    return preg_replace('/([a-z0-9])?([A-Z])/', '$1 $2', camel_case($string, true));
}

function array_is_assoc($array)
{
    return !ctype_digit(implode('', array_keys($array))) || !isset($array[0]);
}

function array_transpose($array)
{
    $result = [];
    foreach ($array as $key => $sub)
    {
        if (!is_array($sub))
        {
            throw new ErrorException('Can only transpose Bi-dimensional array!');
        }

        foreach($sub as $subkey => $value)
        {
            $result[$subkey][$key] = $value;
        }
    }
    return $result;
}

function name_format($string)
{
    $string = preg_replace('/([a-z0-9])?([A-Z])/', '$1 $2', str_replace(['_', '-'], ' ', $string));
    return trim(ucwords($string), ' ');
}

function string_format($string, $params)
{
    $largest = 0;
    foreach (array_keys($params) as $key)
    {
        if (($length = strlen($key)) > $largest)
        {
            $largest = $length;
        }
    }

    $buffer = '';

    $conditionalParenthesis = false;
    $insideParameter = false;
    $isSetParameter = false;

    $bufferLength = 1;
    $param = '';

    $output = '';
    $string .= '!';

    for (
        $characterCount = 0, $character = $originalCharacter = '';
        isset($string[$characterCount]);
        ++$characterCount, ++$bufferLength
    )
    {
        $character = $string[$characterCount];

        if ($insideParameter)
        {
            $ascii = ord($character);

            if (!(
                    $ascii === 95 ||
                    (
                        ($ascii >= 48 && $ascii <= 57) ||
                        ($ascii >= 65 && $ascii <= 90) ||
                        ($ascii >= 97 && $ascii <= 122)
                    )
                )
            )
            {
                $isSetParameter = isset($params[$buffer]);

                if (!$conditionalParenthesis && !$isSetParameter)
                {
                    $message = sprintf(__FUNCTION__ . ': the parameter "%s" is not defined', $buffer);
                    throw new ErrorException($message);
                }
                elseif (!$conditionalParenthesis || $isSetParameter)
                {
                    $output .= $params[$buffer];
                }

                $isSetParameter = $isSetParameter && !empty($params[$buffer]);
                $originalCharacter = $buffer = '';
                $bufferLength = 0;
                $insideParameter = false;
            }
        }

        if ($conditionalParenthesis && $character === ')')
        {
            $output .= $buffer;

            $conditionalParenthesis = $isSetParameter = false;
            $character = $buffer = '';
            $bufferLength = 0;
        }

        if (($conditionalParenthesis && $isSetParameter) || $insideParameter)
        {
            $buffer .= $character;
        }

        if ($character === '$' && $originalCharacter !== '\\')
        {
            if ($originalCharacter === '(')
            {
                $conditionalParenthesis = true;
            }
            else
            {
                $output .= $originalCharacter;
            }

            $insideParameter = true;
            $buffer = $character = $originalCharacter = '';
            $bufferLength = 0;
        }

        if (!$conditionalParenthesis && $bufferLength > $largest)
        {
            $buffer = substr($buffer, -$largest);
            $bufferLength = $largest;
        }

        if (!$insideParameter && (!$conditionalParenthesis || ($conditionalParenthesis && $isSetParameter)))
        {
            $output .= $originalCharacter;
            if (!$conditionalParenthesis)
            {
                $originalCharacter = $character;
            }
        }
    }

    return $output;
}

function price_format($amount, $decimal = 2)
{
    return number_format($amount, $decimal, '.', '');
}

function truncate($string, $charCount = 100)
{
    return mb_strimwidth($string, 0, $charCount, "...");
}

function nvl(&$var, $default = true)
{
    return isset($var) ? $var : $default;
}

function color_encode($string)
{
    return substr(dechex(crc32($string)), 0, 6);
}

function hyphenize($text)
{
    $utf8 = [
        '/[áàảãạăắằẳẵặâấầẩậªä]/u' => 'a',
        '/[ÁÀẢÃẠĂẮẰẲẴÂẤẦẨẪÄ]/u' => 'A',
        '/[ÍÌỈĨỊÎÏ]/u' => 'I',
        '/[íìỉĩịîï]/u' => 'i',
        '/[éèẻẽẹêếềểễệë]/u' => 'e',
        '/[ÉÈẺẼẸÊẾỀỂỄỆË]/u' => 'E',
        '/[óòỏõọôốồổỗộơớờởỡợºö]/u' => 'o',
        '/[ÓÒỎÕỌÔỐỒỔỖỘƠỚỜỞỠỢÖ]/u' => 'O',
        '/[úùủũụưứừửữựûü]/u' => 'u',
        '/[ÚÙỦŨỤƯỨỪỬỮỰÛÜ]/u' => 'U',
        '/ç/' => 'c',
        '/Ç/' => 'C',
        '/đ/' => 'd',
        '/Đ/' => 'D',
        '/ñ/' => 'n',
        '/Ñ/' => 'N',
        '/–/' => '-',
        '/[’‘‹›‚]/u' => ' ',
        '/[“”«»„]/u' => ' ',
        '/ /' => ' ',
        '#[\\s-]+#' => '-',
        '#[^A-Za-z0-9\. -]+#' => ''
    ];
    return strtolower(preg_replace(array_keys($utf8), array_values($utf8), urldecode($text)));
}

function dd()
{
    if (php_sapi_name() === 'cli')
    {
        var_dump(func_get_args());
        exit;
    }

    echo '
        <style type="text/css">
            .dumpr {
                background-color: #fcfcfc;
                border: 1px solid #d9d9d9;
            }
            .dumpr pre {
                color: #000000;
                font-size: 9pt;
                font-family: "Courier New",Courier,Monaco,monospace;
                margin: 0px;
                padding-top: 5px;
                padding-bottom: 7px;
                padding-left: 9px;
                padding-right: 9px;
            }
            .dumpr span.string {
                color: #c40000;
            }
            .dumpr span.number {
                color: #ff0000;
            }
            .dumpr span.keyword {
                color: #007200;
            }
            .dumpr span.function {
                color: #0000c4;
            }
            .dumpr span.object {
                color: #ac00ac;
            }
            .dumpr span.type {
                color: #0072c4;
            }
        </style>
    ';

    echo '<div class="dumpr">';
    $func = function($data)
    {
        ob_start();
        var_dump($data);
        $c = ob_get_contents();
        ob_end_clean();

        $c = preg_replace("/\r\n|\r/", "\n", $c);
        $c = str_replace("]=>\n", '] = ', $c);
        $c = preg_replace('/= {2,}/', '= ', $c);
        $c = preg_replace("/\[\"(.*?)\"\] = /i", "[$1] = ", $c);
        $c = preg_replace('/  /', "    ", $c);
        $c = preg_replace("/\"\"(.*?)\"/i", "\"$1\"", $c);
        $c = preg_replace("/(int|float)\(([0-9\.]+)\)/i", "$1(<span class=\"number\">$2</span>)", $c);

        $c = preg_replace("/(\[[\w ]+\] = string\([0-9]+\) )\"(.*?)/sim", "$1<span class=\"string\">\"", $c);
        $c = preg_replace("/(\"\n{1,})( {0,}\})/sim", "$1</span>$2", $c);
        $c = preg_replace("/(\"\n{1,})( {0,}\[)/sim", "$1</span>$2", $c);
        $c = preg_replace(
            "/(string\([0-9]+\) )\"(.*?)\"\n/sim", "$1<span class=\"string\">\"$2\"</span>\n", $c
        );

        $regex = [
            'numbers' => [
                '/(^|] = )(array|float|int|string|resource|object\(.*\)|\&amp;object\(.*\))\(([0-9\.]+)\)/i',
                '$1$2(<span class="number">$3</span>)'
            ],
            'null' => [
                '/(^|] = )(null)/i',
                '$1<span class="keyword">$2</span>'
            ],
            'bool' => [
                '/(bool)\((true|false)\)/i',
                '$1(<span class="keyword">$2</span>)'
            ],
            'types' => [
                '/(of type )\((.*)\)/i',
                '$1(<span class="type">$2</span>)'
            ],
            'object' => [
                '/(object|\&amp;object)\(([\w]+)\)/i',
                '$1(<span class="object">$2</span>)'
            ],
            'function' => [
                '/(^|] = )(array|string|int|float|bool|resource|object|\&amp;object)\(/i',
                '$1<span class="function">$2</span>('
            ]
        ];
        foreach ($regex as $x)
        {
            $c = preg_replace($x[0], $x[1], $c);
        }

        $c = preg_replace("/\n<\/span>/", "</span>\n", trim($c));
        echo "<pre>$c</pre>";
    };
    array_map($func, func_get_args());
    echo '</div>';

    unset($_SESSION);
    die;
}
