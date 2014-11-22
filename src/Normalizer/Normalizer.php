<?php

namespace paslandau\DictionaryDecompounder\Normalizer;


class Normalizer implements NormalizerInterface
{

    /**
     * @param string $word
     * @return string
     */
    public function normalize($word)
    {
        $word = trim($word);
        $word = mb_strtolower($word);
        return $word;
    }
} 