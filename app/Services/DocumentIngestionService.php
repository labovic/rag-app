<?php
namespace App\Services;

use App\Models\Document;
use Throwable;

class DocumentIngestionService
{
    public function __construct(
        protected DocumentTextExtractor $documentTextExtractor,
        protected TextChunker           $textChunker
    )
    {
    }

    public function ingest(Document $document): void
    {
        $document->update([
            'status' => 'processing',
            'error_message' => null,
        ]);

        try {
            $text = $this->documentTextExtractor->extract($document);
            $chunks = $this->textChunker->chunk($text);

            $document->chunks()->delete();
            $document->chunks()->createMany($chunks);

            $document->update([
                'status' => 'processed',
                'error_message' => null,
            ]);

        } catch (Throwable $exception) {
            $document->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

}
