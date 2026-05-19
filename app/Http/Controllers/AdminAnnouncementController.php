<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Support\ContentSanitizer;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminAnnouncementController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.announcements.index', [
            'announcements' => Announcement::query()
                ->with('publishedBy')
                ->when($request->filled('status'), fn ($query) => $query->where('status', $request->query('status')))
                ->orderByDesc('published_at')
                ->orderByDesc('id')
                ->paginate(12)
                ->withQueryString(),
            'statusOptions' => Announcement::statuses(),
        ]);
    }

    public function create(): View
    {
        return view('admin.announcements.create', [
            'announcement' => new Announcement([
                'status' => Announcement::STATUS_DRAFT,
                'category' => 'Umum',
            ]),
            'statusOptions' => Announcement::statuses(),
        ]);
    }

    public function store(Request $request, ContentSanitizer $sanitizer): RedirectResponse
    {
        $announcement = Announcement::query()->create($this->validatedPayload($request, $sanitizer));

        return redirect()
            ->route('admin.announcements.edit', $announcement)
            ->with('status', 'Pengumuman berhasil ditambahkan.');
    }

    public function edit(Announcement $announcement): View
    {
        return view('admin.announcements.edit', [
            'announcement' => $announcement,
            'statusOptions' => Announcement::statuses(),
        ]);
    }

    public function update(Request $request, Announcement $announcement, ContentSanitizer $sanitizer): RedirectResponse
    {
        $announcement->update($this->validatedPayload($request, $sanitizer, $announcement));

        return redirect()
            ->route('admin.announcements.edit', $announcement)
            ->with('status', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $this->deleteStoredFile($announcement->file_path);
        $announcement->delete();

        return redirect()
            ->route('admin.announcements.index')
            ->with('status', 'Pengumuman berhasil dihapus.');
    }

    private function validatedPayload(Request $request, ContentSanitizer $sanitizer, ?Announcement $announcement = null): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:500'],
            'slug' => [
                'nullable',
                'string',
                'max:220',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('announcements', 'slug')->ignore($announcement?->id),
            ],
            'category' => ['required', 'string', 'max:160'],
            'content' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'max:20480'],
            'status' => ['required', Rule::in(array_keys(Announcement::statuses()))],
            'published_at' => ['nullable', 'date'],
        ]);

        $payload = [
            'title' => trim($validated['title']),
            'slug' => ($validated['slug'] ?? null) ?: $this->generateUniqueSlug($validated['title'], $announcement),
            'category' => $sanitizer->title($validated['category'], 'Umum', 160),
            'content' => $sanitizer->html($validated['content'] ?? ''),
            'status' => $validated['status'],
            'published_at' => $this->resolvePublishedAt($validated['status'], $validated['published_at'] ?? null, $announcement),
            'published_by' => $validated['status'] === Announcement::STATUS_PUBLISHED ? $request->user()?->id : null,
            'file_path' => $announcement?->file_path,
            'file_name' => $announcement?->file_name,
            'mime_type' => $announcement?->mime_type,
            'file_size' => $announcement?->file_size,
        ];

        $file = $request->file('file');

        if ($file instanceof UploadedFile) {
            $this->deleteStoredFile($announcement?->file_path);
            $payload['file_path'] = $file->store('announcements', 'public');
            $payload['file_name'] = $file->getClientOriginalName();
            $payload['mime_type'] = $file->getClientMimeType();
            $payload['file_size'] = $file->getSize();
        }

        return $payload;
    }

    private function generateUniqueSlug(string $title, ?Announcement $announcement = null): string
    {
        $base = Str::slug($title) ?: 'pengumuman';
        $slug = $base;
        $counter = 2;

        while (Announcement::query()
            ->where('slug', $slug)
            ->when($announcement, fn ($query) => $query->whereKeyNot($announcement->id))
            ->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function resolvePublishedAt(string $status, ?string $publishedAt, ?Announcement $announcement = null): ?Carbon
    {
        if ($status !== Announcement::STATUS_PUBLISHED) {
            return null;
        }

        if ($publishedAt) {
            return Carbon::parse($publishedAt);
        }

        return $announcement?->published_at ?? now();
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
