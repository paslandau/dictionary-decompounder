<?php
use paslandau\DictionaryDecompounder\Normalizer\Normalizer;

/**
 * Created by PhpStorm.
 * User: Hirnhamster
 * Date: 21.11.2014
 * Time: 22:51
 */

class NormalizerTest extends PHPUnit_Framework_TestCase {

    public function test_ShouldNormalize(){

        $tests = [
            "Chaos" => ["chaos", "lowercase"],
            " Chaos " => ["chaos", "trim"],
            "Straßenübergang" => ["straßenübergang", "Umlauts"],
            "" => ["", "empty"],
        ];

        $normalizer = new Normalizer();
        foreach($tests as $word => $result){
            $expected = $result[0];
            $msg = $result[1];
            $actual = $normalizer->normalize($word);

            $this->assertEquals($expected,$actual, $msg);
        }

    }
}
 