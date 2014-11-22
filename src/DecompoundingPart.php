<?php
namespace paslandau\DictionaryDecompounder;

class DecompoundingPart
{
    /**
     * The identified word (part)
     * @var string
     */
    private $word;

    /**
     * True, if the word was in the dictionary of the Decompounder.
     * @var bool
     */
    private $inDictionary;

    /**
     * True, if the word was the result of an \Exception check in the Decompounder.
     * @var bool
     */
    private $isPredefined;

    /**
     * The interfix, if it existed
     * @var string
     */
    private $interfix;

    /**
     *
     * @var DecompoundingPart[][]
     */
    private $decompoundingParts;

    /**
     * @param string $word
     * @param bool $isInDictionary
     * @param bool $isPredefined. [optional]. Default: false.
     */
    public function __construct($word, $isInDictionary, $isPredefined = null)
    {
        $this->word = $word;
        $this->inDictionary = $isInDictionary;
        if($isPredefined === null) {
            $isPredefined = false;
        }
        $this->isPredefined = $isPredefined;
        $this->interfix = "";
        $this->decompoundingParts = array();
    }

    /**
     * Returns the most fitting word parts as string array.
     * Because there might be multiple possibilities to decompound a compound word, there are different options to control the result.
     * @param bool $canReturnSelf. [optional]. Default: true. Returns the input word itself if it is in the dictionary
     * @param bool $addInterfix. [optional]. Default: false. Adds the interfix characters to the different word parts (if there were any in the input word)
     * @param bool $preferFewestParts. [optional]. Default: true. If there's more than one valid decompounding, the one with the fewest parts will be preferred.
     * @param bool $preferNoInterfix. [optional]. Default: true. If there's more than one valid decompounding, the one that has no interfix in it will be preferred.
     * @return string[]
     */
    public function getDecompoundingResult($canReturnSelf = true, $addInterfix = false, $preferFewestParts = true, $preferNoInterfix = true)
    {
        // The original keyword itself is in the dictionary --- no decompounding necessary
        if ($canReturnSelf && $this->inDictionary) {
            $word = $this->word;
            if ($addInterfix) {
                $word .= $this->interfix;
            }
            return [$word];
        }

        $valid = $this->getOnlyValidDecompoundings();
        // The original keyword couldn't be decompounded  --- return it as is.
        if (count($valid) === 0) {
            $word = $this->word;
            if ($addInterfix) {
                $word .= $this->interfix;
            }
            return [$word];
        }

        // check the decompounding parts
        $tmpRes = array();
        $minCount = null;
        foreach ($valid as $arr) {
            $innerRes = array();
            $hasInterfix = false;
            /**
             * @var DecompoundingPart $dr
             */
            foreach ($arr as $dr) {
                if ($dr->inDictionary) {
                    $word = $dr->getWord();
                    if ($addInterfix) {
                        $word .= $dr->getInterfix();
                    }
                    if (!($dr->interfix === null || $dr->interfix === "")) {
                        $hasInterfix = true;
                    }
                    $innerRes [] = $word;
                } else {
                    $innerRes = array_merge($innerRes, $dr->getDecompoundingResult($canReturnSelf, $addInterfix, $preferFewestParts, $preferNoInterfix));
                }
            }
            // prepare sorting for fewest parts
            $count = count($innerRes);
            if ($count < $minCount || $minCount === null) {
                $minCount = $count;
            }
            $tmpRes[] = array("data" => $innerRes, "count" => $count, "hasInterfix" => $hasInterfix);
        }
        if ($preferFewestParts) {
            foreach ($tmpRes as $key => $data) {
                if ($data["count"] > $minCount) {
                    unset($tmpRes[$key]);
                }
            }
        }
        //by now we only have the ones with fewest count (if flag was set)
        do {
            // take one after the other until we find one without interfix
            // if we find none, we simply take the last one we got
            $final = array_shift($tmpRes);
        } while ($preferNoInterfix && count($tmpRes) > 0 && $final["hasInterfix"]);
        return $final["data"];
    }

    /**
     * @return DecompoundingPart[]
     */
    public function getOnlyValidDecompoundings()
    {
        $validDecompoundings = array();
        foreach ($this->decompoundingParts as $arr) {
            $add = true;
            foreach ($arr as $dr) {
                if (!$dr->isValid()) {
                    $add = false;
                    break;
                }
            }
            if ($add) {
                $validDecompoundings[] = $arr;
            }
        }
        return $validDecompoundings;
    }

    /**
     * Returns true is $this->word is $this->isInDictionary OR at least one of $this->decompoundingParts is completely valid (recursively)
     * @return boolean
     */
    public function isValid()
    {
        // Am I valid?
        if ($this->inDictionary) {
            return true;
        }

        // Or are my parts valid?
        foreach ($this->decompoundingParts as $arr) {
            $isValid = true;
            foreach ($arr as $dr) {
                if (!$dr->isValid()) {
                    $isValid = false;
                    break;
                }
            }
            if ($isValid) {
                return true;
            }
        }
        return false;
    }

    public function toFormattedString($level = 1, $onlyValid = false)
    {
        $whiteSpace = array_fill(0, $level, " ");
        $whiteSpace = implode("", $whiteSpace);

        $s = array();
        $s[] = $whiteSpace . $this->word . (($this->interfix === null || $this->interfix === "") ? "" : "[" . $this->interfix . "]") . " (" . ($this->inDictionary?"true":false). ") ";

        $decParts = $this->decompoundingParts;
        if ($onlyValid) {
            $decParts = $this->getOnlyValidDecompoundings();
        }
        foreach ($decParts as $arr) {
            foreach ($arr as $key => $dr) {
                $s [] = $dr->toFormattedString($level + 1, $onlyValid);
            }
        }
        return implode("\n", $s);
    }

    /**
     * @return DecompoundingPart[][]
     */
    public function getDecompoundingParts()
    {
        return $this->decompoundingParts;
    }

    /**
     * @return String
     */
    public function getInterfix()
    {
        return $this->interfix;
    }

    /**
     * @return boolean
     */
    public function isPredefined()
    {
        return $this->isPredefined;
    }

    /**
     * @return boolean
     */
    public function isInDictionary()
    {
        return $this->inDictionary;
    }

    /**
     * @return string
     */
    public function getWord()
    {
        return $this->word;
    }

    /**
     * @param DecompoundingPart[][] $decompoundingParts
     */
    public function setDecompoundingParts($decompoundingParts)
    {
        $this->decompoundingParts = $decompoundingParts;
    }

    /**
     * @param string $interfix
     */
    public function setInterfix($interfix)
    {
        $this->interfix = $interfix;
    }

    /**
     * @param boolean $isPredefined
     */
    public function setIsPredefined($isPredefined)
    {
        $this->isPredefined = $isPredefined;
    }

    /**
     * @param boolean $isInDictionary
     */
    public function setInDictionary($isInDictionary)
    {
        $this->inDictionary = $isInDictionary;
    }

    /**
     * @param string $word
     */
    public function setWord($word)
    {
        $this->word = $word;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toFormattedString();
    }
}