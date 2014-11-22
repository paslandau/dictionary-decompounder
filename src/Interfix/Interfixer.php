<?php

namespace paslandau\DictionaryDecompounder\Interfix;


class Interfixer implements InterfixerInterface
{

    /**
     * List of interfixes that might "glue" words together,
     * e.g. ["s", "s-", "-"],
     *        Automatisierungsfunktion => Automatisierung, Funktion
     *        ===============^
     * @var String[]
     */
    private $interfixes;

    /**
     * @var string[]
     */
    private $interfixPatterns;

    /**
     * @param array $interfixes. [optional]. Default: [].
     */
    function __construct($interfixes = null)
    {
        if($interfixes === null) {
            $interfixes = [];
        }
        $this->interfixes = $interfixes;
        $this->interfixPatterns = null;
    }

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
    public function getInterfixedParts($word)
    {
        $patterns = $this->getInterfixPatterns();
        $interfixedParts = [];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $word, $match)) {
                $wordWithoutInterfix = preg_replace($pattern, "", $word);
                $interfix = $match[1];
                $interfixedParts[] = array($wordWithoutInterfix, $interfix);
            }
        }
        return $interfixedParts;
    }

    /**
     * Gets the regex patterns for $this->interfixes
     * @return string[]
     */
    private function getInterfixPatterns()
    {
        if ($this->interfixPatterns === null) {
            $this->interfixPatterns = [];
            $q = "#";
            $patternArrs = [];
            foreach ($this->interfixes as $interfix) {
                // order patterns by count so that the results wont overlap (e.g. interfix "en" and "n")
                $charCount = strlen($interfix);
                if (!array_key_exists($charCount, $patternArrs)) {
                    $patternArrs[$charCount] = [];
                }
                $quoted = preg_quote($interfix, $q);
                $patternArrs[$charCount][] = $quoted;
            }
            foreach ($patternArrs as $patternArr) {
                $pattern = $q . "(" . implode("|", $patternArr) . ")$" . $q . "ui";
                $this->interfixPatterns[] = $pattern;
            }
        }
        return $this->interfixPatterns;
    }
}