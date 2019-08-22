<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function home() {
        return '<center><h1>Welcome to Findly 1.0</h1><br><a href="/suggestions?q=Londo&latitude=43.70011&longitude=-79.4163">Run test here</a></center>';
    }

    public function suggestions(REQUEST $request){
        $name = $request->input('q');
        $lat = $request->input('latitude');
        $lon = $request->input('longitude');
        $result = [];

        if (($handle = fopen(storage_path('data/Cities.csv'), "r")) !== FALSE) {
            
            // Here we are getting values from the imported CSV file and parsing them in an Object
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if (strpos($data[0], $name) !== false) {
                    $object = (object)[];
                    $object->name = $data[0] .', '.$data[1];
                    $object->latitude = (float)$data[2];
                    $object->longitude = (float)$data[3];

                    // Once we detect that the latitude and longitude are provided from the client side, 
                    // We will use the Haversine Formula to calculate the distance and provide it as a score first 
                    if($lat && $lon){
                        $object->score = HomeController::haversineFormula($data[2], $data[3], $lat, $lon);
                    }
                    array_push($result, $object);
                }
            }
            fclose($handle);
            if($lat && $lon){
                $size = sizeof($result);
                $lowest = 0;
                $highest = $result[$size - 1]->score + 1;
                
                for($a = 0; $a < $size; $a++){
                    // Then we will sort the objects according to their scores (in ascending mode)
                    usort($result, function ($data1, $data2) {
                        return $data1->score > $data2->score;
                    });

                    // After that, we shall normalize the distance values
                    $result[$a]->score = HomeController::reverseNormalization($highest, $lowest, $result[$a]->score);
                }
            }
            return $result;
        }
    }

    // Haversine Formula (used to calculate distances between 2 points on the globe)
    public function haversineFormula($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo){
        // Converting from degrees to radians
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        // Calculating distance between latitudes and longitudes 
        $dLat = $latTo - $latFrom;
        $dLon = $lonTo - $lonFrom;

        $radius = 6371;

        $angle = 2 * asin(sqrt(pow(sin($dLat / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($dLon / 2), 2)));
        return $angle * $radius;
    }

    # Reverse Normalization (used to provide scores to cities ranging from 1 - 0 inclusive) 
    public function reverseNormalization($highest, $lowest, $value){
        return round(($highest - $value) / ($highest - $lowest), 1);         
    }
}
