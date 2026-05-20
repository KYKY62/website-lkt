<?php

namespace App\Http\Controllers;

use App\Models\PageWidget;
use App\Models\StaticPage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminPageWidgetController extends Controller
{
    public function index(Request $request): View
    {
        $widgets = PageWidget::query()
            ->with('staticPage')
            ->when($request->filled('target_key'), fn ($query) => $this->applyTargetFilter($query, (string) $request->query('target_key')))
            ->when($request->filled('display_area'), fn ($query) => $query->where('display_area', $request->query('display_area')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->query('status')))
            ->when($request->filled('widget_type'), fn ($query) => $query->where('widget_type', $request->query('widget_type')))
            ->when($request->filled('column'), fn ($query) => $query->where('column', $request->query('column')))
            ->ordered()
            ->paginate(12)
            ->withQueryString();

        return view('admin.widgets.index', [
            'widgets' => $widgets,
            'targetOptions' => $this->targetOptions(),
            'areaOptions' => PageWidget::areas(),
            'typeOptions' => PageWidget::types(),
            'columnOptions' => PageWidget::columns(),
            'statusOptions' => PageWidget::statuses(),
        ]);
    }

    public function create(): View
    {
        return view('admin.widgets.create', $this->formData(new PageWidget([
            'target_type' => PageWidget::TARGET_BUILTIN,
            'display_area' => PageWidget::AREA_PRE_FOOTER,
            'target_path' => '/',
            'column' => PageWidget::COLUMN_LEFT,
            'widget_type' => PageWidget::TYPE_TEXT_CTA,
            'status' => PageWidget::STATUS_DRAFT,
            'sort_order' => 1,
            'link_target' => '_self',
        ])));
    }

    public function store(Request $request): RedirectResponse
    {
        $widget = PageWidget::query()->create($this->validatedPayload($request));

        return redirect()
            ->route('admin.widgets.edit', $widget)
            ->with('status', 'Widget halaman berhasil ditambahkan.');
    }

    public function edit(PageWidget $widget): View
    {
        return view('admin.widgets.edit', $this->formData($widget->load('staticPage')));
    }

    public function update(Request $request, PageWidget $widget): RedirectResponse
    {
        $widget->update($this->validatedPayload($request, $widget));

        return redirect()
            ->route('admin.widgets.edit', $widget)
            ->with('status', 'Widget halaman berhasil diperbarui.');
    }

    public function destroy(PageWidget $widget): RedirectResponse
    {
        $this->deleteStoredImage($widget->image_path);
        $widget->delete();

        return redirect()
            ->route('admin.widgets.index')
            ->with('status', 'Widget halaman berhasil dihapus.');
    }

    private function formData(PageWidget $widget): array
    {
        return [
            'widget' => $widget,
            'selectedTargetKey' => $this->targetKey($widget),
            'targetOptions' => $this->targetOptions(),
            'areaOptions' => PageWidget::areas(),
            'typeOptions' => PageWidget::types(),
            'columnOptions' => PageWidget::columns(),
            'statusOptions' => PageWidget::statuses(),
            'allowedEmbedDomains' => config('langkat_site.widget_embed_allowed_domains', []),
        ];
    }

    private function targetOptions(): array
    {
        $builtinTargets = collect(PageWidget::builtinTargets())
            ->map(fn (array $target): array => [
                'value' => PageWidget::TARGET_BUILTIN.':'.$target['path'],
                'label' => "{$target['label']} ({$target['path']})",
            ]);

        $staticPages = StaticPage::query()
            ->orderBy('title')
            ->get()
            ->map(fn (StaticPage $page): array => [
                'value' => PageWidget::TARGET_STATIC_PAGE.':'.$page->id,
                'label' => "{$page->title} ({$page->path})",
            ]);

        return $builtinTargets
            ->merge($staticPages)
            ->values()
            ->all();
    }

    private function targetKey(PageWidget $widget): string
    {
        if ($widget->target_type === PageWidget::TARGET_STATIC_PAGE && $widget->static_page_id) {
            return PageWidget::TARGET_STATIC_PAGE.':'.$widget->static_page_id;
        }

        return PageWidget::TARGET_BUILTIN.':'.($widget->target_path ?: '/');
    }

    private function applyTargetFilter($query, string $targetKey): void
    {
        $target = $this->resolveTarget($targetKey);

        if ($target['target_type'] === PageWidget::TARGET_STATIC_PAGE) {
            $query->where('target_type', PageWidget::TARGET_STATIC_PAGE)
                ->where('static_page_id', $target['static_page_id']);

            return;
        }

        $query->where('target_type', PageWidget::TARGET_BUILTIN)
            ->where('target_path', $target['target_path']);
    }

    private function validatedPayload(Request $request, ?PageWidget $widget = null): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'target_key' => ['required', 'string', 'max:120'],
            'display_area' => ['nullable', Rule::in(array_keys(PageWidget::areas()))],
            'column' => ['required', Rule::in(array_keys(PageWidget::columns()))],
            'widget_type' => ['required', Rule::in(array_keys(PageWidget::types()))],
            'status' => ['required', Rule::in(array_keys(PageWidget::statuses()))],
            'sort_order' => ['required', 'integer', 'min:1', 'max:999'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'image_alt' => ['nullable', 'string', 'max:180'],
            'link_url' => ['nullable', 'string', 'max:500'],
            'link_target' => ['nullable', Rule::in(['_self', '_blank'])],
            'html_content' => ['nullable', 'string'],
            'embed_url' => ['nullable', 'string', 'max:800'],
            'text_body' => ['nullable', 'string', 'max:700'],
            'cta_label' => ['nullable', 'string', 'max:120'],
        ]);

        $displayArea = $validated['display_area'] ?? PageWidget::AREA_PRE_FOOTER;
        $target = $displayArea === PageWidget::AREA_HOME_HERO
            ? [
                'target_type' => PageWidget::TARGET_BUILTIN,
                'target_path' => '/',
                'static_page_id' => null,
            ]
            : $this->resolveTarget($validated['target_key']);
        $uploadedImage = $request->file('image');
        $currentImagePath = $widget?->image_path;
        $needsImage = in_array($validated['widget_type'], [PageWidget::TYPE_STATIC_IMAGE, PageWidget::TYPE_LINK_BANNER], true);

        if ($needsImage && ! $uploadedImage && ! $currentImagePath) {
            throw ValidationException::withMessages([
                'image' => 'Gambar wajib diupload untuk tipe widget ini.',
            ]);
        }

        $payload = [
            ...$target,
            'display_area' => $displayArea,
            'title' => trim($validated['title']),
            'column' => $displayArea === PageWidget::AREA_HOME_HERO ? PageWidget::COLUMN_RIGHT : $validated['column'],
            'widget_type' => $validated['widget_type'],
            'status' => $validated['status'],
            'sort_order' => (int) $validated['sort_order'],
            'image_alt' => filled($validated['image_alt'] ?? null) ? trim((string) $validated['image_alt']) : null,
            'link_target' => $validated['link_target'] ?? '_self',
            'link_url' => null,
            'html_content' => null,
            'embed_url' => null,
            'text_body' => null,
            'cta_label' => null,
        ];

        if ($needsImage) {
            $payload['image_path'] = $this->resolveImagePath($uploadedImage, $currentImagePath);
        } else {
            $this->deleteStoredImage($currentImagePath);
            $payload['image_path'] = null;
            $payload['image_alt'] = null;
        }

        if ($validated['widget_type'] === PageWidget::TYPE_LINK_BANNER) {
            $payload['link_url'] = $this->requiredLink($validated['link_url'] ?? null);
        }

        if ($validated['widget_type'] === PageWidget::TYPE_HTML) {
            $payload['html_content'] = $this->requiredHtml($validated['html_content'] ?? null);
        }

        if ($validated['widget_type'] === PageWidget::TYPE_EMBED) {
            $payload['embed_url'] = $this->requiredEmbedUrl($validated['embed_url'] ?? null);
        }

        if ($validated['widget_type'] === PageWidget::TYPE_TEXT_CTA) {
            $this->fillTextCta($payload, $validated);
        }

        return $payload;
    }

    private function resolveTarget(string $targetKey): array
    {
        if (Str::startsWith($targetKey, PageWidget::TARGET_STATIC_PAGE.':')) {
            $pageId = (int) Str::after($targetKey, PageWidget::TARGET_STATIC_PAGE.':');

            if (! StaticPage::query()->whereKey($pageId)->exists()) {
                throw ValidationException::withMessages([
                    'target_key' => 'Halaman statis yang dipilih tidak tersedia.',
                ]);
            }

            return [
                'target_type' => PageWidget::TARGET_STATIC_PAGE,
                'target_path' => null,
                'static_page_id' => $pageId,
            ];
        }

        if (Str::startsWith($targetKey, PageWidget::TARGET_BUILTIN.':')) {
            $path = Str::after($targetKey, PageWidget::TARGET_BUILTIN.':') ?: '/';
            $allowedPaths = collect(PageWidget::builtinTargets())->pluck('path')->all();

            if (! in_array($path, $allowedPaths, true)) {
                throw ValidationException::withMessages([
                    'target_key' => 'Halaman bawaan yang dipilih tidak valid.',
                ]);
            }

            return [
                'target_type' => PageWidget::TARGET_BUILTIN,
                'target_path' => $path,
                'static_page_id' => null,
            ];
        }

        throw ValidationException::withMessages([
            'target_key' => 'Target halaman tidak valid.',
        ]);
    }

    private function resolveImagePath(?UploadedFile $uploadedImage, ?string $currentImagePath): ?string
    {
        if ($uploadedImage) {
            $this->deleteStoredImage($currentImagePath);

            return $uploadedImage->store('page-widgets', 'public');
        }

        return $currentImagePath;
    }

    private function requiredLink(?string $link): string
    {
        $normalized = $this->normalizeLink($link);

        if (! $normalized) {
            throw ValidationException::withMessages([
                'link_url' => 'URL wajib diisi untuk tipe widget ini.',
            ]);
        }

        return $normalized;
    }

    private function normalizeLink(?string $link): ?string
    {
        $link = trim((string) $link);

        if ($link === '') {
            return null;
        }

        if (Str::startsWith($link, '/')) {
            return '/'.ltrim($link, '/');
        }

        $scheme = parse_url($link, PHP_URL_SCHEME);

        if (! in_array($scheme, ['http', 'https'], true)) {
            throw ValidationException::withMessages([
                'link_url' => 'URL harus berupa path internal atau link http/https.',
            ]);
        }

        return $link;
    }

    private function requiredHtml(?string $html): string
    {
        $html = trim((string) $html);

        if ($html === '') {
            throw ValidationException::withMessages([
                'html_content' => 'Konten HTML wajib diisi.',
            ]);
        }

        return $html;
    }

    private function requiredEmbedUrl(?string $url): string
    {
        $url = trim((string) $url);
        $scheme = parse_url($url, PHP_URL_SCHEME);
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        if ($url === '' || ! in_array($scheme, ['http', 'https'], true)) {
            throw ValidationException::withMessages([
                'embed_url' => 'URL embed wajib berupa link http/https.',
            ]);
        }

        if (! in_array($host, config('langkat_site.widget_embed_allowed_domains', []), true)) {
            throw ValidationException::withMessages([
                'embed_url' => 'Domain embed belum masuk whitelist.',
            ]);
        }

        return $url;
    }

    private function fillTextCta(array &$payload, array $validated): void
    {
        $body = trim((string) ($validated['text_body'] ?? ''));
        $label = trim((string) ($validated['cta_label'] ?? ''));

        if ($body === '') {
            throw ValidationException::withMessages([
                'text_body' => 'Teks CTA wajib diisi.',
            ]);
        }

        if ($label === '') {
            throw ValidationException::withMessages([
                'cta_label' => 'Label tombol wajib diisi.',
            ]);
        }

        $payload['text_body'] = $body;
        $payload['cta_label'] = $label;
        $payload['link_url'] = $this->requiredLink($validated['link_url'] ?? null);
    }

    private function deleteStoredImage(?string $path): void
    {
        $path = trim((string) $path);

        if ($path === '' || Str::startsWith($path, ['http://', 'https://', '/'])) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
