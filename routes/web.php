<?php

use App\Http\Controllers\DocumentController;
use App\Models\Document;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

Route::get('/dashboard', function () {
    return inertia('Dashboard', [
        'documents' => Document::query()
        ->where('user_id', auth()->id())
        ->withCount('chunks')
        ->latest()
        ->get([
            'id',
            'title',
            'file_path',
            'mime_type',
            'size',
            'status',
            'error_message',
            'created_at',
        ]),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/documents/{document}', function (Document $document) {
    abort_unless($document->user_id === auth()->id(), 403);

    $document->load([
        'chunks' => fn ($query) => $query->orderBy('chunk_index'),
    ])->loadCount('chunks');

    return inertia('DocumentShow', [
        'document' => $document,
    ]);
})->middleware('auth')->name('documents.show');

Route::post('/documents', [DocumentController::class, 'store'])
    ->middleware('auth')
    ->name('documents.store');

require __DIR__.'/settings.php';
