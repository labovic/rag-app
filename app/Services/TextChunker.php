<?php

namespace App\Services;

class TextChunker
{
    private int $maxChunkLength = 1000;

    /**
     * @return array<int, array{content: string, chunk_index: int, metadata: array<string, mixed>|null}>
     */
    public function chunk(string $text): array
    {
        $paragraphs = $this->splitIntoParagraphs($text);

        $parts = $this->splitParagraphIntoParts($paragraphs);
        $contents = $this->packPartsIntoChunks($parts);

        return $this->formatChunks($contents);

    }

    private function splitIntoParagraphs(string $text): array
    {
        $normalizedText = str_replace(["\r\n", "\r"], "\n", $text);
        $paragraphs = preg_split("/\n\s*\n/", $normalizedText) ?: [];

        $paragraphs = array_values(array_filter(
            array_map(fn ($p) => trim($p), $paragraphs),
            fn ($p) => $p !== ''
        ));

        return $paragraphs;
    }

    private function splitParagraphIntoParts(array $paragraphs): array
    {
        $parts = [];

        foreach ($paragraphs as $paragraphIndex => $paragraph) {
            $paragraphParts = strlen($paragraph) > $this->maxChunkLength
                ? $this->splitOversizedParagraph($paragraph, $this->maxChunkLength)
                : [$paragraph];

            $paragraphPartCount = count($paragraphParts);

            foreach ($paragraphParts as $paragraphPartIndex => $paragraphPart) {
                $parts[] = [
                    'content' => $paragraphPart,
                    'metadata' => [
                        'paragraph_index' => $paragraphIndex,
                        'paragraph_part_index' => $paragraphPartIndex,
                        'paragraph_part_count' => $paragraphPartCount,
                        'is_split_paragraph' => $paragraphPartCount > 1,
                        'content_length' => strlen($paragraphPart),
                    ],
                ];

            }
        }

        return $parts;
    }

    private function packPartsIntoChunks(array $parts): array
    {
        $chunks = [];

        $currentContent = '';
        $currentParts = [];

        foreach ($parts as $part) {
            $separator = $currentContent === '' ? '' : "\n\n";
            $candidate = $currentContent.$separator.$part['content'];

            if ($currentContent !== '' && strlen($candidate) > $this->maxChunkLength) {
                $chunks[] = [
                    'content' => $currentContent,
                    'metadata' => [
                        'parts' => $currentParts,
                        'is_packed_chunk' => count($currentParts) > 1,
                        'content_length' => strlen($currentContent),
                    ],
                ];

                $currentContent = $part['content'];
                $currentParts = [$part['metadata']];

                continue;
            }
            $currentContent = $candidate;
            $currentParts[] = $part['metadata'];
        }

        if ($currentContent !== '') {
            $chunks[] = [
                'content' => $currentContent,
                'metadata' => [
                    'parts' => $currentParts,
                    'is_packed_chunk' => count($currentParts) > 1,
                    'content_length' => strlen($currentContent),
                ],
            ];
        }

        return $chunks;
    }

    private function splitOversizedParagraph(string $paragraph, int $maxLength): array
    {
        $chunks = [];
        $remaining = trim($paragraph);

        while (strlen($remaining) > $maxLength) {
            $splitAt = $this->findSplitPosition($remaining, $maxLength);
            $chunk = trim(substr($remaining, 0, $splitAt));

            if ($chunk !== '') {
                $chunks[] = $chunk;
            }

            $remaining = trim(substr($remaining, $splitAt));
        }

        if ($remaining !== '') {
            $chunks[] = $remaining;
        }

        return $chunks;
    }

    private function findSplitPosition(string $text, int $maxLength): int
    {
        $window = substr($text, 0, $maxLength + 1);

        foreach (['. ', '! ', '? ', "\n"] as $boundary) {
            $position = strrpos($window, $boundary);

            if ($position !== false && $position > 0) {
                return $position + strlen($boundary);
            }
        }

        $position = strrpos(substr($text, 0, $maxLength), ' ');

        if ($position !== false && $position > 0) {
            return $position;
        }

        return $maxLength;
    }

    private function formatChunks(array $contents): array
    {
        $chunks = [];
        foreach (array_values($contents) as $chunkIndex => $content) {
            $chunks[] = [
                'content' => $content['content'],
                'chunk_index' => $chunkIndex,
                'metadata' => $content['metadata'],
            ];
        }

        return $chunks;
    }
}
