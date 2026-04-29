<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentRequest;
use App\Jobs\ProcessUploadedDocumentJob;
use App\Models\Document;
use Illuminate\Http\RedirectResponse;

class DocumentController extends Controller
{
    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        $uploadedFile = $request->file('document');
        $path = $uploadedFile->store('documents', 'local');

        $document = Document::create([
            'user_id' => $request->user()->id,
            'title' => $request->input('title') ?: $uploadedFile->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $uploadedFile->getMimeType(),
            'size' => $uploadedFile->getSize(),
            'status' => 'uploaded',
        ]);

        ProcessUploadedDocumentJob::dispatch($document);
        return back();
    }
}
