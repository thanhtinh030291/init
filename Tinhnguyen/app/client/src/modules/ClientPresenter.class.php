<?php

namespace Lza\App\Client\Modules;


use Lza\Config\Models\ModelPool;
use Lza\LazyAdmin\Runtime\BasePresenter;
use Lza\LazyAdmin\Utility\Tool\Log\LogLevel;

/**
 * Base Presenter for Front End
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientPresenter extends BasePresenter
{
    /**
     * Get data which is used by any page
     *
     * @throws
     */
    public function doGetMasterData()
    {
        $model = ModelPool::getModel('lzalanguage');
        $this->data->languages = $model->order("order_by");
    }

    /**
     * Validate captcha if needed
     *
     * @throws
     */
    public function doVerifyCaptcha()
    {
        $secretKey = GOOGLE_RECAPTCHA_SECRET_KEY;
        $siteKeyPost = $this->request->post->gRecaptchaResponse;

        if (!empty($_SERVER['HTTP_CLIENT_IP']))
        {
            $remoteIp = $_SERVER['HTTP_CLIENT_IP'];
        }
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        {
            $remoteIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        else
        {
            $remoteIp = $_SERVER['REMOTE_ADDR'];
        }

        $apiUrl = "https://www.google.com/recaptcha/api/siteverify"
                . "?secret={$secretKey}&response={$siteKeyPost}&remoteip={$remoteIp}";
        $response = file_get_contents($apiUrl);
        $response = $this->encryptor->jsonDecode($response);

        return isset($response->success)
            && $response->success === true;
    }

    /**
     * Event when an action is successfully executed
     *
     * @throws
     */
    public function onSuccess($data = null)
    {
        $this->logger->log(LogLevel::SUCCESS, $data);
    }

    /**
     * Event when an action is failed to execute
     *
     * @throws
     */
    public function onError($message)
    {
        $this->logger->log(LogLevel::ERROR, $message);
    }
}
