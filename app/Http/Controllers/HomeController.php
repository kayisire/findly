<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function home() {
        return "Welcome to FindCity 1.0";
    }

    public function suggestions(REQUEST $request){
        $name = $request->input('q');
        $lat = $request->input('latitude');
        $lon = $request->input('longitude');
        $result = [];

        if (($handle = fopen(storage_path('data/Cities.csv'), "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if (strpos($data[0], $name) !== false) {
                    $object = (object)[];
                    $object->name = $data[0] .', '.$data[1];
                    $object->latitude = (float)$data[2];
                    $object->longitude = (float)$data[3];
                    if($lat && $lon){
                        $object->score = HomeController::haversineFormula($data[2], $data[3], $lat, $lon);
                        // Sorting the class objects according to their scores (length)
                        usort($result, function ($object1, $object2) { 
                            return $object1->score > $object2->score;
                        });
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
                    $result[$a]->score = HomeController::reverseNormalization($highest, $lowest, $result[$a]->score);
                }
            }
            return $result;
        }
    }

    // Haversine Formula (used to calculate distances between 2 points on the globe)
    public function haversineFormula($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371){
        // convert from degrees to radians
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $angle * $earthRadius;
    }

    # Reverse Normalization (used to provide scores to cities ranging from 1 - 0 inclusive) 
    public function reverseNormalization($highest, $lowest, $value){
        return round(($highest - $value) / ($highest - $lowest), 1);         
    }
}
