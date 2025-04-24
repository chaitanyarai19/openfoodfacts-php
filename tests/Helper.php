<?php

namespace OpenFoodFactsTests;

use OpenFoodFacts\Api;
use OpenFoodFacts\Document;

class Helper
{
    public static function getProductWithCache(Api $api, string $barCode): Document
    {
        return $GLOBALS['cache-'.$api->getCurrentApi()][$barCode] ?? $GLOBALS['cache-'.$api->getCurrentApi()][$barCode] = $api->getProduct($barCode);
    }
}

//Function for add 2 numbers
/**
 * add two numbers and return the result.
 * 
 * @param int $a First number
 * @param int $b Second number
 * @return int Sum of the two numbers
 *
 */
function add($a, $b) {
    return $a + $b;
}
