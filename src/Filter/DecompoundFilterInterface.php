<?php namespace paslandau\DictionaryDecompounder\Filter;

use paslandau\DictionaryDecompounder\CompleteWord;

interface DecompoundFilterInterface
{
    /**
     * @param CompleteWord $word
     * @param bool $includeOriginal [optional]. Default: false.
     * @return string[][]
     */
    public function filterList(CompleteWord $word, $includeOriginal = null);

    /**
     * @param CompleteWord $word
     * @param bool $includeOriginal [optional]. Default: false.
     * @return string[]
     */
    public function filterBest(CompleteWord $word, $includeOriginal = null);
}