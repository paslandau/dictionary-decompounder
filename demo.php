<?php

use paslandau\DictionaryDecompounder\Decompounder;
use paslandau\DictionaryDecompounder\Dictionary\ArrayDictionary;
use paslandau\DictionaryDecompounder\Interfix\Interfixer;

require_once __DIR__ . '/demo-bootstrap.php';

// Create dictionary
$dictionary = new ArrayDictionary();
$dictionary->addWords(["Herren","Schuhe","Foo","Bar"]);

// Define interfixes - this is language specific!
$interfixes = array("-","s","s-");
$interfixer = new Interfixer($interfixes);

// Create decompounder
$decompounder = new Decompounder($dictionary, $interfixer);

// Decompound
$word = "Herrenschuhe";
$result = $decompounder->decompoundWord($word);

//display results
$parts = $result->getDecompoundingResult();
echo "Original:\t{$result->getWord()}\n".
     "Word parts:\t".implode(", ", $parts);