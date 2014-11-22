<?php namespace paslandau\DictionaryDecompounder\Dictionary;

use paslandau\DictionaryDecompounder\Normalizer\NormalizerInterface;

interface DictionaryInterface
{
    /**
     * Adds all $words to the dictionary.
     * @param array $words
     */
    public function addWords(array $words);

    /**
     * Adds a word to the dictionary after normalizing it
     * @param string $word
     */
    public function add($word);

    /**
     * Removes $word after normalizing it from the dictionary
     * @param string $word
     * @return bool. true if the word has been in the dictionary - false otherwise.
     */
    public function remove($word);

    /**
     * Checks if $word is in the dictionary
     * @param string $word
     * @return bool. true if the word is in the dictionary.
     */
    public function exists($word);

    /**
     * @return \paslandau\DictionaryDecompounder\Normalizer\NormalizerInterface
     */
    public function getNormalizer();
}