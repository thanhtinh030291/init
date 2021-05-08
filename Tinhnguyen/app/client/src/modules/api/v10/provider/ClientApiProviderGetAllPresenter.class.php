<?php

namespace Lza\App\Client\Modules\Api\V10\Provider;


use Lza\App\Client\Modules\Api\V10\ClientApiV10Presenter;

/**
 * Handle Get All Provider action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientApiProviderGetAllPresenter extends ClientApiV10Presenter
{
    private $fields = [
        'name' => 'title',
        'address' => 'providerAddress',
        'country' => 'providerCountry',
        'city' => 'providerCity',
        'district' => 'providerDistrict',
        'specialty' => 'providerMedicalServices',
        'type' => 'MedicalType'
    ];

    /**
     * Validate inputs and do Get Providers request
     *
     * @throws
     */
    public function doGetProviders($search = null)
    {
        if (empty($search))
        {
            $condition = '';
            $params = [];
        }
        else
        {
            $conditions = [];
            $params = [];
            if((count($search) == 1 && !empty($search['lang']))){
                $condition = "where lang = '".$search['lang']."'";
            } else {
                foreach ($this->fields as $key => $value)
                {
                    if (!empty($search[$key]) && is_string($search[$key]))
                    {
                        if($key == 'name'){
                            $conditions[] = "({$value} like ? or providerNickname1 like ?)";
                            $params[] = "%{$search[$key]}%";
                            $params[] = "%{$search[$key]}%";
                        } else {
                            $conditions[] = "{$value} like ?";
                            $params[] = "%{$search[$key]}%";
                        }
                    }
                }
                
                $condition = 'where ' . implode(' and ', $conditions);
                $condition .= " and lang = '".$search['lang']."'";
            }
        }
        
        $sql = $this->sql->pcvProvider([
            'where' => $condition
        ]);
        $providers = $this->sql->query($sql, $params, 'main_website');

        if (count($providers) === 0)
        {
            return false;
        }
        return $providers;
    }
}
