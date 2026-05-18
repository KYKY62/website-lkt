<?php

namespace App\Http\Controllers;

use App\Models\StaticPage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminStaticPageController extends Controller
{
    public function index(): View
    {
        return view('admin.pages.index', [
            'pages' => StaticPage::query()
                ->latest('updated_at')
                ->paginate(12),
        ]);
    }

    public function create(): View
    {
        return view('admin.pages.create', [
            'page' => new StaticPage([
                'status' => 'draft',
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $page = StaticPage::query()->create($this->validatedPayload($request));

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('status', 'Halaman statis berhasil ditambahkan.');
    }

    public function edit(StaticPage $page): View
    {
        return view('admin.pages.edit', [
            'page' => $page,
        ]);
    }

    public function update(Request $request, StaticPage $page): RedirectResponse
    {
        $page->update($this->validatedPayload($request, $page));

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('status', 'Halaman statis berhasil diperbarui.');
    }

    public function destroy(StaticPage $page): RedirectResponse
    {
        $page->delete();

        return redirect()
            ->route('admin.pages.index')
            ->with('status', 'Halaman statis berhasil dihapus.');
    }

    private function validatedPayload(Request $request, ?StaticPage $page = null): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'slug' => [
                'nullable',
                'string',
                'max:180',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('static_pages', 'slug')->ignore($page?->id),
            ],
            'path' => ['nullable', 'string', 'max:220'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'status' => ['required', Rule::in(['draft', 'published'])],
        ]);

        $validated['slug'] = $validated['slug'] ?: $this->uniqueSlug($validated['title'], $page);
        $validated['path'] = $this->resolvePath($validated['path'] ?? null, $validated['slug'], $page);
        $validated['content'] = trim(strip_tags(
            $validated['content'],
            '<p><br><strong><b><em><i><u><ul><ol><li><a><h2><h3><h4><blockquote>'
        ));

        return $validated;
    }

    private function uniqueSlug(string $title, ?StaticPage $page = null): string
    {
        $baseSlug = Str::slug($title) ?: 'halaman';
        $slug = $baseSlug;
        $counter = 2;

        while (
            StaticPage::query()
                ->when($page, fn ($query) => $query->whereKeyNot($page->id))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function resolvePath(?string $path, string $slug, ?StaticPage $page = null): string
    {
        $path = '/'.trim((string) ($path ?: $slug), '/');

        if (in_array($path, StaticPage::reservedPaths(), true)) {
            throw ValidationException::withMessages([
                'path' => 'Path halaman bentrok dengan modul bawaan website.',
            ]);
        }

        validator(
            ['path' => $path],
            ['path' => [Rule::unique('static_pages', 'path')->ignore($page?->id)]]
        )->validate();

        return $path;
    }
}
