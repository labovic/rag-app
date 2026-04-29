<?php

namespace App\Jobs;

use App\Models\Document;
use App\Services\DocumentIngestionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessUploadedDocumentJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Document $document
    )
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(DocumentIngestionService $ingestionService): void
    {
        $ingestionService->ingest($this->document);
    }
}
