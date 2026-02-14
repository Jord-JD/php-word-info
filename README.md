# PHP Word Info

[![Build Status](https://travis-ci.org/Jord-JD/php-word-info.svg?branch=master)](https://travis-ci.org/Jord-JD/php-word-info)
[![Coverage Status](https://coveralls.io/repos/github/Jord-JD/php-word-info/badge.svg?branch=master)](https://coveralls.io/github/Jord-JD/php-word-info?branch=master)
[![StyleCI](https://styleci.io/repos/118921741/shield?branch=master)](https://styleci.io/repos/118921741)

This PHP library can be used to look up information about a word, including the following.

* Rhymes
* Half rhymes
* Number of syllables
* Offensive or not
* Portmanteaus
* Plural / singular

## Installation

PHP Word Info can be easily installed using Composer. Just run the following command from the root of your project.

```
composer require jord-jd/php-word-info
```

If you have never used the Composer dependency manager before, head to the [Composer website](https://getcomposer.org/) for more information on how to get started.

## Usage

To use PHP Word Info, you must first create a new `Word` object. You can then call any of the `Word` object methods, as shown below.

```php
$word = new Word('cat');

$rhymes = $word->rhymes();
$halfRhymes = $word->halfRhymes();
$portmanteaus = $word->portmanteaus();

$numberOfSyllables = $word->syllables();    // Returns an integer
$isOffensive = $word->offensive();          // Returns true/false
$plural = $word->plural();                  // Returns `Word` object
$singular = $word->singular();              // Returns `Word` object
```

Most methods will return an array of `Word` objects, unless specified otherwise.
