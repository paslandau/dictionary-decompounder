#dictionary-decompounder
[![Build Status](https://travis-ci.org/paslandau/dictionary-decompounder.svg?branch=master)](https://travis-ci.org/paslandau/dictionary-decompounder)

A dictionary based decompounder that recognizes compound words like 'Herrenschuh' and splits them into its individual parts, e.g. 'Herren' and 'Schuh'.

##Description

A compound word is the combination of mulitple words glued together to one "new" word. 
Compounding is primarily known in in German, Scandinavian Languages, Finnish and Korean. In German, there is the well known example 
"Donaudampfschifffahrtsgesellschaftskapitän" that ([according to dict.cc](http://www.dict.cc/?s=Donaudampfschifffahrtsgesellschaftskapit%C3%A4n)) translates to 
"Danube steamship company captain" or "captain of the Danube Steam Shipping Company". It actually consists of several individual nouns:
Donau-Dampf-Schiff-Fahrt[s]-Gesellschaft[s]-Kapitän (letters in square brackets are so called interfixes).

In the field of text mining/information retrieval, decompounding is a valuable technique to widen the index terms of a document in order to make 
it relevant for the individual terms of a compound word. The `dictionary-decompounder` provides a dictionary based algorithm to perform the decompounding.
In theory, it should be language-agnostic since you need to provide the dictionary (and the interfixes) yourself - but I'm not familiar with other 
compound languages apart from German so take this with a grain of salt.

A german dictionary based on the [german full form dictionary](http://www.danielnaber.de/morphologie/) of Daniel Naber is included and located in `resources/dict-de.txt` (UTF-8 encoded). 
It is licensed under [Attribution-ShareAlike 4.0 International (CC BY-SA 4.0)](http://creativecommons.org/licenses/by-sa/4.0/).
In addition, this project was originally developed as a PHP translation of Daniel's [jwordsplitter](https://github.com/danielnaber/jwordsplitter) written in Java (although
there isn't much left of the original code after refactoring it like 3 times :))

###Basic Usage
```php

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

```

**Output**

     straßensperre (false) 
     ╔ straße[n] (true) 
     ╚ sperre (true) 

###Examples

See `demo*.php` files.

##Requirements

- PHP >= 5.5

##Installation

The recommended way to install dictionary-decompounder is through [Composer](http://getcomposer.org/).

    curl -sS https://getcomposer.org/installer | php

Next, update your project's composer.json file to include DictionaryDecompounder:

    {
        "repositories": [
            {
                "type": "git",
                "url": "https://github.com/paslandau/dictionary-decompounder.git"
            }
        ],
        "require": {
             "paslandau/dictionary-decompounder": "~0"
        }
    }

After installing, you need to require Composer's autoloader:
```php

require 'vendor/autoload.php';
```

##General workflow and customization options

Firstly, a `Dictionary` and an `Interfixer` (that splits a word into "word" + "interfix") need to be provided. These are language specific:

```php
// Create dictionary
$dictionary = new ArrayDictionary();
$dictionary->addWords(["Straße","Sperre","Foo","Bar"]);
//or load from text file
$pathToDict = __DIR__ . "/resources/ger-dict.txt";;
$dictionary->loadFromFile($pathToDict);

// Define interfixes - this is language specific!
$interfixes = [
    "e", "s", "es", "n", "en", "er", "ens",         // These interfixes make sense for german words
    "e-", "s-", "es-", "n-", "en-", "er-", "ens-",  // let's add "-" to each one
    "-"                                             // and the "-" itself 
    ];
$interfixer = new Interfixer($interfixes);
```

Next, a `Decompounder` is instantiated with the dictionary and the interfixer as dependencies

```php
// Create decompounder
$decompounder = new Decompounder($dictionary, $interfixer);
```

By using the `Decompounder`s `decompoundWord()` method, an object of type `CompleteWord` is returned.

```php
// Decompound
$word = "Donaudampfschifffahrtsgesellschaftskapitän";
$result = $decompounder->decompoundWord($word);
```

For a quick overview, you can just `echo` the result out, which will show you all possible decompounding parts that were found:

```php
echo $result;
```

**Output**

     donaudampfschifffahrtsgesellschaftskapitän (false) 
     ╔ donaudampfschifffahrtsgesellschafts (false) 
     ║╔ donaudampfschifffahrts (false) 
     ║║╔ donaudampfschiff (false) 
     ║║║╔ donaudampf (false) 
     ║║║║╔ donau (true) 
     ║║║║║╔ don (true) 
     ║║║║║╚ au (true) 
     ║║║║╚ dampf (true) 
     ║║║╚ schiff (true) 
     ║║╚ fahrt[s] (true) 
     ║╚ gesellschaft[s] (true) 
     ╚ kapitän (true) 

`<word> (false)` means that `<word>` has not been found in the dictionary. This is important when the "real" decompounding result should be computed,
which should be an array containing every partial word of the compound word.  In fact, we found multiple options to split "Donaudampfschifffahrtsgesellschaftskapitän":

 - donaudampfschifffahrtsgesellschafts-kapitän
 - donaudampfschifffahrts-gesellschaft[s]-kapitän
 - donaudampfschiff-fahrt[s]-gesellschaft[s]-kapitän
 - donaudampf-schiff-fahrt[s]-gesellschaft[s]-kapitän
 - donau-dampf-schiff-fahrt[s]-gesellschaft[s]-kapitän  
 - don-au-dampf-schiff-fahrt[s]-gesellschaft[s]-kapitän 
  
To get the "one" solution, the result now has to be filtered. This filter has to implement the `DecompoundFilterInterface` and provide a method to list all possible solutions as multi dimensional string array (via `filterList()` method) and
to pick the "best" solution as string array (via `filterBest()`). 

A default filter is provided with the `DecompoundFilter` class. This class provides several settings to customize the filtered result.
The most important one is the `$onlyValid` flag which states that only solutions are acceptable that contain only valid individual words (e.g. exist in the provided dictionary).

```php
$onlyValid = true;
$addInterfix = true;
$filter = new DecompoundFilter($addInterfix,$onlyValid);
$decompoundedResult = $filter->filterList($result);
```

By applying this filter, 4 of the above possible decompoundings are invalid:

 - donaudampfschifffahrtsgesellschafts-kapitän (donaudampfschifffahrtsgesellschafts is not in dictionay)
 - donaudampfschifffahrts-gesellschaft[s]-kapitän (donaudampfschifffahrts is not in dictionay)
 - donaudampfschiff-fahrt[s]-gesellschaft[s]-kapitän (donaudampfschiff is not in dictionay)
 - donaudampf-schiff-fahrt[s]-gesellschaft[s]-kapitän (donaudampf is not in dictionay)
 
But we still got 2 possible, valid solutions: 

 - donau-dampf-schiff-fahrt[s]-gesellschaft[s]-kapitän  
 - don-au-dampf-schiff-fahrt[s]-gesellschaft[s]-kapitän 
 
Now, "donau-dampf-schiff-fahrt[s]-gesellschaft[s]-kapitän" is the one we're looking for and we can get that by using the `$preferLessParts` option on the `DecompoundFilter`.
This option assures that decompoundings with less parts are weighted higher than decompoundings with more parts. Activating this filter and using the `filterBest()` method yields
the desired result:

```php
$onlyValid = true;
$addInterfix = true;
$preferLessParts = true;
$filter = new DecompoundFilter($addInterfix,$onlyValid,$preferLessParts);
$decompoundedResult = $filter->filterBest($result);
echo implode("-",$decompoundedResult);
```

    donau-dampf-schiff-fahrts-gesellschafts-kapitän
 
In that case, we still have the interfixes attached to the corresponding words in the result (fahrt,s and gesellschaft,s). To omit them, set the `$addInterfix` option to `false`:

```php
$onlyValid = true;
$addInterfix = false; // omit interfixes
$preferLessParts = true;
$filter = new DecompoundFilter($addInterfix,$onlyValid,$preferLessParts);
$decompoundedResult = $filter->filterBest($result);
echo implode("-",$decompoundedResult);
```

    donau-dampf-schiff-fahrt-gesellschaft-kapitän

###Algorithm

The `dictionary-decompounder` implements a dictionary based decompounding algorithm. In short, there has to be a dictionary of known words that is used
to analyze compound words. In addition, so called interfixes can be provided, that "glue" two parts of a compound word together and are taken into
account when checking the dictionary. Example:

    Dictionary
    ===
    Hochzeit
    Messe
    
    Interfixes
    ===
    s
    
    Compound word
    ===
    Hochzeitsmesse
    
    Result
    ===
    Hochzeit (known word)
    s        (interfix)
    Messe    (known word)
    
The compound word is analyzed from right to left and each text portion is compared to the dictionary. If the portion exists in the dictionary, a new 
`PartialWords` instance is created. Example:

    Hochzeitsmesse|
    --------------^ "" not in dictionary
    Hochzeitsmess|e
    -------------^  "e" not in dictionary
    Hochzeitsmes|se
    ------------^   "se" not in dictionary
    Hochzeitsme|sse
    -----------^    "sse" not in dictionary
    Hochzeitsm|esse
    ----------^     "esse" not in dictionary
    Hochzeits|messe
    ---------^      "messe" in dictionary
    
    #Let's check the remaining left part
    
    Hochzeits|
    ---------^      "" not in dictionary
    Hochzeit|s
    --------^       "s" not in dictionary
    [...]
    |Hochzeits
    ^               "Hochzeits" not in dictionary 
                    but let's check if it might be interfixed
    |Hochzeit,s
    ^               "Hochzeit" in dictionary 
                    "s" is an appended interfix  
                    
The above example results in a `CompleteWord` instance that has one `PartialWords` decompounding option with a `leftPart` of `hochzeit` with the interfix `s` and a `rightPart` of `messe` with no interfix.
`leftPart` and `rightPart` are instances of `CompleteWord` itself, resulting in the following (simplified) object structure:
    
    CompleteWord
        word: hochzeitsmesse
        interfix: (none)
        partialWords: 
            [
                PartialWords
                    leftPart: CompleteWord
                                word: hochzeit
                                interfix: s
                                partialWords: (none)
                    rightPart: CompleteWord
                                word: messe
                                interfix: (none)
                                partialWords: (none)                    
            ]
            
There are two important things to notice:

1. A `CompleteWord` can have more than one `PartialWords` decompounding option
2. A `PartialWord` consits of a left and a right part where each part is a `CompleteWord` again (hence might consist of `PartialWords` itself)

The algorithm needs this kind of flexibility because it has no information about the given word other than the word itself. 
This might lead to situations where multiple solutions exists. Example:

    Eis|becher
    ---^        "becher" in dictionary
    
    |Eis
    ^           "eis" is in dictionary
    
    |Ei,s       "ei" is also in dictionary and "s" is a valid interfix
    ^
    
Obviously, `Eis-Becher` (ice and cup) would be the correct solution, but since we cannot provide individual interfixes for every word (yet), 
we must assume that `Ei[s]-Becher` (interfix: s) (egg and cup) is also valid. To determine which solution is correct, a `DecompoundFilter` can be used. 

To be honest, I'm not too satisfied with this procedure, but I haven't come across a more suitable solution and wanted to make the `Decompounder` as flexible as possible so the end user
can customize it to his needs.
                    
##Pros and Cons

###Pros
 
 - "simple" solution
 - easy to implement and understand
 - flexible 
    
###Cons
 
 - high memory consumption (dictionary is loaded into memory [which also leads to a long startup time])
 - relatively slow (produces every possible decompounding option - only to throw most of them away later on)
 - ambigous results (Eis-Becher vs. Ei[s]-Becher)
   
##Similar projects

- [jwordsplitter](https://github.com/danielnaber/jwordsplitter) (Java)
- [ElasticSearch Analysis Decompound](https://github.com/jprante/elasticsearch-analysis-decompound) (Plugin for [ElasticSearch](http://www.elasticsearch.org))

##Frequently searched questions

- How can I split compound words in their individual parts?
- Where can I find an example for an open source PHP decompounder?
- How does a dictionary base word decompounder work?