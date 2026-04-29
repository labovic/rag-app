<?php

namespace App\Services;

class TextChunker
{
    /**
     * @return array<int, array{content: string, chunk_index: int, metadata: array<string, mixed>|null}>
     */
    public function chunk(string $text): array
    {
        $normalizedText = str_replace(["\r\n", "\r"], "\n", $text);
        $parts = preg_split("/\n\s*\n/", $normalizedText) ?: [];
        $chunks = [];

        foreach ($parts as $index => $part)
        {
            $content = trim($part);

            if ($content === '') continue;

            $chunks[] = [
                'content' => $content,
                'chunk_index' => count($chunks),
                'metadata' => null
            ];
        }


        return $chunks;
    }
}
