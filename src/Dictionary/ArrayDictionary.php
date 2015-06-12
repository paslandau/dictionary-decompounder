<?php

namespace paslandau\DictionaryDecompounder\Dictionary;


use paslandau\DictionaryDecompounder\Normalizer\Normalizer;
use paslandau\DictionaryDecompounder\Normalizer\NormalizerInterface;

class ArrayDictionary implements DictionaryInterface
{

    /**
     * Dictionary of known words used for lookup
     * @var string[]
     */
    private $dictionary;

    /**
     * @var \paslandau\DictionaryDecompounder\Normalizer\NormalizerInterface
     */
    private $normalizer;

    /**
     * @param NormalizerInterface $normalizer [optional]. Default: null.
     */
    function __construct($normalizer = null)
    {
        if($normalizer === null) {
            $normalizer = new Normalizer();
        }
        $this->normalizer = $normalizer;
        $this->dictionary = [];
    }

    /**
     * Loads a dictionary from file. Expects one word per line.
     * @param string $pathToFile
     */
    public function loadFromFile($pathToFile)
    {
        $lines = file($pathToFile);
        $this->addWords($lines);
    }

    /**
     * Adds all $words to the dictionary.
     * @param array $words
     */
    public function addWords(array $words){
        foreach ($words as $word) {
            $this->add($word);
        }
    }

    /**
     * Adds a word to the dictionary after normalizing it
     * @param string $word
     */
    public function add($word)
    {
        $word = $this->normalizer->normalize($word);
        $this->dictionary[$word] = $word;
    }

    /**
     * Removes $word after normalizing it from the dictionary
     * @param string $word
     * @return bool. true if the word has been in the dictionary - false otherwise.
     */
    public function remove($word)
    {
        $word = $this->normalizer->normalize($word);
        if (array_key_exists($word, $this->dictionary)) {
            unset($this->dictionary[$word]);
            return true;
        }
        return false;
    }

    /**
     * Checks if $word is in the dictionary
     * @param string $word
     * @return bool. true if the word is in the dictionary.
     */
    public function exists($word)
    {
        $word = $this->normalizer->normalize($word);
        return array_key_exists($word, $this->dictionary);
    }

    /**
     * @return \paslandau\DictionaryDecompounder\Normalizer\NormalizerInterface
     */
    public function getNormalizer()
    {
        return $this->normalizer;
    }
}