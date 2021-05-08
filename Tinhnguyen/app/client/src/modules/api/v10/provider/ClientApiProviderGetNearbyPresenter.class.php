<?php

namespace Lza\App\Client\Modules\Api\V10\Provider;


use Lza\App\Client\Modules\Api\V10\ClientApiV10Presenter;
use Geokit\BoundingBox;
use Geokit\Position;
use Geokit\Distance;


/**
 * Handle Get All Provider action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientApiProviderGetNearbyPresenter extends ClientApiV10Presenter
{
    private $fields = [
        'name' => 'title',
        'address' => 'providerAddress',
        'country' => 'providerCountry',
        'city' => 'providerCity',
        'district' => 'providerDistrict',
        'specialities' => 'providerMedicalServices',
        'type' => 'MedicalType'
    ];

    /**
     * Validate inputs and do Get Nearby request
     *
     * @throws
     */
    public function doGetNearby($search = null)
    {
        $southWest = Position::fromXY($search['longititude'], $search['latitude']);
        $northEast = Position::fromXY($search['longititude'], $search['latitude']);
        $boundingBox = BoundingBox::fromCornerPositions($southWest, $northEast);

        $expandedBoundingBox = $boundingBox->expand(
            Distance::fromString($search['distance'])
        );

        $southWestPosition = $expandedBoundingBox->southWest();
        $northEastPosition = $expandedBoundingBox->northEast();

        // dd($expandedBoundingBox, $southWestPosition, $northEastPosition);

        $minLongititude = $southWestPosition->x();
        $minLatitude = $southWestPosition->y();

        $maxLatitude = $northEastPosition->y();
        $maxLongititude = $northEastPosition->x();

        // dd($boundingBox, $expandedBoundingBox, $southWestPosition, $northEastPosition, $minLongititude, $maxLongititude, $minLatitude, $maxLatitude);

        if (empty($search))
        {
            $condition = '';
            $param = [];
        }
        else
        {
            $conditions = [];
            $params = [];
            foreach ($this->fields as $key => $value)
            {
                if (!empty($search[$key]) && is_string($search[$key]))
                {
                    $conditions[] = "{$value} like ?";
                    $params[] = "%{$search[$key]}%";
                }
            }

            $condition = 'where (' . implode(' or ', $conditions);
            $condition .= ") and lang = '".$search['lang']."'";
        }
        $condition = "where (providerLatitude between $minLatitude and $maxLatitude) and (providerLongitude between $minLongititude and $maxLongititude)";
        $condition .= " and lang = '".$search['lang']."'";
        
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
