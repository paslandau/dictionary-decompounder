<?php
namespace paslandau\DictionaryDecompounder;

use paslandau\DictionaryDecompounder\Dictionary\DictionaryInterface;
use paslandau\DictionaryDecompounder\Interfix\InterfixerInterface;

class Decompounder
{
    /**
     * Map of [string ($word) => CompleteWord ($decompoundingResult)] that denotes how a certain word has to
     * be decompounded because it won't be caught right by the decompounding algorithm.
     * E.g. "Eisenbahn" => new DecompoundingPart("Eisenbahn", true);
     *        (because otherwise we would end up with "Eisen" and "Bahn".
     * @var CompleteWord[]
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
     * @return CompleteWord
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
     * Adds a predefined decompounding.
     * @param $word
     * @param CompleteWord $decompoundingResult
     */
    public function addPredefinedDecompoundingResult($word, CompleteWord $decompoundingResult)
    {
        $word = $this->dictionary->getNormalizer()->normalize($word);
        $this->predefinedDecompoundingResults[$word] = $decompoundingResult;
    }

    /**
     * @param $word
     * @return null|CompleteWord
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

    /**
     * @param string $word
     * @return CompleteWord[]
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

        $originalCompleteWord = new CompleteWord($word, $this->isValidWord($word));

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
                if ($this->isValidWord($rightPartOfWord)) {
                    if ($i > 0) { // 0 would mean we're checking the original $word
                        $rightWords = $this->split($rightPartOfWord);
                        foreach($rightWords as $key => $rightWord) {
                            if ($rightWord->getInterfix() !== ""){ // we already have the interfix, so we can ignore these parts
                                unset($rightWords[$key]);
                            }
                            $rightWord->setInterfix($interfix);
                        }

                        $leftWords = $this->split($leftPart);
                        foreach($rightWords as $rightWord){
                            foreach($leftWords as $leftWord){
                                $resultSet[] = new PartialWords($leftWord, $rightWord);
                            }
                        }
                    } else {
                        $completeWord = new CompleteWord($rightPartOfWord, true);
                        $completeWord->setInterfix($interfix);
                        $results[] = $completeWord;
                    }
                }
            }
        }

        if(count($results) === 0){ // even after we checked if the original word might have interfixes, we couldn't find it in the dictionary
            $results[] = $originalCompleteWord; // so let's add the original one
        }
        /** @var CompleteWord $part */
        foreach($results as $part){
            $part->setPartialWordsList($resultSet);
        }
        return $results;
    }
}