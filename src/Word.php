<?php

namespace JordJD\WordInfo;

use DaveChild\TextStatistics\Syllables;
use rapidweb\RWFileCache\RWFileCache;

class Word
{
    /** @var string|null */
    private $word;

    /** @var RWFileCache|null */
    private $cache;

    /**
     * Constructor.
     *
     * @param string $word
     *
     * @return void
     */
    public function __construct(string $word)
    {
        $this->word = $word;
        $this->setupCache();
    }

    /**
     * Convert class instance to string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->word;
    }

    /**
     * Set up cache.
     *
     * @return void
     */
    private function setupCache()
    {
        $this->cache = new RWFileCache();
        $this->cache->changeConfig(['cacheDirectory' => '/tmp/php-word-info-cache/']);
    }

    /**
     * Add the rhyme in word.
     *
     * @param bool $halfRhymes
     *
     * @return mixed
     */
    public function rhymes(bool $halfRhymes = false)
    {
        $cacheKey = $this->word.'.rhymes';

        if ($halfRhymes) {
            $cacheKey = $this->word.'.halfRhymes';
        }

        $value = $this->cache->get($cacheKey);

        if ($value) {
            return $value;
        }

        $response = file_get_contents('http://rhymebrain.com/talk?function=getRhymes&word='.urlencode($this->word));
        $responseItems = json_decode($response);

        $rhymes = [];

        foreach ($responseItems as $responseItem) {
            if ($halfRhymes) {
                if ($responseItem->score < 300) {
                    $rhymes[] = new self($responseItem->word);
                }
            } else {
                if ($responseItem->score == 300) {
                    $rhymes[] = new self($responseItem->word);
                }
            }
        }

        sort($rhymes);

        $this->cache->set($cacheKey, $rhymes);

        return $rhymes;
    }

    /**
     * Let word be half rhymes.
     *
     * @return mixed
     */
    public function halfRhymes()
    {
        return $this->rhymes(true);
    }

    /**
     * Syllables word.
     *
     * @return int
     */
    public function syllables()
    {
        return Syllables::syllableCount($this->word);
    }

    /**
     * Plural word.
     *
     * @return string
     */
    public function plural()
    {
        return (new Pluralizer($this))->pluralize();
    }

    /**
     * Singular word.
     *
     * @return string
     */
    public function singular()
    {
        return (new Pluralizer($this))->singularize();
    }

    /**
     * Check the word is offensive.
     *
     * @return bool
     */
    public function offensive()
    {
        return is_offensive($this->word);
    }

    /**
     * Portmanteaus word.
     *
     * @return mixed
     */
    public function portmanteaus()
    {
        $cacheKey = $this->word.'.portmanteaus';

        $value = $this->cache->get($cacheKey);

        if ($value) {
            return $value;
        }

        $response = file_get_contents('http://rhymebrain.com/talk?function=getPortmanteaus&word='.urlencode($this->word));
        $responseItems = json_decode($response);

        $portmanteaus = [];

        foreach ($responseItems as $responseItem) {
            $responseItemPortmanteaus = array_map(function ($portmanteauString) {
                return new Word($portmanteauString);
            }, explode(',', $responseItem->combined));

            $portmanteaus = array_merge($portmanteaus, $responseItemPortmanteaus);
        }

        sort($portmanteaus);

        $this->cache->set($cacheKey, $portmanteaus);

        return $portmanteaus;
    }
}
