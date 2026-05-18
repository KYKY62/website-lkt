<?php

namespace App\Http\Controllers;

use App\Models\NewsArticle;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminNewsController extends Controller
{
    public function index(): View
    {
        return view('admin.news.index', [
            'articles' => NewsArticle::query()
                ->with('publishedBy')
                ->latest('updated_at')
                ->paginate(12),
        ]);
    }

    public function create(): View
    {
        return view('admin.news.create', [
            'article' => new NewsArticle([
                'status' => 'draft',
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $article = NewsArticle::create($this->validatedPayload($request));

        return redirect()
            ->route('admin.news.edit', $article)
            ->with('status', 'Berita baru berhasil disimpan.');
    }

    public function edit(NewsArticle $news): View
    {
        return view('admin.news.edit', [
            'article' => $news,
        ]);
    }

    public function update(Request $request, NewsArticle $news): RedirectResponse
    {
        $news->update($this->validatedPayload($request, $news));

        return redirect()
            ->route('admin.news.edit', $news)
            ->with('status', 'Perubahan berita berhasil disimpan.');
    }

    public function destroy(NewsArticle $news): RedirectResponse
    {
        $this->deleteGalleryFiles($news->image_urls ?? []);
        $news->delete();

        return redirect()
            ->route('admin.news.index')
            ->with('status', 'Berita berhasil dihapus.');
    }

    private function validatedPayload(Request $request, ?NewsArticle $article = null): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'slug' => [
                'nullable',
                'string',
                'max:180',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('news_articles', 'slug')->ignore($article?->id),
            ],
            'category' => ['required', 'string', 'max:120'],
            'excerpt' => ['required', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'images' => ['nullable', 'array'],
            'images.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'existing_image_order' => ['nullable', 'array'],
            'existing_image_order.*' => ['nullable', 'string', 'max:500'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'published_at' => ['nullable', 'date'],
        ]);

        $validated['image_urls'] = $this->resolveStoredImages(
            $request->file('images', []),
            $article?->image_urls ?? [],
            $validated['existing_image_order'] ?? []
        );
        $validated['slug'] = $validated['slug'] ?: $this->generateUniqueSlug($validated['title'], $article);
        $validated['content'] = trim(strip_tags(
            $validated['content'],
            '<p><br><strong><b><em><i><u><ul><ol><li><a><h2><h3><h4><blockquote>'
        ));
        $validated['cover_image_url'] = $validated['image_urls'][0] ?? null;
        $validated['published_at'] = $this->resolvePublishedAt(
            $validated['status'],
            $validated['published_at'] ?? null,
            $article
        );
        $validated['published_by'] = $validated['status'] === 'published'
            ? $request->user()?->id
            : null;

        return $validated;
    }

    private function resolveStoredImages(array $uploadedImages, array $existingImages = [], array $requestedExistingOrder = []): array
    {
        $storedImages = Collection::make($uploadedImages)
            ->filter(fn ($file) => $file instanceof UploadedFile)
            ->map(fn (UploadedFile $file): string => $file->store('news-gallery', 'public'))
            ->filter()
            ->values()
            ->all();

        if ($storedImages !== []) {
            $this->deleteGalleryFiles($existingImages);

            return $storedImages;
        }

        $existingCollection = Collection::make($existingImages)
            ->map(fn (?string $path): string => trim((string) $path))
            ->filter()
            ->values();

        $requestedOrder = Collection::make($requestedExistingOrder)
            ->map(fn (?string $path): string => trim((string) $path))
            ->filter()
            ->values();

        if ($requestedOrder->isNotEmpty() || $existingCollection->isNotEmpty()) {
            $keptImages = $requestedOrder
                ->filter(fn (string $path): bool => $existingCollection->contains($path))
                ->values()
                ->all();

            $removedImages = $existingCollection
                ->reject(fn (string $path): bool => in_array($path, $keptImages, true))
                ->values()
                ->all();

            $this->deleteGalleryFiles($removedImages);

            return $keptImages;
        }

        return $existingCollection
            ->values()
            ->all();
    }

    private function deleteGalleryFiles(array $paths): void
    {
        Collection::make($paths)
            ->map(fn (?string $path): string => trim((string) $path))
            ->filter()
            ->reject(fn (string $path): bool => Str::startsWith($path, ['http://', 'https://', '/']))
            ->each(fn (string $path) => Storage::disk('public')->delete($path));
    }

    private function generateUniqueSlug(string $title, ?NewsArticle $article = null): string
    {
        $baseSlug = Str::slug($title) ?: 'berita';
        $slug = $baseSlug;
        $counter = 2;

        while (
            NewsArticle::query()
                ->when($article, fn ($query) => $query->whereKeyNot($article->id))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function resolvePublishedAt(string $status, ?string $publishedAt, ?NewsArticle $article = null): ?Carbon
    {
        if ($status !== 'published') {
            return null;
        }

        if ($publishedAt) {
            return Carbon::parse($publishedAt);
        }

        if ($article?->published_at) {
            return $article->published_at;
        }

        return now();
    }
}
