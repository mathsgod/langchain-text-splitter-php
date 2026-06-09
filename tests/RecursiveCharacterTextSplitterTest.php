<?php

namespace Langchain\TextSplitter\Tests;

use Langchain\TextSplitter\RecursiveCharacterTextSplitter;
use PHPUnit\Framework\TestCase;

class RecursiveCharacterTextSplitterTest extends TestCase
{
    public function testSplitChineseTextMatchesReadmeExample(): void
    {
        $splitter = new RecursiveCharacterTextSplitter([
            'chunk_size' => 10,
            'chunk_overlap' => 2,
        ]);

        $text = "財政司長陳茂波明日公布新一份財政預算案，焦點之一是會否全面取消樓市逆周期措施。瑞銀發報告認為，在財赤及樓市疲軟下，預料港府會就樓市全面「撤辣」，但按2019年及去年的經驗，相信隨之而來的利好情緒只會維持4至16周，並續料今年樓價會下挫，最新預測會跌5%至10%，而早前的估計是跌少於10%。";

        $chunks = $splitter->splitText($text);

        $expected = [
            '財政司長陳茂波明日公',
            '日公布新一份財政預算',
            '預算案，焦點之一是會',
            '是會否全面取消樓市逆',
            '市逆周期措施。瑞銀發',
            '銀發報告認為，在財赤',
            '財赤及樓市疲軟下，預',
            '，預料港府會就樓市全',
            '市全面「撤辣」，但按',
            '但按2019年及去年',
            '去年的經驗，相信隨之',
            '隨之而來的利好情緒只',
            '緒只會維持4至16周',
            '6周，並續料今年樓價',
            '樓價會下挫，最新預測',
            '預測會跌5%至10%',
            '0%，而早前的估計是',
            '計是跌少於10%。',
        ];

        $this->assertSame($expected, $chunks);
    }

    public function testSplitsByDoubleNewlineFirst(): void
    {
        $splitter = new RecursiveCharacterTextSplitter([
            'chunk_size' => 15,
            'chunk_overlap' => 0,
        ]);

        $text = "Paragraph one.\n\nParagraph two.\n\nParagraph three.";
        $chunks = $splitter->splitText($text);

        $this->assertNotEmpty($chunks);
        // All three paragraphs should appear across the resulting chunks.
        $joined = implode(' ', $chunks);
        $this->assertStringContainsString('Paragraph one.', $joined);
        $this->assertStringContainsString('Paragraph two.', $joined);
        $this->assertStringContainsString('Paragraph three.', $joined);
    }

    public function testFallsBackToSingleNewlineWhenNoDoubleNewline(): void
    {
        $splitter = new RecursiveCharacterTextSplitter([
            'chunk_size' => 12,
            'chunk_overlap' => 0,
        ]);

        $text = "Line one.\nLine two.\nLine three.\nLine four.";
        $chunks = $splitter->splitText($text);

        $this->assertGreaterThan(1, count($chunks));
        foreach ($chunks as $chunk) {
            $this->assertStringNotContainsString("\n", $chunk);
        }
    }

    public function testFallsBackToSpaceWhenNoNewlines(): void
    {
        $splitter = new RecursiveCharacterTextSplitter([
            'chunk_size' => 12,
            'chunk_overlap' => 0,
        ]);

        $text = 'word1 word2 word3 word4 word5 word6';
        $chunks = $splitter->splitText($text);

        $this->assertGreaterThan(1, count($chunks));
        foreach ($chunks as $chunk) {
            $this->assertLessThanOrEqual(12, mb_strlen($chunk));
        }
    }

    public function testFallsBackToCharacterSplitForLongUnbrokenText(): void
    {
        $splitter = new RecursiveCharacterTextSplitter([
            'chunk_size' => 5,
            'chunk_overlap' => 0,
        ]);

        $text = 'abcdefghij';
        $chunks = $splitter->splitText($text);

        $this->assertGreaterThan(1, count($chunks));
        foreach ($chunks as $chunk) {
            $this->assertLessThanOrEqual(5, mb_strlen($chunk));
        }
    }

    public function testCustomSeparators(): void
    {
        $splitter = new RecursiveCharacterTextSplitter([
            'chunk_size' => 5,
            'chunk_overlap' => 0,
            'separators' => ['|'],
        ]);

        $text = 'aa|bb|cc|dd|ee';
        $chunks = $splitter->splitText($text);

        $this->assertGreaterThan(1, count($chunks));
        foreach ($chunks as $chunk) {
            $this->assertLessThanOrEqual(5, mb_strlen($chunk));
        }
    }

    public function testOverlapProducesOverlappingChunks(): void
    {
        $splitter = new RecursiveCharacterTextSplitter([
            'chunk_size' => 20,
            'chunk_overlap' => 5,
        ]);

        $text = 'alpha beta gamma delta epsilon zeta eta theta iota kappa';
        $chunks = $splitter->splitText($text);

        $this->assertGreaterThan(1, count($chunks));
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

    public function testEmptyStringReturnsEmptyArray(): void
    {
        $splitter = new RecursiveCharacterTextSplitter();
        $this->assertSame([], $splitter->splitText(''));
    }

    public function testAllTextFitsInOneChunk(): void
    {
        $splitter = new RecursiveCharacterTextSplitter([
            'chunk_size' => 1000,
            'chunk_overlap' => 0,
        ]);

        $text = 'short text';
        $chunks = $splitter->splitText($text);

        $this->assertCount(1, $chunks);
        $this->assertSame('short text', trim($chunks[0]));
    }

    public function testThrowsWhenOverlapLargerThanChunkSize(): void
    {
        $this->expectException(\Exception::class);
        new RecursiveCharacterTextSplitter([
            'chunk_size' => 5,
            'chunk_overlap' => 10,
        ]);
    }

    public function testDefaultChunkSizeAndOverlap(): void
    {
        $splitter = new RecursiveCharacterTextSplitter();
        $text = str_repeat('a', 1500);
        $chunks = $splitter->splitText($text);

        $this->assertGreaterThan(1, count($chunks));
        // Default chunk size is 1000, overlap 200
        foreach ($chunks as $chunk) {
            $this->assertLessThanOrEqual(1000, mb_strlen($chunk));
        }
    }
}
