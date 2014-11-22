<?php

use paslandau\DictionaryDecompounder\Decompounder;
use paslandau\DictionaryDecompounder\Dictionary\ArrayDictionary;
use paslandau\DictionaryDecompounder\Filter\DecompoundFilter;
use paslandau\DictionaryDecompounder\Interfix\Interfixer;
use paslandau\IOUtility\IOUtil;

class GermanDecompounderTest extends PHPUnit_Framework_TestCase {

    public function test_ShouldRecognizeWords(){
        mb_internal_encoding("utf-8");

        $pathToDict = __DIR__ . "/../../resources/ger-dict.txt";
        $dictionary = new ArrayDictionary();
        $dictionary->loadFromFile($pathToDict);
        $interfixes = array("e", "s", "es", "n", "en", "er", "ens"); // These interfixes make sense for german words
        foreach($interfixes as $interfix){
            $interfixes[] = $interfix."-";
        }
        $interfixes[] = "-";
        $interfixer = new Interfixer($interfixes);
        $minLength = 2;
        $decompounder = new Decompounder($dictionary, $interfixer, $minLength);
        $filter = new DecompoundFilter();

        $lines = file(__DIR__."/../resources/test-de.txt");
        $sep = ", ";
        $fails = [];
        $success = [];
        foreach($lines as $line){
            $line = trim($line);
            $word = str_replace($sep,"",$line);
            $res = $decompounder->decompoundWord($word);
            $arr = $filter->filterBest($res);
            $actual = implode($sep, $arr);
            $expected = $dictionary->getNormalizer()->normalize($line);
            if($actual !== $expected) {
                $fails[] = $actual ." != ". $expected ." for ". $word;
            }
        }
        $this->assertCount(0,$fails,count($fails)." total fails: ". implode("\n", $fails));
    }
}
 