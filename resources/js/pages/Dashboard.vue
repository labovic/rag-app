<script setup lang="ts">
import { computed } from 'vue';
import { Form, Head, Link } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard } from '@/routes';

type DocumentListItem = {
    id: number;
    title: string;
    file_path: string | null;
    mime_type: string | null;
    size: number | null;
    status: string;
    error_message: string | null;
    created_at: string;
    chunks_count?: number;
};

const props = defineProps<{
    documents?: DocumentListItem[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

const documents = computed(() => props.documents ?? []);

const badgeVariant = (status: string) => {
    switch (status) {
        case 'processed':
            return 'default';
        case 'failed':
            return 'destructive';
        default:
            return 'secondary';
    }
};

const formatFileSize = (size: number | null) => {
    if (size === null) {
        return 'Unknown size';
    }

    if (size < 1024) {
        return `${size} B`;
    }

    if (size < 1024 * 1024) {
        return `${(size / 1024).toFixed(1)} KB`;
    }

    return `${(size / (1024 * 1024)).toFixed(1)} MB`;
};

const formatDate = (value: string) =>
    new Intl.DateTimeFormat(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <div class="grid gap-6 lg:grid-cols-[minmax(0,32rem)_minmax(0,1fr)]">
            <section class="rounded-lg border bg-card p-6">
                <Heading
                    variant="small"
                    title="Upload document"
                    description="Upload a plain text file to start the ingestion pipeline."
                />

                <Form
                    action="/documents"
                    method="post"
                    enctype="multipart/form-data"
                    class="mt-6 space-y-6"
                    v-slot="{ errors, processing }"
                >
                    <div class="grid gap-2">
                        <Label for="title">Title</Label>
                        <Input
                            id="title"
                            name="title"
                            type="text"
                            placeholder="Optional document title"
                        />
                        <InputError :message="errors.title" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="document">Text file</Label>
                        <input
                            id="document"
                            name="document"
                            type="file"
                            accept=".txt,text/plain"
                            class="file:text-foreground placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground dark:bg-input/30 border-input h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none file:inline-flex file:h-7 file:border-0 file:bg-transparent file:text-sm file:font-medium disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]"
                        />
                        <InputError :message="errors.document" />
                    </div>

                    <div class="flex items-center gap-3">
                        <Button type="submit" :disabled="processing">
                            {{ processing ? 'Uploading...' : 'Upload document' }}
                        </Button>
                    </div>
                </Form>
            </section>

            <section class="rounded-lg border bg-card p-6">
                <Heading
                    variant="small"
                    title="Pipeline"
                    description="Files are stored first, then processed by the queue worker in the background."
                />

                <div class="mt-6 space-y-3 text-sm text-muted-foreground">
                    <p>1. Upload stores the file on the local disk.</p>
                    <p>2. A document row is created with status <code>uploaded</code>.</p>
                    <p>3. The queued job moves it to <code>processing</code>.</p>
                    <p>4. Extraction and chunking run in the background.</p>
                    <p>5. On success the status becomes <code>processed</code>.</p>
                </div>
            </section>
        </div>

        <section class="rounded-lg border bg-card p-6">
            <Heading
                variant="small"
                title="Documents"
                description="Recent uploaded documents and their processing state."
            />

            <div
                v-if="documents.length === 0"
                class="mt-6 rounded-md border border-dashed p-6 text-sm text-muted-foreground"
            >
                No documents uploaded yet.
            </div>

            <div v-else class="mt-6 overflow-hidden rounded-md border">
                <div
                    class="grid grid-cols-[minmax(0,2fr)_120px_120px_160px] gap-4 border-b bg-muted/40 px-4 py-3 text-xs font-medium uppercase tracking-wide text-muted-foreground"
                >
                    <div>Document</div>
                    <div>Status</div>
                    <div>Size</div>
                    <div>Created</div>
                </div>

                <Link
                    v-for="document in documents"
                    :key="document.id"
                    :href="`/documents/${document.id}`"
                    class="grid grid-cols-[minmax(0,2fr)_120px_120px_160px] gap-4 border-b px-4 py-4 transition-colors hover:bg-muted/30 last:border-b-0"
                >
                    <div class="min-w-0 space-y-1">
                        <p class="truncate text-sm font-medium text-foreground">
                            {{ document.title }}
                        </p>
                        <p class="truncate text-xs text-muted-foreground">
                            {{ document.file_path ?? 'No stored path' }}
                        </p>
                        <p
                            v-if="document.error_message"
                            class="text-xs text-destructive"
                        >
                            {{ document.error_message }}
                        </p>
                        <p
                            v-else-if="typeof document.chunks_count === 'number'"
                            class="text-xs text-muted-foreground"
                        >
                            {{ document.chunks_count }} chunks
                        </p>
                    </div>

                    <div class="flex items-start">
                        <Badge :variant="badgeVariant(document.status)">
                            {{ document.status }}
                        </Badge>
                    </div>

                    <div class="text-sm text-muted-foreground">
                        {{ formatFileSize(document.size) }}
                    </div>

                    <div class="text-sm text-muted-foreground">
                        {{ formatDate(document.created_at) }}
                    </div>
                </Link>
            </div>
        </section>
    </div>
</template>
