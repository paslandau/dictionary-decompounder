<?php

use paslandau\DictionaryDecompounder\CompleteWord;
use paslandau\DictionaryDecompounder\Decompounder;
use paslandau\DictionaryDecompounder\Dictionary\ArrayDictionary;
use paslandau\DictionaryDecompounder\Dictionary\DictionaryInterface;
use paslandau\DictionaryDecompounder\Interfix\Interfixer;
use paslandau\DictionaryDecompounder\Interfix\InterfixerInterface;
use paslandau\DictionaryDecompounder\Normalizer\Normalizer;
use paslandau\DictionaryDecompounder\PartialWords;

class DecompounderTest extends PHPUnit_Framework_TestCase
{

    public function test_ShouldNotAcceptNull()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        /** @var DictionaryInterface $dictMock */
        $dictMock = $this->getMock(DictionaryInterface::class);
        /** @var InterfixerInterface $interMock */
        $interMock = $this->getMock(InterfixerInterface::class);
        $decompounder = new Decompounder($dictMock, $interMock);
        $decompounder->decompoundWord(null);
    }

    public function test_ShouldRespondWithPredefinedDecompoundingResult()
    {

        $decompounder = $this->getDecompounder();

        $word = "straßenschäden";
        $predefinedResult = new CompleteWord(
            $word,
            true
        );

        // check for predefined result -- should exist yet
        $res = $decompounder->getPredefinedDecompoundingResult($word);
        $this->assertEquals(null, $res, "Found predefined result although it shouldn't exist");

        // add the result
        $decompounder->addPredefinedDecompoundingResult($word, $predefinedResult);

        // check again
        $res = $decompounder->getPredefinedDecompoundingResult($word);
        $this->assertEquals($predefinedResult, $res, "Didn't find predefined result although it should exist");

        //we should also get it on a "normal" decompound operation
        $res = $decompounder->decompoundWord($word);
        $this->assertEquals($predefinedResult, $res, "Didn't get predefined result while normal decompounding although it should exist");
    }

    public function test_DecompoundingShouldWorkIfWordsAreInDict()
    {
        $dict = [
            "Foo",
            "Bar",
            "Herren",
            "Schuhe",
            "Herrenschuhe"
        ];

        $word = "herrenschuhe";

        $decompounder = $this->getDecompounder($dict);
        $res = $decompounder->decompoundWord($word);
        $this->assertTrue($res->isInDictionary(), "The full word '$word' was not recognized in the dict");

        $allParts = $res->getPartialWordsList();
        $parts = $allParts[0];
        $leftPart = $parts->getLeftPartialWord();
        $this->assertTrue($leftPart->isInDictionary(), "The left part-word '$leftPart' was not recognized in the dict");
        $this->assertEquals("herren", $leftPart->getWord(), "The left part-word '$leftPart' was wrong");
        $this->assertEquals(0, count($leftPart->getPartialWordsList()), "The left part sohuld habe no futher decompounding parts");

        $rightPart = $parts->getRightPartialWord();
        $this->assertTrue($rightPart->isInDictionary(), "The right part-word '$rightPart' was not recognized in the dict");
        $this->assertEquals("schuhe", $rightPart->getWord(), "The right part-word '$rightPart' was wrong");
        $this->assertEquals(0, count($rightPart->getPartialWordsList()), "The right part sohuld habe no futher decompounding parts");
    }

    public function test_DecompoundingShouldWorkOnGermanUmlauts()
    {
        $dict = [
            "Straße",
            "Schäden",
        ];

        $interfixes = [
            "n",
        ];

        $word = "straßenschäden";

        $decompounder = $this->getDecompounder($dict, $interfixes);
        $res = $decompounder->decompoundWord($word);
        $this->assertFalse($res->isInDictionary(), "The full word '$word' was recognized in the dict although it's not in there");

        $allParts = $res->getPartialWordsList();
        $parts = $allParts[0];
        $leftPart = $parts->getLeftPartialWord();
        $this->assertTrue($leftPart->isInDictionary(), "The left part-word '$leftPart' was not recognized in the dict");
        $this->assertEquals("straße", $leftPart->getWord(), "The left part-word '$leftPart' was wrong");
        $this->assertEquals("n", $leftPart->getInterfix(), "The interfix of the left part-word '$leftPart' was wrong");
        $this->assertEquals(0, count($leftPart->getPartialWordsList()), "The left part sohuld habe no futher decompounding parts");

        $rightPart = $parts->getRightPartialWord();
        $this->assertTrue($rightPart->isInDictionary(), "The right part-word '$rightPart' was not recognized in the dict");
        $this->assertEquals("schäden", $rightPart->getWord(), "The right part-word '$rightPart' was wrong");
        $this->assertEquals("", $rightPart->getInterfix(), "The interfix of the right part-word '$rightPart' should be empty");
        $this->assertEquals(0, count($rightPart->getPartialWordsList()), "The right part sohuld habe no futher decompounding parts");
    }

    public function test_ShoouldReturnMultipleVariants()
    {
        $dict = [
            "Techniker",
            "Technik",
            "Krankenkasse",
            "Kasse"
        ];

        $interfixes = [
            "er-",
            "-"
        ];

        $word = "techniker-krankenkasse";

        $decompounder = $this->getDecompounder($dict, $interfixes);
        $res = $decompounder->decompoundWord($word);
        $expectedDecompoundings = [
            "techniker-kranken" => [
                "left" => ["techniker-kranken", "", false],
                "right" => ["kasse", "", true],
            ],
            "technik" => [
                "left" => ["technik", "er-", true],
                "right" => ["krankenkasse", "", true,
                    "children" => [
                        "kranken" => [
                            "left" => ["kranken", "", false],
                            "right" => ["kasse", "", true],
                            "valid" => false,
                        ],
                    ]
                ],
            ],
            "techniker" => [
                "left" => ["techniker", "-", true],
                "right" => ["krankenkasse", "", true,
                    "children" => [
                        "kranken" => [
                            "left" => ["kranken", "", false],
                            "right" => ["kasse", "", true],
                            "valid" => false,
                        ],
                    ]
                ],
            ],
        ];

        $allParts = $res->getPartialWordsList();

        foreach ($allParts as $key => $parts) {
            $this->evaluateParts($parts, $expectedDecompoundings);
        }
    }

    private function getDecompounder($dictionaryWords = [], $interfixes = [], $minLength = 2)
    {

        $normalizer = new Normalizer();
        $dictionary = new ArrayDictionary($normalizer);
        $dictionary->addWords($dictionaryWords);

        $interfixer = new Interfixer($interfixes);

        $decompounder = new Decompounder($dictionary, $interfixer, $minLength);

        return $decompounder;
    }

    /**
     * @param PartialWords $parts
     * @param $expectedDecompoundings
     */
    private function evaluateParts($parts, $expectedDecompoundings)
    {
        $left = $parts->getLeftPartialWord();
        $this->assertArrayHasKey($left->getWord(), $expectedDecompoundings, "Left part '$left' not found in expected decompoundings");
        $expectedParts = $expectedDecompoundings[$left->getWord()];

        $compare = [
            [$parts->getLeftPartialWord(), $expectedParts["left"]],
            [$parts->getRightPartialWord(), $expectedParts["right"]]
        ];

        foreach ($compare as $arr) {
            /** @var CompleteWord $actual */
            $actual = $arr[0];
            $expected = $arr[1];
            $this->assertEquals($expected[0], $actual->getWord(), "Word didn't match");
            $this->assertEquals($expected[1], $actual->getInterfix(), "Interfix didn't match");
            $this->assertEquals($expected[2], $actual->isInDictionary(), "IsInDictionary didn't match");
            $this->assertEquals($expected[2], $actual->isInDictionary(), "IsInDictionary didn't match");
            if (array_key_exists("children", $expected)) {
                $allParts = $actual->getPartialWordsList();
                foreach ($allParts as $innerParts) {
                    $this->evaluateParts($innerParts, $expected["children"]);
                }
            }
        }
    }
}
 