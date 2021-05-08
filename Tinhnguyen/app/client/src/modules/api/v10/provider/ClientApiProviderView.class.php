<?php

namespace Lza\App\Client\Modules\Api\V10\Provider;


use Lza\App\Client\Modules\Api\V10\ClientApiV10View;

/**
 * Process Provider API
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientApiProviderView extends ClientApiV10View
{
    /**
     * @api GET /api/provider/all?address=
     *
     * @throws
     */
    public function getAll($request, $response, $args)
    {
        $language = $request->getHeaderLine(self::HEADER_LANGUAGE);
        $language = empty($language) || strpos($language, 'vi') === false ? '' : '_vi';
        
        $this->session->lzalanguage = $language;

        $data = $request->getQueryParams();
        $data['lang']=$language == '_vi'? 'vi':'en';
        if (!empty($data) && is_array($data))
        {
            $providers = $this->doGetProviders($data);
        }
        else
        {
            $providers = $this->doGetProviders();
        }

        return $response->withStatus(200)->withJson([
            'code' => 0,
            'message' => 'OK!',
            'data' => $providers
        ]);
    }

    /**
     * @api GET /api/provider/properties
     *
     * @throws
     */
    public function getProperties($request, $response)
    {
        $language = $request->getHeaderLine(self::HEADER_LANGUAGE);
        $language = empty($language) || strpos($language, 'vi') === false ? '' : '_vi';
        
        $this->session->lzalanguage = $language;

        $lang =$language == '_vi'? 'vi':'en';
        
        $providers = $this->doGetAttributeData($lang);

        return $response->withStatus(200)->withJson([
            'code' => 0,
            'message' => 'OK!',
            'data' => $providers
        ]);
    }

    /**
     * @api GET /api/provider/nearby
     *
     * @throws
     */
    public function getNearby($request, $response)
    {
        $language = $request->getHeaderLine(self::HEADER_LANGUAGE);
        $language = empty($language) || strpos($language, 'vi') === false ? '' : '_vi';
        
        $this->session->lzalanguage = $language;
        
        $data = $request->getQueryParams();
        $data['lang']=$language == '_vi'? 'vi':'en';
        
        $providers = $this->doGetNearby($data);

        return $response->withStatus(200)->withJson([
            'code' => 0,
            'message' => 'OK!',
            'data' => $providers
        ]);
    }
}
