<?php

namespace Langchain\TextSplitter\Tests;

use Langchain\TextSplitter\CharacterTextSplitter;
use PHPUnit\Framework\TestCase;

class CharacterTextSplitterTest extends TestCase
{
    public function testSplitsByDoubleNewlineByDefault(): void
    {
        $splitter = new CharacterTextSplitter([
            'chunk_size' => 12,
            'chunk_overlap' => 0,
        ]);

        $text = "Paragraph one.\n\nParagraph two.\n\nParagraph three.";
        $chunks = $splitter->splitText($text);

        $this->assertCount(3, $chunks);
        foreach ($chunks as $chunk) {
            $this->assertStringNotContainsString("\n\n", $chunk);
        }
    }

    public function testMergesIntoChunksWithinSizeLimit(): void
    {
        $splitter = new CharacterTextSplitter([
            'chunk_size' => 10,
            'chunk_overlap' => 0,
            'separator' => ' ',
        ]);

        $text = 'a b c d e f g h i j k l m n o p';
        $chunks = $splitter->splitText($text);

        $this->assertGreaterThan(1, count($chunks));
        foreach ($chunks as $chunk) {
            $this->assertLessThanOrEqual(10, mb_strlen($chunk));
        }
    }

    public function testOverlapProducesOverlappingChunks(): void
    {
        $splitter = new CharacterTextSplitter([
            'chunk_size' => 11,
            'chunk_overlap' => 5,
            'separator' => ' ',
        ]);

        $text = 'alpha beta gamma delta epsilon zeta eta theta';
        $chunks = $splitter->splitText($text);

        $this->assertGreaterThan(1, count($chunks));
        // At least one pair of adjacent chunks should share content (overlap)
        $foundOverlap = false;
        for ($i = 1; $i < count($chunks); $i++) {
            $prevWords = explode(' ', $chunks[$i - 1]);
            $nextWords = explode(' ', $chunks[$i]);
            foreach ($prevWords as $w) {
                if (in_array($w, $nextWords, true)) {
                    $foundOverlap = true;
                    break 2;
                }
            }
        }
        $this->assertTrue($foundOverlap, 'Expected overlap between adjacent chunks');
    }

    public function testCustomSeparator(): void
    {
        $splitter = new CharacterTextSplitter([
            'chunk_size' => 5,
            'chunk_overlap' => 0,
            'separator' => '|',
        ]);

        $text = 'part1|part2|part3|part4|part5';
        $chunks = $splitter->splitText($text);

        $this->assertGreaterThan(1, count($chunks));
        foreach ($chunks as $chunk) {
            $this->assertStringNotContainsString('|', $chunk);
        }
    }

    public function testMultibyteText(): void
    {
        $splitter = new CharacterTextSplitter([
            'chunk_size' => 10,
            'chunk_overlap' => 2,
            'separator' => '，',
        ]);

        $text = '財政司長陳茂波明日公布，新一份財政預算案，焦點之一是會否全面取消';
        $chunks = $splitter->splitText($text);

        $this->assertNotEmpty($chunks);
        // Length is measured in characters (not bytes) and may slightly exceed
        // chunk_size because the merge condition adds separator length first.
        foreach ($chunks as $chunk) {
            $this->assertLessThanOrEqual(13, mb_strlen($chunk));
        }
    }

    public function testEmptyStringReturnsEmptyArray(): void
    {
        $splitter = new CharacterTextSplitter();
        $this->assertSame([], $splitter->splitText(''));
    }

    public function testThrowsWhenOverlapLargerThanChunkSize(): void
    {
        $this->expectException(\Exception::class);
        new CharacterTextSplitter([
            'chunk_size' => 5,
            'chunk_overlap' => 10,
        ]);
    }
}
