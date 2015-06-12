<?php

use paslandau\DictionaryDecompounder\Decompounder;
use paslandau\DictionaryDecompounder\Dictionary\ArrayDictionary;
use paslandau\DictionaryDecompounder\Filter\DecompoundFilter;
use paslandau\DictionaryDecompounder\Interfix\Interfixer;

require_once __DIR__ . '/demo-bootstrap.php';

$dictionary = new ArrayDictionary();
$dictionary->addWords(["Don","Au", "Donau","Dampf","Schiff","Fahrt", "Gesellschaft","Kapitän"]);

// Define interfixes - this is language specific!
$interfixes = array("s");
$interfixer = new Interfixer($interfixes);

// Create decompounder
$decompounder = new Decompounder($dictionary, $interfixer);

// Decompound
$word = "donaudampfschifffahrtsgesellschaftskapitän";
$result = $decompounder->decompoundWord($word);
$filter = new DecompoundFilter(false,true,true);
$res = $filter->filterBest($result);
echo implode("-",$res);