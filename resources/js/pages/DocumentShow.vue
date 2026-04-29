<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';

type DocumentChunk = {
    id: number;
    chunk_index: number;
    content: string;
    metadata: Record<string, unknown> | null;
    created_at?: string;
};

type DocumentDetails = {
    id: number;
    title: string;
    file_path: string | null;
    mime_type: string | null;
    size: number | null;
    status: string;
    error_message: string | null;
    created_at: string;
    updated_at: string;
    chunks?: DocumentChunk[];
    chunks_count?: number;
};

const props = defineProps<{
    document: DocumentDetails;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Document',
                href: '',
            },
        ],
    },
});

const document = computed(() => props.document);
const chunks = computed(() => props.document.chunks ?? []);

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
    <Head :title="document.title" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <div class="flex items-start justify-between gap-4">
            <Heading
                :title="document.title"
                description="Inspect ingestion metadata and the resulting chunks."
            />

            <Button as-child variant="outline">
                <Link :href="dashboard()">Back to dashboard</Link>
            </Button>
        </div>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,360px)_minmax(0,1fr)]">
            <section class="rounded-lg border bg-card p-6">
                <Heading
                    variant="small"
                    title="Document metadata"
                    description="Stored file details and processing state."
                />

                <dl class="mt-6 space-y-4 text-sm">
                    <div class="grid gap-1">
                        <dt class="text-muted-foreground">Status</dt>
                        <dd>
                            <Badge :variant="badgeVariant(document.status)">
                                {{ document.status }}
                            </Badge>
                        </dd>
                    </div>

                    <div class="grid gap-1">
                        <dt class="text-muted-foreground">Stored path</dt>
                        <dd class="break-all text-foreground">
                            {{ document.file_path ?? 'No stored path' }}
                        </dd>
                    </div>

                    <div class="grid gap-1">
                        <dt class="text-muted-foreground">Mime type</dt>
                        <dd class="text-foreground">
                            {{ document.mime_type ?? 'Unknown mime type' }}
                        </dd>
                    </div>

                    <div class="grid gap-1">
                        <dt class="text-muted-foreground">File size</dt>
                        <dd class="text-foreground">
                            {{ formatFileSize(document.size) }}
                        </dd>
                    </div>

                    <div class="grid gap-1">
                        <dt class="text-muted-foreground">Chunks</dt>
                        <dd class="text-foreground">
                            {{
                                typeof document.chunks_count === 'number'
                                    ? document.chunks_count
                                    : chunks.length
                            }}
                        </dd>
                    </div>

                    <div class="grid gap-1">
                        <dt class="text-muted-foreground">Created</dt>
                        <dd class="text-foreground">
                            {{ formatDate(document.created_at) }}
                        </dd>
                    </div>

                    <div class="grid gap-1">
                        <dt class="text-muted-foreground">Updated</dt>
                        <dd class="text-foreground">
                            {{ formatDate(document.updated_at) }}
                        </dd>
                    </div>

                    <div v-if="document.error_message" class="grid gap-1">
                        <dt class="text-muted-foreground">Error</dt>
                        <dd class="rounded-md border border-destructive/30 bg-destructive/5 p-3 text-destructive">
                            {{ document.error_message }}
                        </dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-lg border bg-card p-6">
                <Heading
                    variant="small"
                    title="Chunks"
                    description="Review the extracted chunk output for this document."
                />

                <div
                    v-if="chunks.length === 0"
                    class="mt-6 rounded-md border border-dashed p-6 text-sm text-muted-foreground"
                >
                    No chunks available yet.
                </div>

                <div v-else class="mt-6 space-y-4">
                    <article
                        v-for="chunk in chunks"
                        :key="chunk.id"
                        class="rounded-md border p-4"
                    >
                        <div class="mb-3 flex items-center justify-between gap-4">
                            <p class="text-sm font-medium text-foreground">
                                Chunk {{ chunk.chunk_index }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ chunk.content.length }} chars
                            </p>
                        </div>

                        <pre class="overflow-x-auto whitespace-pre-wrap break-words text-sm leading-6 text-foreground">{{ chunk.content }}</pre>
                    </article>
                </div>
            </section>
        </div>
    </div>
</template>
