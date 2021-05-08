<?php

namespace Lza\App\Client\Modules\Api\V20\Info;


use Lza\App\Client\Modules\Api\V20\ClientApiV20Presenter;

/**
 * Handle Get Version action
 *
 * @author Chinh Le (chinhle@pacificcross.com.vn)
 */
class ClientApiInfoGetVersionPresenter extends ClientApiV20Presenter
{
    private $priority = [
        "FLEXIBLE",
        "IMMEDIATE"
    ];

    private $Android = [
        "version"   => "1.0.13",
        "build"     => "",
        "date"      => ""
    ];

    private $iOS = [
        "version"   => "1.0.12",
        "build"     => "1.0.2",
        "date"      => ""
    ];

    /**
     * Validate inputs and do Get Version request
     *
     * @throws
     */
    public function doGetVersion($search = null)
    {
        if (empty($search) || (count($search) == 1 && !empty($search['lang'])))
        {
            return false;
        }
        $devicetype = $search['devicetype'];
        if (!in_array($devicetype, ['Android', 'iOS']))
        {
            return false;
        }
        if ($search['lang'] == 'vi')
        {
            $this->{$search['devicetype']}['message'] = "Vui lòng cập nhật phiên bản mới {$this->{$search['devicetype']}['version']} bây giờ";
            $this->{$search['devicetype']}['type'] = $this->priority[1];
        }
        else
        {
            $this->{$search['devicetype']}['message'] = "A new version of app is available. Please update to version {$this->{$search['devicetype']}['version']} now.";
            $this->{$search['devicetype']}['type'] = $this->priority[1];
        }

        $version = [
            "Android" => $this->Android,
            "iOS" => $this->iOS
        ];
        return $version[$search['devicetype']];
    }
}
