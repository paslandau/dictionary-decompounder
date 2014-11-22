<?php namespace paslandau\DictionaryDecompounder\Normalizer;

interface NormalizerInterface
{
    /**
     * @param string $word
     * @return string
     */
    public function normalize($word);
}