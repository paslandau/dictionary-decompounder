<?php

namespace paslandau\DictionaryDecompounder;


class CompleteWord {

    /**
     * The identified word
     * @var string
     */
    private $word;

    /**
     * True, if the word was in the dictionary of the Decompounder.
     * @var bool
     */
    private $inDictionary;

    /**
     * The interfix, if one was found
     * @var string
     */
    private $interfix;

    /**
     * @var PartialWords[]
     */
    private $partialWordsList;

    /**
     * @param string $word
     * @param bool $inDictionary
     * @param PartialWords[] $partialWordsList [optional]. Default: null.
     * @param string $interfix [optional]. Default: "".
     */
    function __construct($word, $inDictionary, array $partialWordsList = null, $interfix = null)
    {
        $this->inDictionary = $inDictionary;
        $this->word = $word;
        if($interfix === null) {
            $interfix = "";
        }
        $this->interfix = $interfix;
        if($partialWordsList === null) {
            $partialWordsList = [];
        }
        $this->partialWordsList = $partialWordsList;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        if ($this->inDictionary) {
            return true;
        }
        foreach ($this->$partialWordsList as $partialWords){
            if($partialWords->areValid()){
                return true;
            }
        }
        return false;
    }

    /**
     * @return PartialWords[]
     */
    public function getValidPartialWords(){
        $result = [];
        foreach($this->partialWordsList as $partialWords){
            if($partialWords->areValid()){
                $result[] = $partialWords;
            }
        }
        return $result;
    }

    public function toFormattedString($onlyValid = false, $level = 0 , $char = "")
    {
        $whiteSpace = " ";
        if($level > 1) {
            $filler = array_fill(0, $level-1, "║");
            $whiteSpace .= implode("", $filler);
        }
        $s = array();
        $s[] =  $whiteSpace . $char . $this->word . (($this->interfix === "") ? "" : "[" . $this->interfix . "]") . " (" . ($this->inDictionary?"true":"false"). ") ";

        $decParts = $this->partialWordsList;
        if ($onlyValid) {
            $decParts = $this->getValidPartialWords();
        }
        foreach ($decParts as $partialWords) {
            $s[] = $partialWords->getLeftPartialWord()->toFormattedString($onlyValid, $level + 1, "╔ ");
            $rightChar = "╚ ";
            if(count($partialWords->getRightPartialWord()->getPartialWordsList()) > 0){
                $rightChar = "╠ ";
            }
            $s[] = $partialWords->getRightPartialWord()->toFormattedString($onlyValid, $level + 1, $rightChar);
        }
        return implode("\n", $s);
    }

    /**
     * @return boolean
     */
    public function isInDictionary()
    {
        return $this->inDictionary;
    }

    /**
     * @param boolean $inDictionary
     */
    public function setInDictionary($inDictionary)
    {
        $this->inDictionary = $inDictionary;
    }

    /**
     * @return string
     */
    public function getInterfix()
    {
        return $this->interfix;
    }

    /**
     * @param string $interfix
     */
    public function setInterfix($interfix)
    {
        $this->interfix = $interfix;
    }

    /**
     * @return PartialWords[]
     */
    public function getPartialWordsList()
    {
        return $this->partialWordsList;
    }

    /**
     * @param PartialWords[] $partialWordsList
     */
    public function setPartialWordsList($partialWordsList)
    {
        $this->partialWordsList = $partialWordsList;
    }

    /**
     * @return string
     */
    public function getWord()
    {
        return $this->word;
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
        return $this->toFormattedString(false);
    }
}