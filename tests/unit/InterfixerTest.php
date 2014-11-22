<?php
use paslandau\DictionaryDecompounder\Interfix\Interfixer;

/**
 * Created by PhpStorm.
 * User: Hirnhamster
 * Date: 21.11.2014
 * Time: 22:42
 */

class InterfixerTest extends PHPUnit_Framework_TestCase {

    public function test_ShouldReturnCorrectInterfix(){

        $tests = [
            "Straßen" => [
                ["Straß", "en"]
            ],
            "Waldes" => [
                ["Walde", "s"],
                ["Wald", "es"],
            ],
            "Glücks-" => [
                ["Glücks", "-"],
                ["Glück", "s-"],
            ]
        ];
        $interfixes = ["-", "s", "s-", "e", "es", "en"];
        $interfixer = new Interfixer($interfixes);

        foreach($tests as $word => $expected){
            $actual = $interfixer->getInterfixedParts($word);
            sort($actual);
            sort($expected);
            $this->assertEquals(serialize($actual),serialize($expected));
        }
    }
}
 