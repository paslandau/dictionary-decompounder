#DictionaryDecompounder
[![Build Status](https://travis-ci.org/paslandau/DictionaryDecompounder.svg?branch=master)](https://travis-ci.org/paslandau/DictionaryDecompounder)

A dictionary based decompounder that recognizes compound words like 'Herrenschuh' and splits them into its individual parts, e.g. 'Herren' and 'Schuh'.

##Description

...

###Basic Usage
```php


...

```

**Output**

    ...

###Examples

See `demo*.php` files.

##Requirements

- PHP >= 5.5

##Installation

The recommended way to install DictionaryDecompounder is through [Composer](http://getcomposer.org/).

    curl -sS https://getcomposer.org/installer | php

Next, update your project's composer.json file to include DictionaryDecompounder:

    {
        "repositories": [
            {
                "type": "git",
                "url": "https://github.com/paslandau/DictionaryDecompounder.git"
            }
        ],
        "require": {
             "paslandau/DictionaryDecompounder": "~0"
        }
    }

After installing, you need to require Composer's autoloader:
```php

require 'vendor/autoload.php';
```

##General workflow and customization options

...

###Settings

...
    
##Similar projects

- [jwordsplitter](https://github.com/danielnaber/jwordsplitter) (Java)
- [ElasticSearch Analysis Decompound](https://github.com/jprante/elasticsearch-analysis-decompound) (Plugin for [ElasticSearch](http://www.elasticsearch.org))

##Frequently searched questions

- How can I split compound words in their individual parts?
- Where can I find an example for an open source PHP decompounder?