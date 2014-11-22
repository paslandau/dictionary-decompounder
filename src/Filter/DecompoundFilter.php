<?php

namespace paslandau\DictionaryDecompounder\Filter;


use paslandau\DictionaryDecompounder\CompleteWord;
use paslandau\DictionaryDecompounder\PartialWords;

class DecompoundFilter implements DecompoundFilterInterface
{

    /**
     * @var bool
     */
    private $onlyValid;

    /**
     * @var bool
     */
    private $addInterfix;

    /**
     * @var bool
     */
    private $preferLessParts;

    /**
     * @param bool $addInterfix [optional]. Default: false.
     * @param bool $onlyValid [optional]. Default: true.
     * @param bool $preferLessParts [optional]. Default: true.
     */
    function __construct($addInterfix = null, $onlyValid = null, $preferLessParts = null)
    {
        if($addInterfix === null) {
            $addInterfix = false;
        }
        $this->addInterfix = $addInterfix;
        if($onlyValid === null) {
            $onlyValid = true;
        }
        $this->onlyValid = $onlyValid;

        if($preferLessParts === null) {
            $preferLessParts = true;
        }
        $this->preferLessParts = $preferLessParts;
    }


    /**
     * @param CompleteWord $word
     * @param bool $includeOriginal [optional]. Default: false.
     * @return string[][]
     */
    public function filterList(CompleteWord $word, $includeOriginal = null){
        $result = [];

        $original = $this->getOriginalWord($word, $includeOriginal);
        if($original !== null){
            $result[$original] = [$original];
        }

        foreach($word->getPartialWordsList() as $partialWords){
            $partialResult = $this->getPartialWordsDecompounds($partialWords);
            $result = array_merge($result, $partialResult);
        }
        return $result;
    }

    /**
     * @param CompleteWord $word
     * @param bool $includeOriginal [optional]. Default: false.
     * @return null|string
     */
    private function getOriginalWord(CompleteWord $word, $includeOriginal = null){
        if($includeOriginal === null) {
            $includeOriginal = false;
        }
        if($includeOriginal) {
            if (!$this->onlyValid || $word->isInDictionary()) {
                $decompounding = $word->getWord();
                if ($this->addInterfix) {
                    $decompounding .= $word->getInterfix();
                }
                return $decompounding;
            }
        }
        return null;
    }

    /**
     * @param PartialWords $partialWords
     * @return string[][]
     */
    private function getPartialWordsDecompounds(PartialWords $partialWords){
        $results = [];
        $lefts = $this->filterList($partialWords->getLeftPartialWord(), true);
        $rights = $this->filterList($partialWords->getRightPartialWord(), true);
        foreach($lefts as $left){
            foreach($rights as $right){
                $key = implode(",", $left).",".implode(",", $right);
                $results[$key] = array_merge($left, $right);
            }
        }
        return $results;
    }

    /**
     * @param CompleteWord $word
     * @param bool $includeOriginal [optional]. Default: false.
     * @return string[]
     */
    public function filterBest(CompleteWord $word, $includeOriginal = null){
        $all = $this->filterList($word, $includeOriginal);
        if(count($all)){
            if($this->preferLessParts) { // sort ascending by number of partial words
                usort($all, function ($a, $b) {
                    return count($a) - count($b);
                });
            }
            return array_shift($all);
        }
        return [];
    }
} 