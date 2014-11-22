<?php

use paslandau\DictionaryDecompounder\Decompounder;
use paslandau\DictionaryDecompounder\Dictionary\ArrayDictionary;
use paslandau\DictionaryDecompounder\Filter\DecompoundFilter;
use paslandau\DictionaryDecompounder\Interfix\Interfixer;

require_once __DIR__ . '/demo-bootstrap.php';

// Define dictionary for the lookup of known words
mb_internal_encoding("utf-8"); // Set internal charset to utf-8 (dictionary file is in utf-8)
$pathToDict = __DIR__ . "/resources/ger-dict.txt";
$dictionary = new ArrayDictionary();
$dictionary->loadFromFile($pathToDict);

// Define interfixes - this is language specific!
$interfixes = array("e", "s", "es", "n", "en", "er", "ens"); // These interfixes make sense for german words
foreach($interfixes as $interfix){
    $interfixes[] = $interfix."-";
}
$interfixes[] = "-";
$interfixer = new Interfixer($interfixes);

// A word should have at least two characters
$minLength = 2;

// Create decompounder
$decompounder = new Decompounder($dictionary, $interfixer, $minLength);

$words = array(
    "Handtuch",
    "Eisenbahn",
    "Frottierhandtuch",
    "Regenbogenfamilien",
    "Außenviertel",
    "Bergemann",
    "Gipsprodukten",
    "Sandsteintürme",
    "Preiselbeermarmelade",
    "Automatisierungsfunktion",
    "Automatisierungs-funktion",
    "Techniker-Anwalt",
    "Zahntechniker-Anwalt",
    "Zerspannungstechniker",
    "Hochseilakrobat",
    "Eisdiele",
    "Eismaschine",
    "Glücksgefühls",
    "Gefühlsglücks",
    "Himbeereis",
    "Erdbeereismaschine",
    "Schokoladenmanufaktur",
    "Manneskraft",
    "Donaudampfschifffahrtsgesellschaftskapitän"
);

$filter = new DecompoundFilter(true,true);
foreach ($words as $word) {
    $res = $decompounder->decompoundWord($word);
    echo "$word:";
    echo "\n";
    echo "==> " . implode(", ", $filter->filterBest($res));
    echo "\n";
    echo $res->toFormattedString(false);
    echo "\n\n";
}