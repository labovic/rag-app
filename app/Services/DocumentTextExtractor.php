<?php
namespace App\Services;

use App\Models\Document;
use RuntimeException;
use Illuminate\Support\Facades\Storage;

class DocumentTextExtractor
{
    public function extract(Document $document): string
    {
        if (blank($document->file_path)) {
            throw new RuntimeException('File path is required.');
        }

        $disk = Storage::disk('local');

        if (! $disk->exists($document->file_path)) {
            throw new RuntimeException('File not found.');
        }

        return $disk->get($document->file_path);
    }
}
