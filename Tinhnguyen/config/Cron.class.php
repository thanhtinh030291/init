<?php

namespace Lza\Config;


use Ahc\Cron\Expression;
use Lza\Config\Models\ModelPool;
use Lza\LazyAdmin\Core;
use Lza\LazyAdmin\Utility\Pattern\DIContainer;
use Lza\LazyAdmin\Utility\Tool\FirebaseCloudMessageHandler;
use Lza\LazyAdmin\Utility\Tool\HttpRequestHandler;
use Lza\LazyAdmin\Utility\Tool\PHPMailHandler;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class Cron extends Core
{
    private $tasks = [];

    /**
     * @throws
     */
    protected function defineConstants($region)
    {
        global $ds;
        define('RES_PATH', dirname(__DIR__) . "{$ds}app{$ds}task{$ds}res");
    }

    /**
     * @throws
     */
    protected function route($region, $params)
    {
        $model = ModelPool::getModel('Lzatask');
        $tasks = $model->where('enabled=1');
        foreach ($tasks as $task)
        {
            $task['params'] = explode(',', $task['params']);
            $this->tasks[] = [
                'time' => implode(' ', [
                    $task['minute'],
                    $task['hour'],
                    $task['month_day'],
                    $task['month'],
                    $task['week_day'],
                ]),
                'class' => $task['class'],
                'params' => $task['params']
            ];
        }
        $this->tasks[] = [
            'time' => '* * * * *',
            'class' => FirebaseCloudMessageHandler::class,
            'params' => []
        ];
        $this->tasks[] = [
            'time' => '* * * * *',
            'class' => PHPMailHandler::class,
            'params' => []
        ];
        $this->tasks[] = [
            'time' => '* * * * *',
            'class' => HttpRequestHandler::class,
            'params' => []
        ];
    }

    /**
     * @throws
     */
    public function launch($region)
    {
        $sapi = php_sapi_name();
        if ($sapi !== 'cli')
        {
            echo '<pre>';
            $args = array_values($_GET);
        }
        else
        {
            global $argv;
            $args = $argv;
        }

        $now = time();
        $expr = DIContainer::resolve(Expression::class);
        foreach ($this->tasks as $task)
        {
            if (FORCE_TASK_RUN || in_array('-force', $args) || $expr->isCronDue($task['time'], $now))
            {
                echo "Run {$task['class']}\n";
                $params = array_merge([$task['class']], $task['params']);
                call_user_func_array(DIContainer::class . '::resolve', $params)->execute(true);
            }
        }
    }
}
