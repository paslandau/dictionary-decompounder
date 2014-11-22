<?php namespace paslandau\DictionaryDecompounder\Interfix;

interface InterfixerInterface
{
    /**
     * Returns all matched interfixed split from the corresponding word as array.
     * If $this->interfixes are ["es", "s"]
     * E.g. "Waldes"
     * return [
     *   ["Wald", "es"],
     *   ["Walde", "s"]
     * ]
     * @param string $word
     * @return string[][]
     */
    public function getInterfixedParts($word);
}