<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'document_id',
    'content',
    'chunk_index',
    'metadata',
])]
class DocumentChunk extends Model
{
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    protected function casts(): array
    {
        return [
            'chunk_index' => 'integer',
            'metadata' => 'array',
        ];
    }
}
