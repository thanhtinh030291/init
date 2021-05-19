<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Provider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;


class ProviderController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        
    }
    /**
     * Get a list of all of a provider
     *
     * @return \Illuminate\Http\Response
     */
    public function all(Request $request)
    {
        $lang = App::currentLocale();
        $data = Provider::select(
            "code as id",
            "name as title",
            "phone as providerPhone",
            "email as providerEmail",
            'website as providerWebsite',
            'address as providerAddress',
            'city as providerCity',
            'district as providerDistrict',
            'country as providerCountry',
            'latitude as providerLatitude',
            'longitude as providerLongitude',
            'day_from_1 as providerFromDay1',
            'day_to_1 as providerToDay1',
            'hour_open_1 as providerOpeningHours1',
            'hour_close_1 as providerClosingHours1',
            'day_from_2 as providerFromDay2',
            'day_to_2 as providerToDay2',
            'hour_open_2 as providerOpeningHours2',
            'hour_close_2 as providerClosingHours2',
            'emergency_services as providerEmergencyServices',
            'emergency_phone as providerEmergencyPhone',
            'direct_billing as providerDirectBilling',
            'medical_type as MedicalType',
            'price_to as providerAmount',
            'medical_services as providerMedicalServices',
            'price_from as providersPriceFrom',
            'price_to as providersPriceTo',
            'lang'
        );
        
        if($request->name != null){
            $data = $data->where('name','like',"%".$request->name."%");
        }
        
        if($request->country != null){
            $data = $data->where('country','like', "%".$request->country."%");
        }

        if($request->city != null){
            $data = $data->where('country','like', "%".$request->city."%");
        }

        if($request->specialty != null){
            $data = $data->where('medical_services','like', "%".$request->medical_services."%"); 
        }

        if($request->type != null){
            $data = $data->where('medical_type','like', "%".$request->medical_services."%"); 
        }
        $data = $data->where('lang', $lang)->get();
        return $this->sendResponse($data, 'OK', 0); 
    }

    /**
     * Get a list of all of a provider
     *
     * @return \Illuminate\Http\Response
     */
    public function properties()
    {
        $lang = App::currentLocale();
        $listProviderName = Provider::where('lang', $lang)->whereNotNull('name')->distinct()->pluck('name');
        $countries = Provider::where('lang', $lang)->whereNotNull('country')->distinct()->pluck('country');
        $cities = Provider::select('city','country')->where('lang', $lang)->whereNotNull('city')->distinct()->get()->toArray();
        $cities = collect($cities)->groupBy('country')->toArray();
        $ct = [];
        foreach ($cities as $key => $value) {
            $ct[$key] = array_column($value,'city');
        }
        $medical_services = Provider::where('lang', $lang)->whereNotNull('medical_services')->distinct()->pluck('medical_services');
        $medical_type = Provider::where('lang', $lang)->whereNotNull('medical_type')->distinct()->pluck('medical_type');
        $data =
        [
            'listProviderName' => $listProviderName,
            'countries' => $countries,
            'cities' => $ct,
            'medicalServices' => $medical_services,
            'medicalTypes' => 	$medical_type
        ];
        return $this->sendResponse($data, 'OK', 0); 
    }



    public function nearby(Request $request){
        $search = [
            'longitude' => $request->longitude ? $request->longitude : "106.685017",
            'latitude' => $request->latitude ? $request->latitude : "10.755557",
            'distance' => $request->distance ? $request->distance : 10,
        ];
        
        $lang = App::currentLocale();
        
        $data = Provider::select("code as id",
        "name as title",
        "phone as providerPhone",
        "email as providerEmail",
        'website as providerWebsite',
        'address as providerAddress',
        'city as providerCity',
        'district as providerDistrict',
        'country as providerCountry',
        'latitude as providerLatitude',
        'longitude as providerLongitude',
        'day_from_1 as providerFromDay1',
        'day_to_1 as providerToDay1',
        'hour_open_1 as providerOpeningHours1',
        'hour_close_1 as providerClosingHours1',
        'day_from_2 as providerFromDay2',
        'day_to_2 as providerToDay2',
        'hour_open_2 as providerOpeningHours2',
        'hour_close_2 as providerClosingHours2',
        'emergency_services as providerEmergencyServices',
        'emergency_phone as providerEmergencyPhone',
        'direct_billing as providerDirectBilling',
        'medical_type as MedicalType',
        'price_to as providerAmount',
        'medical_services as providerMedicalServices',
        'price_from as providersPriceFrom',
        'price_to as providersPriceTo',
        'lang', DB::raw("6371 * acos(cos(radians(" . $search['latitude'] . "))
                                * cos(radians(latitude)) * cos(radians(longitude) - radians(" . $search['longitude'] . "))
                                + sin(radians(" .$search['latitude']. ")) * sin(radians(latitude))) AS distance"));
        $data =  $data->having('distance', '<', $search['distance']);
        $data = $data->orderBy('distance', 'asc');
        $data = $data->where('lang', $lang)->get();
        return $this->sendResponse($data, 'OK', 0);
    }
}
