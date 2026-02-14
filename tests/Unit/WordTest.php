<?php

use JordJD\WordInfo\Word;
use PHPUnit\Framework\TestCase;

final class WordTest extends TestCase
{
    private function wordsToStrings(array $words): array
    {
        return array_map(static function ($word): string {
            return (string) $word;
        }, $words);
    }

    public function testRhymes()
    {
        $rhymes = (new Word('cat'))->rhymes();
        $this->assertIsArray($rhymes);
        if (count($rhymes) === 0) {
            $this->markTestSkipped('No rhymes returned from external API.');
        }

        $this->assertContains('bat', $this->wordsToStrings($rhymes));
    }

    public function testHalfRhymes()
    {
        $rhymes = (new Word('violet'))->halfRhymes();
        $this->assertIsArray($rhymes);
        if (count($rhymes) === 0) {
            $this->markTestSkipped('No half-rhymes returned from external API.');
        }

        $this->assertContains('scientist', $this->wordsToStrings($rhymes));
    }

    public function testSyllables1()
    {
        $syllables = (new Word('hi'))->syllables();
        $expected = 1;

        $this->assertEquals($expected, $syllables);
    }

    public function testSyllables2()
    {
        $syllables = (new Word('hello'))->syllables();
        $expected = 2;

        $this->assertEquals($expected, $syllables);
    }

    public function testSyllables3()
    {
        $syllables = (new Word('happiness'))->syllables();
        $expected = 3;

        $this->assertEquals($expected, $syllables);
    }

    public function testOffensive1()
    {
        $offensive = (new Word('fuck'))->offensive();
        $expected = true;

        $this->assertEquals($expected, $offensive);
    }

    public function testOffensive2()
    {
        $offensive = (new Word('crap'))->offensive();
        $expected = true;

        $this->assertEquals($expected, $offensive);
    }

    public function testOffensive3()
    {
        $offensive = (new Word('shit'))->offensive();
        $expected = true;

        $this->assertEquals($expected, $offensive);
    }

    public function testOffensive4()
    {
        $offensive = (new Word('shitty'))->offensive();
        $expected = true;

        $this->assertEquals($expected, $offensive);
    }

    public function testNotOffensive()
    {
        $offensive = (new Word('cake'))->offensive();
        $expected = false;

        $this->assertEquals($expected, $offensive);
    }

    public function testPortmanteaus1()
    {
        $portmanteaus = (new Word('computer'))->portmanteaus();
        $this->assertIsArray($portmanteaus);
        if (count($portmanteaus) === 0) {
            $this->markTestSkipped('No portmanteaus returned from external API.');
        }

        $this->assertContains('computerise', $this->wordsToStrings($portmanteaus));
    }

    public function testPortmanteaus2()
    {
        $portmanteaus = (new Word('cheese'))->portmanteaus();
        $this->assertIsArray($portmanteaus);
        if (count($portmanteaus) === 0) {
            $this->markTestSkipped('No portmanteaus returned from external API.');
        }

        $this->assertContains('cheasy', $this->wordsToStrings($portmanteaus));
    }

    public function pluraliseProvider()
    {
        return [
            ['cat', 'cats'],
            ['mitten', 'mittens'],
            ['sausage', 'sausages'],
            ['child', 'children'],
            ['goose', 'geese'],
            ['person', 'people'],
            ['woman', 'women'],
            ['man', 'men'],
            ['audio', 'audio'],
            ['education', 'education'],
            ['rice', 'rice'],
            ['love', 'love'],
            ['pokemon', 'pokemon'],
            ['sheep', 'sheep'],
            ['sex', 'sexes'],
            ['mouse', 'mice'],
            ['mathematics', 'mathematics'],
            ['information', 'information'],
            ['tooth', 'teeth'],
        ];
    }

    /**
     * @dataProvider pluraliseProvider
     */
    public function testPluralise($singular, $plural)
    {
        $word = new Word($singular);

        $this->assertEquals($plural, $word->plural());
    }

    /**
     * @dataProvider pluraliseProvider
     */
    public function testSingularise($singular, $plural)
    {
        $word = new Word($plural);

        $this->assertEquals($singular, $word->singular());
    }
}
