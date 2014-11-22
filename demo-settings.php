<?php

use paslandau\DictionaryDecompounder\Decompounder;
use paslandau\DictionaryDecompounder\Dictionary\ArrayDictionary;
use paslandau\DictionaryDecompounder\Interfix\Interfixer;

require_once __DIR__ . '/demo-bootstrap.php';

// Define dictionary for the lookup of known words
mb_internal_encoding("utf-8"); // Set internal charset to utf-8 (dictionary file is in utf-8)
$pathToDict = __DIR__ . "/resources/ger-dict.txt";
$dictionary = new ArrayDictionary();
$dictionary->loadFromFile($pathToDict);
//$dictionary->addWords(["zahn", "Techniker","Technik","Anwalt","Alt"]);
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
 		"bergemann",
 		"Gipsprodukten",
    "Sandsteintürme",
    "Preiselbeermarmelade",
 		"Automatisierung funktion",
 		"Automatisierungs-funktion",
    "Techniker-Anwalt",
  		"ZahnTechniker-Anwalt",
 		"Zerspannungstechniker",
 		"Hochseilakrobat",
 		"Eisdiele",
 		"Eismaschine",
 		"Glücksgefühls",
 		"Gefühlsglücks",
 		"Himbeereis",
 		"Erdbeereismaschine",
 		"Schokoladenmanufaktur",
 		"Manneskraft"
);
foreach ($words as $word) {
    $res = $decompounder->decompoundWord($word);
    echo "$word: ";
    echo $res->toFormattedString(1, false);
    $decs = $res->getDecompoundingResult();
    echo "\n==> " . implode(", ", $decs);
    $decs = $res->getDecompoundingResult(false, false, false, false);
    echo "\n==> " . implode(", ", $decs);
    $decs = $res->getDecompoundingResult(true, false, false, false);
    echo "\n==> " . implode(", ", $decs);
    $decs = $res->getDecompoundingResult(true, true, false, false);
    echo "\n==> " . implode(", ", $decs);
    $decs = $res->getDecompoundingResult(true, true, true, false);
    echo "\n==> " . implode(", ", $decs);
    $decs = $res->getDecompoundingResult(true, true, true, true);
    echo "\n==> " . implode(", ", $decs);
    echo "\n";
}