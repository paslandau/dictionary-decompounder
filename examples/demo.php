<?php

use paslandau\DictionaryDecompounder\Decompounder;
use paslandau\DictionaryDecompounder\Dictionary\ArrayDictionary;
use paslandau\DictionaryDecompounder\Filter\DecompoundFilter;
use paslandau\DictionaryDecompounder\Interfix\Interfixer;

require_once __DIR__ . '/demo-bootstrap.php';

// Create dictionary
$dictionary = new ArrayDictionary();
$dictionary->addWords(["Straße","Sperre","Foo","Bar"]);

// Define interfixes - this is language specific!
$interfixes = array("n");
$interfixer = new Interfixer($interfixes);

// Create decompounder
$decompounder = new Decompounder($dictionary, $interfixer);

// Decompound
$word = "Straßensperre";
$result = $decompounder->decompoundWord($word);

echo $result;