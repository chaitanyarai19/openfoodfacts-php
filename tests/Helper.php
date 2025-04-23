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

//Function for addition for 2 numbers
/**
* This function takes two numbers as input and returns their sum.
 * @param int $a first number
 * @param int $b second number
 * @return int sum of the two numbers
 */

function add($a, $b) {
    return $a + $b;
}
