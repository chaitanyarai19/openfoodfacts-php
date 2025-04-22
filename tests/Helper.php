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


//add 2 numbers
/*
 * This function adds two numbers together.
 * @param int $a
 * @param int $b
 * @return int
 */
function add($a, $b) {
    return $a + $b;
}