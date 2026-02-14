<?php

namespace JordJD\WordInfo;

use Doctrine\Common\Inflector\Inflector;

class Pluralizer
{
    /** @var array */
    private $irregular = [
        'goose' => 'geese',
    ];

    /** @var array */
    private $uncountable = [
        'audio',
        'education',
        'love',
        'pokemon',
        'mathematics',
    ];

    /** @var Word|null */
    private $word;

    /**
     * Constructor.
     *
     * @param Word $word
     *
     * @return void
     */
    public function __construct(Word $word)
    {
        $this->word = $word;
    }

    /**
     * Pluralize the word.
     *
     * @return Word
     */
    public function pluralize()
    {
        if ($this->isUncountable()) {
            return new Word($this->word);
        }

        foreach ($this->irregular as $singular => $plural) {
            if ($singular == $this->word) {
                return new Word($plural);
            }
        }

        $plural = Inflector::pluralize((string) $this->word);

        return new Word($plural);
    }

    /**
     * Singularize the word.
     *
     * @return Word
     */
    public function singularize()
    {
        if ($this->isUncountable()) {
            return new Word($this->word);
        }

        foreach ($this->irregular as $singular => $plural) {
            if ($plural == $this->word) {
                return new Word($singular);
            }
        }

        $singular = Inflector::singularize((string) $this->word);

        return new Word($singular);
    }

    /**
     * Check the word is uncountable, meaning there is no spelling difference between the plural and singular versions of the word.
     *
     * @return bool
     */
    private function isUncountable()
    {
        return in_array($this->word, $this->uncountable);
    }
}
