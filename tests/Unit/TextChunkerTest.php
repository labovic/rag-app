<?php

use App\Services\TextChunker;

it('returns no chunks for empty text', function () {
    $chunks = (new TextChunker)->chunk("   \n\n   ");

    expect($chunks)->toBe([]);
});

it('packs small paragraphs into one chunk', function () {
    $text = implode("\n\n", [
        str_repeat('A', 300),
        str_repeat('B', 300),
        str_repeat('C', 300),
    ]);

    $chunks = (new TextChunker)->chunk($text);

    expect($chunks)->toHaveCount(1)
        ->and($chunks[0]['chunk_index'])->toBe(0)
        ->and(strlen($chunks[0]['content']))->toBeLessThan(1000)
        ->and($chunks[0]['metadata']['is_packed_chunk'])->toBeTrue()
        ->and($chunks[0]['metadata']['parts'])->toHaveCount(3);
});

it('flushes to a new chunk when adding the next paragraph would exceed the limit', function () {
    $text = implode("\n\n", [
        str_repeat('A', 600),
        str_repeat('B', 500),
        str_repeat('C', 200),
    ]);

    $chunks = (new TextChunker)->chunk($text);

    expect($chunks)->toHaveCount(2)
        ->and($chunks[0]['chunk_index'])->toBe(0)
        ->and($chunks[1]['chunk_index'])->toBe(1)
        ->and(strlen($chunks[0]['content']))->toBeLessThanOrEqual(1000)
        ->and(strlen($chunks[1]['content']))->toBeLessThanOrEqual(1000)
        ->and($chunks[0]['metadata']['parts'])->toHaveCount(1)
        ->and($chunks[1]['metadata']['parts'])->toHaveCount(2);
});

it('splits oversized paragraphs before packing', function () {
    $text = str_repeat('word ', 300);

    $chunks = (new TextChunker)->chunk($text);

    expect($chunks)->not->toBeEmpty();

    foreach ($chunks as $chunk) {
        expect(strlen($chunk['content']))->toBeLessThanOrEqual(1000);
    }

    $parts = collect($chunks)
        ->flatMap(fn ($chunk) => $chunk['metadata']['parts']);

    expect($parts->pluck('is_split_paragraph')->contains(true))->toBeTrue()
        ->and($parts->pluck('paragraph_index')->unique()->values()->all())->toBe([0])
        ->and($parts->pluck('paragraph_part_index')->values()->all())->toBe([0, 1]);
});

it('assigns sequential chunk indexes', function () {
    $text = implode("\n\n", [
        str_repeat('A', 900),
        str_repeat('B', 900),
        str_repeat('C', 900),
    ]);

    $chunks = (new TextChunker)->chunk($text);

    expect(array_column($chunks, 'chunk_index'))->toBe([0, 1, 2]);
});
