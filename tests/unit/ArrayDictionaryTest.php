<?php

use paslandau\DictionaryDecompounder\Dictionary\ArrayDictionary;
use paslandau\DictionaryDecompounder\Normalizer\Normalizer;

class ArrayDictionaryTest extends PHPUnit_Framework_TestCase {

    public function test_ShouldAddAndRemoveWords(){

        $normalizer = new Normalizer();
        $dict = new ArrayDictionary($normalizer);
        $dict->add("Straße");
        $this->assertTrue($dict->exists("Straße"), "Exists doesn't find word");
        $this->assertTrue($dict->exists("straße"), "Exists doesn't find word in different form (normalization issue)");
        $this->assertFalse($dict->exists("Test"), "Exists does find not existing word");
        $dict->remove("straße");
        $this->assertFalse($dict->exists("Straße"), "Exists does find word although it was deleted previously");
        $words = ["Foo","Bar", "Baz"];
        $dict->addWords($words);
        foreach ($words as $word) {
            $this->assertTrue($dict->exists($word),"Didn't find word although it should exist");
        }
    }
}
 