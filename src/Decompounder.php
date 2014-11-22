<?php
namespace paslandau\DictionaryDecompounder;

use paslandau\DictionaryDecompounder\Dictionary\DictionaryInterface;
use paslandau\DictionaryDecompounder\Interfix\InterfixerInterface;

class Decompounder
{
    /**
     * Map of [string ($word) => DecompoundingPart ($decompoundingResult)] that denotes how a certain word has to
     * be decompounded because it won't be caught right by the decompounding algorithm.
     * E.g. "Eisenbahn" => new DecompoundingPart("Eisenbahn", true);
     *        (because otherwise we would end up with "Eisen" and "Bahn".
     * @var DecompoundingPart[]
     */
    private $predefinedDecompoundingResults;

    /**
     * Minimum length of a word
     * @var integer
     */
    private $minWordLength;

    /**
     * @var InterfixerInterface
     */
    private $interfixer;

    /**
     * @var DictionaryInterface
     */
    private $dictionary;

    /**
     * @param DictionaryInterface $dictionary
     * @param InterfixerInterface $interfixer
     * @param int $minWordLength [optional]. Default: 2.
     */
    public function __construct(DictionaryInterface $dictionary, InterfixerInterface $interfixer, $minWordLength = null)
    {
        $this->dictionary = $dictionary;
        $this->interfixer = $interfixer;
        $this->predefinedDecompoundingResults = [];

        if ($minWordLength === null) {
            $minWordLength = 2;
        }
        $this->minWordLength = $minWordLength;
    }

    /**
     * Get all decompound possibilities for $word
     * @param string $word
     * @return DecompoundingPart
     */
    public function decompoundWord($word)
    {
        if ($word === null) {
            throw new \InvalidArgumentException("'word' must not be null");
        }
        $word = $this->dictionary->getNormalizer()->normalize($word);
        if ($word === "") {
            throw new \InvalidArgumentException("'word' must not be empty");
        }

        $decompoundingPart = $this->split($word);

        return $decompoundingPart[0];
    }

    /**
     * Adds a decompunding exception.
     * @param $word
     * @param DecompoundingPart $decompoundingPart
     */
    public function addPredefinedDecompoundingResult($word, DecompoundingPart $decompoundingPart)
    {
        $word = $this->dictionary->getNormalizer()->normalize($word);
        $this->predefinedDecompoundingResults[$word] = $decompoundingPart;
    }

    /**
     * @param $word
     * @return null|DecompoundingPart
     */
    public function getPredefinedDecompoundingResult($word)
    {
        $word = $this->dictionary->getNormalizer()->normalize($word);
        if (array_key_exists($word, $this->predefinedDecompoundingResults)) {
            return $this->predefinedDecompoundingResults[$word];
        }
        return null;
    }

