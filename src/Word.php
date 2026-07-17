<?php

namespace JordJD\WordInfo;

use DaveChild\TextStatistics\Syllables;
use JordJD\DOFileCache\DOFileCache;

class Word
{
    /** @var string|null */
    private $word;

    /** @var DOFileCache|null */
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
        $this->cache = new DOFileCache();
        // Use a cache directory name that is stable across installs but doesn't
        // collide with historical caches that may contain serialized objects from
        // previous namespaces.
        $this->cache->changeConfig([
            'cacheDirectory' => sys_get_temp_dir().DIRECTORY_SEPARATOR.'jordjd-php-word-info-cache'.DIRECTORY_SEPARATOR,
        ]);
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

        if ($value !== false) {
            // New format: cached as an array of strings.
            if (is_array($value) && (count($value) === 0 || is_string($value[0]))) {
                return array_map(static function (string $word): self {
                    return new self($word);
                }, $value);
            }

            // Old format: cached as an array of Word objects. If the namespace has
            // changed since the cache was written, unserialize() will yield
            // __PHP_Incomplete_Class instances, so treat that cache as invalid.
            if (is_array($value)) {
                $allWords = true;
                foreach ($value as $item) {
                    if (!$item instanceof self) {
                        $allWords = false;
                        break;
                    }
                }
                if ($allWords) {
                    return $value;
                }
            }

            $this->cache->delete($cacheKey);
        }

        $responseItems = $this->requestRhymeBrain('getRhymes');

        $rhymeWords = [];

        foreach ($responseItems as $responseItem) {
            if ($halfRhymes) {
                if ($responseItem->score < 300) {
                    $rhymeWords[] = $responseItem->word;
                }
            } else {
                if ($responseItem->score == 300) {
                    $rhymeWords[] = $responseItem->word;
                }
            }
        }

        sort($rhymeWords);

        $this->cache->set($cacheKey, $rhymeWords);

        return array_map(static function (string $word): self {
            return new self($word);
        }, $rhymeWords);
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

        if ($value !== false) {
            if (is_array($value) && (count($value) === 0 || is_string($value[0]))) {
                return array_map(static function (string $word): self {
                    return new self($word);
                }, $value);
            }

            if (is_array($value)) {
                $allWords = true;
                foreach ($value as $item) {
                    if (!$item instanceof self) {
                        $allWords = false;
                        break;
                    }
                }
                if ($allWords) {
                    return $value;
                }
            }

            $this->cache->delete($cacheKey);
        }

        $responseItems = $this->requestRhymeBrain('getPortmanteaus');

        $portmanteauWords = [];

        foreach ($responseItems as $responseItem) {
            foreach (explode(',', $responseItem->combined) as $portmanteauString) {
                $portmanteauString = trim($portmanteauString);
                if ($portmanteauString === '') {
                    continue;
                }
                $portmanteauWords[] = $portmanteauString;
            }
        }

        sort($portmanteauWords);

        $this->cache->set($cacheKey, $portmanteauWords);

        return array_map(static function (string $word): self {
            return new self($word);
        }, $portmanteauWords);
    }

    /**
     * @return array<int, object>
     */
    private function requestRhymeBrain(string $function): array
    {
        $url = 'https://rhymebrain.com/talk?function='.urlencode($function).'&word='.urlencode($this->word);
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'ignore_errors' => true,
            ],
        ]);
        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            return [];
        }

        $items = json_decode($response);

        return is_array($items) ? $items : [];
    }
}
