<?php

namespace Lza\App\Client\Modules\Api\V20\Info;


use Lza\App\Client\Modules\Api\V20\ClientApiV20View;

/**
 * Process App info
 *
 * @author Chinh Le (chinhle@pacificcross.com.vn)
 */
class ClientApiInfoView extends ClientApiV20View
{
    /**
     * @api GET /api/info/version?devicetype=
     *
     * @throws
     */
    public function getVersion($request, $response, $args)
    {
        $language = $request->getHeaderLine(self::HEADER_LANGUAGE);
        $language = empty($language) || strpos($language, 'vi') === false ? '' : '_vi';
        
        $this->session->lzalanguage = $language;

        $data = $request->getQueryParams();
        $data['lang']=$language == '_vi'? 'vi':'en';
        if (!empty($data) && is_array($data))
        {
            $version = $this->doGetVersion($data);
        }
        else
        {
            $version = $this->doGetVersion();
        }

        return $response->withStatus(200)->withJson([
            'code' => 0,
            'message' => 'OK!',
            'data' => $version
        ]);
    }
}
