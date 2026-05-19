<?php

namespace App\Http\Controllers;

use App\Models\DownloadDocument;
use App\Support\ContentSanitizer;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminDownloadDocumentController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.downloads.index', [
            'downloads' => DownloadDocument::query()
                ->when($request->filled('status'), fn ($query) => $query->where('status', $request->query('status')))
                ->orderByDesc('published_at')
                ->orderByDesc('id')
                ->paginate(12)
                ->withQueryString(),
            'statusOptions' => DownloadDocument::statuses(),
        ]);
    }

    public function create(): View
    {
        return view('admin.downloads.create', [
            'download' => new DownloadDocument([
                'status' => DownloadDocument::STATUS_DRAFT,
                'category' => 'Dokumen',
            ]),
            'statusOptions' => DownloadDocument::statuses(),
        ]);
    }

    public function store(Request $request, ContentSanitizer $sanitizer): RedirectResponse
    {
        $download = DownloadDocument::query()->create($this->validatedPayload($request, $sanitizer));

        return redirect()
            ->route('admin.downloads.edit', $download)
            ->with('status', 'Dokumen download berhasil ditambahkan.');
    }

    public function edit(DownloadDocument $download): View
    {
        return view('admin.downloads.edit', [
            'download' => $download,
            'statusOptions' => DownloadDocument::statuses(),
        ]);
    }

    public function update(Request $request, DownloadDocument $download, ContentSanitizer $sanitizer): RedirectResponse
    {
        $download->update($this->validatedPayload($request, $sanitizer, $download));

        return redirect()
            ->route('admin.downloads.edit', $download)
            ->with('status', 'Dokumen download berhasil diperbarui.');
    }

    public function destroy(DownloadDocument $download): RedirectResponse
    {
        $this->deleteStoredFile($download->file_path);
        $download->delete();

        return redirect()
            ->route('admin.downloads.index')
            ->with('status', 'Dokumen download berhasil dihapus.');
    }

    private function validatedPayload(Request $request, ContentSanitizer $sanitizer, ?DownloadDocument $download = null): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:500'],
            'slug' => [
                'nullable',
                'string',
                'max:220',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('download_documents', 'slug')->ignore($download?->id),
            ],
            'category' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'file' => ['nullable', 'file', 'max:51200'],
            'status' => ['required', Rule::in(array_keys(DownloadDocument::statuses()))],
            'published_at' => ['nullable', 'date'],
        ]);

        $payload = [
            'title' => trim($validated['title']),
            'slug' => ($validated['slug'] ?? null) ?: $this->generateUniqueSlug($validated['title'], $download),
            'category' => $sanitizer->title($validated['category'], 'Dokumen', 160),
            'description' => trim((string) ($validated['description'] ?? '')),
            'status' => $validated['status'],
            'published_at' => $this->resolvePublishedAt($validated['status'], $validated['published_at'] ?? null, $download),
            'published_by' => $validated['status'] === DownloadDocument::STATUS_PUBLISHED ? $request->user()?->id : null,
            'file_path' => $download?->file_path,
            'file_name' => $download?->file_name,
            'mime_type' => $download?->mime_type,
            'file_size' => $download?->file_size,
        ];

        $file = $request->file('file');

        if ($file instanceof UploadedFile) {
            $this->deleteStoredFile($download?->file_path);
            $payload['file_path'] = $file->store('downloads', 'public');
            $payload['file_name'] = $file->getClientOriginalName();
            $payload['mime_type'] = $file->getClientMimeType();
            $payload['file_size'] = $file->getSize();
        }

        return $payload;
    }

    private function generateUniqueSlug(string $title, ?DownloadDocument $download = null): string
    {
        $base = Str::slug($title) ?: 'dokumen';
        $slug = $base;
        $counter = 2;

        while (DownloadDocument::query()
            ->where('slug', $slug)
            ->when($download, fn ($query) => $query->whereKeyNot($download->id))
            ->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function resolvePublishedAt(string $status, ?string $publishedAt, ?DownloadDocument $download = null): ?Carbon
    {
        if ($status !== DownloadDocument::STATUS_PUBLISHED) {
            return null;
        }

        if ($publishedAt) {
            return Carbon::parse($publishedAt);
        }

        return $download?->published_at ?? now();
    }

    private function deleteStoredFile(?string $path): void
    {
        $path = trim((string) $path);

        if ($path === '' || Str::startsWith($path, ['http://', 'https://', '/'])) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
