<?php

namespace Lza\App\Task;


use Exception;
use Lza\Config\Models\ModelPool;
use Lza\LazyAdmin\Runtime\BaseTask;
use Lza\LazyAdmin\Utility\Data\DatabasePool;
use Lza\LazyAdmin\Utility\Data\Setting;
use Lza\LazyAdmin\Utility\Tool\PHPMailHandler;

/**
 * Import Provider from Main Website
 *
 * @var sql
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ImportProviderTask implements BaseTask
{
    /**
     * @throws
     */
    public function __construct()
    {

    }

    /**
     * @throws
     */
    public function execute($echo = false)
    {
        try
        {
			$mainDb = DatabasePool::getConnection();
			$mainDb->exec("truncate provider_info2");
            $model = ModelPool::getModel('ProviderInfo2', 'main');
            $provInfos = $this->sql->query($this->sql->pcvProvider(['where' => '']), [], 'main_website');
            foreach ($provInfos as $provInfo)
            {
				$provInfo['latitude'] = floatval($provInfo['latitude']);
				$provInfo['longitude'] = floatval($provInfo['longitude']);
                $model->insert($provInfo);
            }
			$mainDb->exec("truncate provider_info");
			$mainDb->exec("insert into provider_info select * from provider_info2");
        }
        catch (Exception $e)
        {
            $this->sendMail('Card Validation Error', 'Cannot get Providers!');
            $this->println($e->getMessage() . ' ' . $e->getTraceAsString(), $echo);
        }
    }

    /**
     * @throws
     */
    private function println($message, $echo = false)
    {
        if ($echo)
        {
            println($message);
        }
    }

    /**
     * @throws
     */
    private function sendMail($subject, $message)
    {
        $setting = Setting::getInstance();
        $email = $setting->email;
        PHPMailHandler::getInstance()->add(
            'system',
            $email,
            $email,
            SUPPORT_EMAIL,
            SUPPORT_EMAIL,
            $subject,
            $message
        );
    }
}