    /**
     * @param string $word
     * @return bool
     */
    public function isValidWord($word)
    {
        return mb_strlen($word) >= $this->minWordLength && $this->dictionary->exists($word);
    }

//    /**
//     * @param string $word
//     * @return DecompoundingPart
//     */
//    private function split($word)
//    {
//        if ($word === null || $word === "") {
//            return null;
//        }
//
//        // check if the word is an exception
//        if ( ($res = $this->getPredefinedDecompoundingResult($word)) !== null){
//            return $res;
//        }
//
//        $decompoundingPart = new DecompoundingPart($word, $this->isValidWord($word));
//
//        $charCount = strlen($word);
//        $resultSet = [];
//        for ($i = $charCount - $this->minWordLength; $i >= 0; $i--) {
//            $leftPart = mb_substr($word, 0, $i);
//            $rightPart = mb_substr($word, $i);
//
//            $interfixedParts = $this->interfixer->getInterfixedParts($rightPart);
//
//            $checks = $interfixedParts;
//            $checks[] = array($rightPart, ""); // add variant without interfix by default
//
//            foreach ($checks as $checkArr) {
//                $check = $checkArr[0];
//                $interfix = $checkArr[1];
//                $rightDecompounding = null;
//                if ($this->isValidWord($check)) {
//                    if ($i !== 0) { // 0 would mean we're checking the original $word
//                        $rightDecompounding = $this->split($check);
//                    } else {
//                        $rightDecompounding = new DecompoundingPart($check, true);
//                    }
//                }
//                // found dictionary match
//                if ($rightDecompounding !== null) {
//                    $rightDecompounding->setInterfix($interfix);
//                    if ($i !== 0) { // 0 would mean we're checking the original $word
//                        $leftDecompounding = $this->split($leftPart);
//                        $resultSet[] = array($leftDecompounding, $rightDecompounding);
//                    } else {
//                        // override orginal settings with what we just found out about the full word
//                        $decompoundingPart->setInterfix($rightDecompounding->getInterfix());
//                        $decompoundingPart->setWord($rightDecompounding->getWord());
//                        $decompoundingPart->setInDictionary($rightDecompounding->isInDictionary());
//
////                        $decompoundingPart->setDecompoundingParts($res->getDecompoundingParts());
////                        $resultSet[] = [$decompoundingPart];
////                        return $decompoundingPart;
////                        if ($res->isPredefined()) {
////                            $decompoundingPart->setIsPredefined(true);
////                            $decompoundingPart->setDecompoundingParts($res->getDecompoundingParts());
////                            return $decompoundingPart;
////                        }
//                    }
//                }
//            }
//        }
//        $decompoundingPart->setDecompoundingParts($resultSet);
//        return $decompoundingPart;
//    }

    /**
     * @param string $word
     * @return DecompoundingPart[]
     */
    private function split($word)
    {
        if ($word === null || $word === "") {
            return null;
        }

        // check if the word is an exception
        if ( ($res = $this->getPredefinedDecompoundingResult($word)) !== null){
            return [$res];
        }

        $originalDecompoundingPart = new DecompoundingPart($word, $this->isValidWord($word));

        $charCount = mb_strlen($word);
        $results = [];
        $resultSet = [];
        for ($i = $charCount - $this->minWordLength; $i >= 0; $i--) {
            $leftPart = mb_substr($word, 0, $i);
            $rightPart = mb_substr($word, $i);

            $rightPartsOfWord = [
                [$rightPart, ""] // add variant without interfix by default
            ];
            $interfixedParts = $this->interfixer->getInterfixedParts($rightPart);
            $rightPartsOfWord = array_merge($rightPartsOfWord, $interfixedParts);

            foreach ($rightPartsOfWord as $rightPartOfWordArr) {
                $rightPartOfWord = $rightPartOfWordArr[0];
                $interfix = $rightPartOfWordArr[1];
                $rightDecompounding = null;
                if ($this->isValidWord($rightPartOfWord)) {
                    if ($i > 0) { // 0 would mean we're checking the original $word
                        $rightDecompoundings = $this->split($rightPartOfWord);
                        foreach($rightDecompoundings as $key => $rightDecompounding) {
                            if ($rightDecompounding->getInterfix() !== ""){ // we already have the interfix, so we can ignore these parts
                                unset($rightDecompoundings[$key]);
                            }
                            $rightDecompounding->setInterfix($interfix);
                        }

                        $leftDecompoundings = $this->split($leftPart);
                        foreach($rightDecompoundings as $rightDecompounding){
                            foreach($leftDecompoundings as $leftDecompounding){
                                $resultSet[] = array($leftDecompounding, $rightDecompounding);
                            }
                        }
                    } else {
                        $finalDecompounding = new DecompoundingPart($rightPartOfWord, true);
                        $finalDecompounding->setInterfix($interfix);
                        $results[] = $finalDecompounding;
                    }
                }
            }
        }

        if(count($results) === 0){ // even after we checked if the original word might have interfixes, we couldn't find it in the dictionary
            $results[] = $originalDecompoundingPart; // so let's add the original one
        }
        /** @var DecompoundingPart $part */
        foreach($results as $part){
            $part->setDecompoundingParts($resultSet);
        }
        return $results;
    }
}