<?php

namespace paslandau\DictionaryDecompounder;


class PartialWords {

    /**
     * @var CompleteWord
     */
    private $leftPartialWord;

    /**
     * @var CompleteWord
     */
    private $rightPartialWord;

    /**
     * @param CompleteWord $leftPartialWord
     * @param CompleteWord $rightPartialWord
     */
    function __construct(CompleteWord $leftPartialWord, CompleteWord $rightPartialWord)
    {
        $this->leftPartialWord = $leftPartialWord;
        $this->rightPartialWord = $rightPartialWord;
    }

    /**
     * True if left and right partial words are valid
     * @return bool
     */
    public function areValid(){
        return $this->leftPartialWord->isValid() && $this->rightPartialWord->isValid();
    }

    /**
     * @return CompleteWord
     */
    public function getLeftPartialWord()
    {
        return $this->leftPartialWord;
    }

    /**
     * @param CompleteWord $leftPartialWord
     */
    public function setLeftPartialWord($leftPartialWord)
    {
        $this->leftPartialWord = $leftPartialWord;
    }

    /**
     * @return CompleteWord
     */
    public function getRightPartialWord()
    {
        return $this->rightPartialWord;
    }

    /**
     * @param CompleteWord $rightPartialWord
     */
    public function setRightPartialWord($rightPartialWord)
    {
        $this->rightPartialWord = $rightPartialWord;
    }

} 