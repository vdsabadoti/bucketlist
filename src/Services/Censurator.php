<?php

namespace App\Services;

class Censurator
{
    public function purify(string $text) : string {
        $censured = ['kill','steal','fuck'];
        $array = explode(' ', $text);
        foreach ($array as $key => $word) {
            $word = str_ireplace( array( '\'', '"' , ';', '<', '>' ), '', $word);
            foreach ($censured as $censure) {
                if (str_contains(strtolower($word), strtolower($censure))) {
                    $array[$key] = '*';
                    break;
                }
            }
        }
        return implode(' ', $array);
    }
}