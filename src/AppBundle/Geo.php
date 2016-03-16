<?php
/**
 * Created by PhpStorm.
 * User: ben
 * Date: 2016/2/25
 * Time: 22:35
 */

namespace AppBundle;


class Geo
{
/*GeoCoding*/
    public function calculation($products)
    {
        $diff["distances"][0]="";
        $diff["time"][0]="";
        $diff["speed"][0]="";
        for ($i = 1; $i < count($products); $i++) {
            $distance = self::GPSToDistance($products[$i - 1]->getLat(), $products[$i - 1]->getLng(), $products[$i]->getLat(), $products[$i]->getLng());
            $time = date_diff($products[$i - 1]->getTimestamp(), $products[$i]->getTimestamp());
            $diff["speed"][$i] = intdiv($distance, ($time->days * 86400 + $time->h * 3600 + $time->i * 60 + $time->s));
            $diff["distances"][$i] = $distance;
            $diff["time"][$i] = $time->format('%H:%i:%s');
        }
        return $diff;
    }

    /*
     * http://stackoverflow.com/questions/10053358/measuring-the-distance-between-two-coordinates-in-php
     * Calculates the great-circle distance between two points, with the Vincenty formula.
     *
     * Distance return as the same unit as $earthRadius.
     * $earthRadius Option:
     *      6371000 meters
     *      3959 miles
     */
    public function GPSToDistance (
        $latitudeFrom , $longitudeFrom , $latitudeTo , $longitudeTo , $earthRadius =  6371000 )
    {
        // Convert from degrees to radians
        $latFrom = deg2rad ( $latitudeFrom );
        $lonFrom = deg2rad ( $longitudeFrom );
        $latTo = deg2rad ( $latitudeTo );
        $lonTo = deg2rad ( $longitudeTo );

        $lonDelta = $lonTo - $lonFrom;
        $a = pow(cos($latTo) * sin($lonDelta), 2) +
            pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
        $b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);

        $angle = atan2(sqrt($a), $b);
        return $angle * $earthRadius;
    }

/*GeoLookup*/
    public function reverseGeocoding($LatLng)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' . $LatLng . '&key='.$this->getParameter('google_api_key').'AIzaSyCC5fPfRRc_5zonzfsqwAZ0ypRj73u7ghw&result_type=street_address');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json')); // Assuming you're requesting JSON
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response);
        if ($data->status == "OK") {
            return $data->results[0]->formatted_address;
        } else {
            return "NA";
        }
    }
}