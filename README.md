# rag-app

A Laravel learning project for building a backend-focused RAG AI agent step by step.

The current focus is the document ingestion pipeline, using Laravel jobs, service classes, and database-backed queues before adding embeddings, vector search, and chat.

## Project goals

This project is for practicing:
- Queues and jobs
- Service-class based application design
- AI integration in Laravel
- Document processing
- Text chunking
- Embeddings later
- Vector search later with pgvector

## Current stack

- Laravel
- Vue starter kit
- Laravel built-in auth
- PostgreSQL
- Laravel AI package
- Database queue driver

## Current status

Already in place:
- Laravel project scaffolded
- Vue starter kit installed
- Built-in auth available
- PostgreSQL configured
- Laravel AI package installed manually
- `agent_conversations` migration already published and migrated successfully

Current queue target:
- `QUEUE_CONNECTION=database`

## MVP scope

First implementation supports plain text files only.

Initial pipeline:
1. User uploads a document
2. Laravel stores document metadata
3. A queued job processes the document
4. A service extracts text from the file
5. A service chunks the text
6. Chunks are stored in the database
7. Later phases add embeddings and RAG chat

## Planned backend architecture

### Models

#### `Document`
Stores uploaded document metadata and processing state.

Expected fields:
- `id`
- `user_id` nullable foreign key
- `title`
- `file_path` nullable
- `mime_type` nullable
- `size` nullable unsigned big integer
- `status` default `uploaded`
- `error_message` nullable
- timestamps

#### `DocumentChunk`
Stores chunked text derived from a processed document.

Expected fields:
- `id`
- `document_id` foreign key with cascade delete
- `content` long text
- `chunk_index` unsigned integer
- `metadata` nullable json
- timestamps

### Relationships

- `Document` has many `DocumentChunk`
- `DocumentChunk` belongs to `Document`

### Jobs

#### `ProcessUploadedDocumentJob`
Responsible for background processing after upload.

Expected responsibilities:
- Load the document
- Mark processing status
- Extract text
- Chunk text
- Persist chunks
- Mark success or failure

### Services

#### `DocumentTextExtractor`
Responsible for reading the uploaded file and returning raw text.

First version:
- Support plain text files only
- Reject unsupported file types cleanly

#### `TextChunker`
Responsible for splitting raw text into ordered chunks.

First version:
- Deterministic text chunking for storage
- Simple chunk sizing strategy is enough for MVP

#### `DocumentIngestionService`
Responsible for orchestration of ingestion logic.

Expected responsibilities:
- Validate process assumptions
- Call text extraction
- Call chunking
- Store chunk rows
- Keep controller logic thin

## Implementation principles

- Keep controllers thin
- Put orchestration in jobs and services
- Keep document ingestion backend-first
- Do not add embeddings yet
- Do not add vector search yet
- Support only `.txt` files in the first pass
- Build for extension toward pgvector-based retrieval later

## Suggested processing states

Useful `documents.status` values:
- `uploaded`
- `processing`
- `processed`
- `failed`

## Local setup

### 1. Install dependencies

```bash
composer install
npm install
```

### 2. Configure environment

Make sure `.env` is configured for:
- PostgreSQL
- database queue driver
- filesystem settings for uploads
- Laravel AI package settings as needed

Important queue setting:

```env
QUEUE_CONNECTION=database
```

### 3. Run migrations

```bash
php artisan migrate
```

### 4. Run queue worker

```bash
php artisan queue:work
```

### 5. Run the app

```bash
composer run dev
```

## Near-term implementation checklist

- [ ] Create `documents` migration
- [ ] Create `document_chunks` migration
- [ ] Create `Document` model
- [ ] Create `DocumentChunk` model
- [ ] Create model relationships
- [ ] Create `ProcessUploadedDocumentJob`
- [ ] Create `DocumentTextExtractor`
- [ ] Create `TextChunker`
- [ ] Create `DocumentIngestionService`
- [ ] Add plain text upload flow
- [ ] Persist chunks from processed text

## Later phases

After the ingestion pipeline is stable:
- Generate embeddings for chunks
- Store vectors with pgvector
- Add semantic retrieval
- Add chat over retrieved context
- Add agent workflows on top of RAG

## Notes

This repository is intentionally being built in small steps. The goal is to understand Laravel architecture around AI and RAG systems, not to skip directly to a full production implementation.
