<?php

namespace Lza\App\Client\Modules\Api\V20\Provider;


use Lza\App\Client\Modules\Api\V20\ClientApiV20Presenter;

/**
 * Handle Get All Provider action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientApiProviderGetPropertiesPresenter extends ClientApiV20Presenter
{
    public function doGetAttributeData($lang='vi'){
        $result = [
            'listProviderName' => $this->getProvidername($lang),
            'countries' => $this->getProviderCountry($lang),
            'cities' => $this->getProviderCity($lang),
            'medicalServices' => $this->getProviderService($lang),
            'medicalTypes' => $this->getProviderType($lang)
        ];
        return $result;
    }

    private function getProviderName($lang='vi'){
        $sql = $this->sql->pcvProviderName([
            'where' => " where lang = '".$lang."'",
            'column' => 'title'
        ]);
        $providers = $this->sql->query($sql, [], 'main_website');
        $result = [];
        foreach ($providers as $key=>$data){
            foreach($data as $key=>$value){
                if(!is_null($value)){
                    $result[] = $value;
                }
            }
        }
        return $result;
    }
    private function getProviderCountry($lang='vi'){
        $sql = $this->sql->pcvProviderName([
            'where' => " where lang = '".$lang."'",
            'column' => 'providerCountry'
        ]);
        $providers = $this->sql->query($sql, [], 'main_website');
        $result = [];
        foreach ($providers as $key=>$data){
            foreach($data as $key=>$value){
                if(!is_null($value)){
                    $result[] = $value;
                }
            }
        }
        return $result;
    }
    private function getProviderService($lang='vi'){
        $sql = $this->sql->pcvProviderName([
            'where' => " where lang = '".$lang."'",
            'column' => 'providerMedicalServices'
        ]);
        $providers = $this->sql->query($sql, [], 'main_website');
        $result = [];
        foreach ($providers as $key=>$data){
            foreach($data as $key=>$value){
                if(!is_null($value)){
                    $result[] = $value;
                }
            }
        }
        return $result;
    }
    private function getProviderType($lang='vi'){
        $sql = $this->sql->pcvProviderName([
            'where' => " where lang = '".$lang."'",
            'column' => 'MedicalType'
        ]);
        $providers = $this->sql->query($sql, [], 'main_website');
        $result = [];
        foreach ($providers as $key=>$data){
            foreach($data as $key=>$value){
                if(!is_null($value)){
                    $result[] = $value;
                }
            }
        }
        return $result;
    }
    private function getProviderCity($lang='vi'){
        $sql = $this->sql->pcvProviderName([
            'where' => " where lang = '".$lang."'",
            'column' => 'providerCountry'
        ]);
        $providers = $this->sql->query($sql, [], 'main_website');
        $result = [];
        foreach ($providers as $key=>$data){
            foreach($data as $key=>$value){
                if(!is_null($value)){
                    $result[$value] = $this->getCity($value, $lang);
                }
            }
        }
        return $result;
    }
    private function getCity($country, $lang='vi'){
        $sql = $this->sql->pcvProviderName([
            'where' => " where lang = '".$lang."' and providerCountry='".$country."'",
            'column' => 'providerCity'
        ]);
        $providers = $this->sql->query($sql, [], 'main_website');
        $result = [];
        foreach ($providers as $key=>$data){
            foreach($data as $key=>$value){
                if(!is_null($value)){
                    $result[] = $value;
                }
            }
        }
        return $result;
    }
}
