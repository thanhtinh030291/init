<?php

namespace Lza\Config;


use Lza\LazyAdmin\Core;
use Lza\LazyAdmin\Utility\Pattern\DIContainer;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class Application extends Core
{
    /**
     * @throws
     */
    protected function defineConstants($region)
    {
        parent::defineConstants($region);
        $this->router->defineConstants($this);
        $dateFroms = ['d', 'm', 'Y'];
        $datetimeFroms = ['d', 'm', 'Y', 'H', 'i', 's'];

        define('SQL_DATE_FORMAT', $this->setting->dateFormat);
        define('SQL_DATETIME_FORMAT', $this->setting->datetimeFormat);
        define('DATE_FORMAT', str_replace('%', '', SQL_DATE_FORMAT));
        define('DATETIME_FORMAT', str_replace('%', '', SQL_DATETIME_FORMAT));
        define('ORA_DATE_FORMAT', str_replace($dateFroms, ['DD', 'MM', 'YYYY'], DATE_FORMAT));
        define('ORA_DATETIME_FORMAT', str_replace($datetimeFroms, ['DD', 'MM', 'YYYY', 'HH24', 'MI', 'SS'], DATETIME_FORMAT));
        define('JS_DATE_FORMAT', str_replace($dateFroms, ['DD', 'MM', 'YYYY'], DATE_FORMAT));
        define('JS_DATETIME_FORMAT', str_replace($datetimeFroms, ['DD', 'MM', 'YYYY', 'hh', 'mm', 'ss'], DATETIME_FORMAT));
        define('REGEX_DATE_FORMAT', str_replace($dateFroms, ['(0[1-9]|[12][0-9]|3[01])', '(0[1-9]|1[0-2])', '\d\d\d\d'], DATE_FORMAT));
        define('REGEX_DATETIME_FORMAT', str_replace(
            $datetimeFroms, [
                '(0[1-9]|[12][0-9]|3[01])',
                '(0[1-9]|1[0-2])',
                '\d\d\d\d',
                '([01][0-9]|2[0-3])',
                '([0-5][0-9])',
                '([0-5][0-9])'
            ],
            DATETIME_FORMAT
        ));
    }

    /**
     * @throws
     */
    protected function route($region, $params)
    {
        $this->router->route($params);

        $parameters = [
            'region' => 'client',
            'module' => 'client',
            'view' => 'listall',
            'action' => '',
            'page' => 1,
            'id' => 1,
            'level' => null,
            'child1' => null,
            'child2' => null,
            'child3' => null,
            'ref' => null
        ];
        foreach ($parameters as $parameter => $default)
        {
            if (!isset($this->env->$parameter))
            {
                $this->env->$parameter = $default;
            }
            $$parameter = $this->env->$parameter;
        }

        foreach (array_keys($parameters) as $parameter)
        {
            DIContainer::bindValue($parameter, $$parameter);
            $this->env->$parameter = $$parameter;
        }
    }

    /**
     * @throws
     */
    public function launch($region)
    {
        $this->router->launch();
    }
}
