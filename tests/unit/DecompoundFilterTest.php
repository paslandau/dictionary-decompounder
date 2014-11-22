<?php

use paslandau\DictionaryDecompounder\CompleteWord;
use paslandau\DictionaryDecompounder\Filter\DecompoundFilter;
use paslandau\DictionaryDecompounder\PartialWords;

class DecompoundFilterTest extends PHPUnit_Framework_TestCase {

    public function test_ShouldIncludeOrExcludeInvalidDecompoundings(){
        $word = new CompleteWord(
            "herrenschuhe",
            false,
            [
                new PartialWords(
                    new CompleteWord(
                        "herren",
                        true
                    ),
                    new CompleteWord(
                        "schuhe",
                        true
                    )
                ),
            ]
        );

        // evaluation function
        $evaluateResult = function($actual, $expected){
            foreach($expected as $ex) {
                $expectedString = implode(",", $ex);
                $found = false;
                foreach($actual as $key => $ac) {
                    $actualString = implode(",", $ac);
                    if($expectedString == $actualString){
                        $found = true;
                        unset($actual[$key]);
                        break;
                    }
                }
                $this->assertTrue($found, "Did not find expected result '$expectedString'");
            }
            $this->assertCount(0,$actual,"Still elements left. The filter returned too many results.");
        };

        $expected = [
            ["herren","schuhe"]
        ];
        $filter = new DecompoundFilter(true,true);
        $actual = $filter->filterList($word, true);

        $evaluateResult($actual,$expected);

        $expected = [
            ["herrenschuhe"],
            ["herren","schuhe"]
        ];
        $filter = new DecompoundFilter(true,false);
        $actual = $filter->filterList($word, true);

        $evaluateResult($actual,$expected);
    }
    
    public function test_ShouldOmitOrAddInterfix(){
        $word = new CompleteWord(
            "handwerkskunst",
            false,
            [
                new PartialWords(
                    new CompleteWord(
                        "handwerk",
                        true,
                        [],
                        "s"
                    ),
                    new CompleteWord(
                        "kunst",
                        true
                    )
                ),
            ]
        );

        $expected = ["handwerks","kunst"];
        $filter = new DecompoundFilter(true,true);
        $actual = $filter->filterBest($word);
        $this->assertEquals(implode(",",$expected),implode(",",$actual),"Expected result with interfix");

        $expected = ["handwerk","kunst"];
        $filter = new DecompoundFilter(false,true);
        $actual = $filter->filterBest($word);
        $this->assertEquals(implode(",",$expected),implode(",",$actual),"Expected result without interfix");
    }

    public function test_ShouldFindShortestDecompounding(){

        $word = new CompleteWord(
            "frottierhandtuch",
            false,
            [
            new PartialWords(
                new CompleteWord(
                    "frottierhand",
                    false,
                    [
                        new PartialWords(
                            new CompleteWord(
                                "frottier",
                                true
                            ),
                            new CompleteWord(
                                "hand",
                                true
                            )
                        ),
                    ]
                ),
                new CompleteWord(
                    "tuch",
                    true
                )
            ),
                new PartialWords(
                    new CompleteWord(
                        "frottier",
                        true
                    ),
                    new CompleteWord(
                        "handtuch",
                        true,
                        [
                            new PartialWords(
                                new CompleteWord(
                                    "hand",
                                    true
                                ),
                                new CompleteWord(
                                    "tuch",
                                    true
                                )
                            ),
                        ]
                    )
                ),

            ]
        );
        $filter = new DecompoundFilter(true,true,true);

        $expected = ["frottier","handtuch"];
        $actual = $filter->filterBest($word);
        $this->assertEquals(implode(",",$expected),implode(",",$actual),"Wrong result was filtered");
    }
}
 