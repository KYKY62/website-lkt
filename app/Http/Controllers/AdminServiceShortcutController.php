<?php

namespace App\Http\Controllers;

use App\Models\ServiceShortcut;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminServiceShortcutController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.services.index', [
            'services' => ServiceShortcut::query()
                ->when($request->filled('status'), fn ($query) => $query->where('status', $request->query('status')))
                ->ordered()
                ->paginate(12)
                ->withQueryString(),
            'statusOptions' => ServiceShortcut::statuses(),
        ]);
    }

    public function create(): View
    {
        return view('admin.services.create', [
            'service' => new ServiceShortcut([
                'status' => ServiceShortcut::STATUS_DRAFT,
                'sort_order' => 1,
                'link_target' => '_blank',
            ]),
            'statusOptions' => ServiceShortcut::statuses(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $service = ServiceShortcut::query()->create($this->validatedPayload($request));

        return redirect()
            ->route('admin.services.edit', $service)
            ->with('status', 'Layanan berhasil ditambahkan.');
    }

    public function edit(ServiceShortcut $service): View
    {
        return view('admin.services.edit', [
            'service' => $service,
            'statusOptions' => ServiceShortcut::statuses(),
        ]);
    }

    public function update(Request $request, ServiceShortcut $service): RedirectResponse
    {
        $service->update($this->validatedPayload($request, $service));

        return redirect()
            ->route('admin.services.edit', $service)
            ->with('status', 'Layanan berhasil diperbarui.');
    }

    public function destroy(ServiceShortcut $service): RedirectResponse
    {
        $this->deleteStoredLogo($service->logo_path);
        $service->delete();

        return redirect()
            ->route('admin.services.index')
            ->with('status', 'Layanan berhasil dihapus.');
    }

    private function validatedPayload(Request $request, ?ServiceShortcut $service = null): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'organizer' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string', 'max:700'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'link_url' => ['required', 'string', 'max:500'],
            'link_target' => ['required', Rule::in(['_self', '_blank'])],
            'status' => ['required', Rule::in(array_keys(ServiceShortcut::statuses()))],
            'sort_order' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $payload = [
            'title' => trim($validated['title']),
            'organizer' => trim($validated['organizer']),
            'description' => trim($validated['description']),
            'link_url' => $this->normalizeLink($validated['link_url']),
            'link_target' => $validated['link_target'],
            'status' => $validated['status'],
            'sort_order' => (int) $validated['sort_order'],
            'logo_path' => $service?->logo_path,
        ];

        $logo = $request->file('logo');

        if ($logo instanceof UploadedFile) {
            $this->deleteStoredLogo($service?->logo_path);
            $payload['logo_path'] = $logo->store('service-logos', 'public');
        }

        return $payload;
    }

    private function normalizeLink(string $link): string
    {
        $link = trim($link);

        if (Str::startsWith($link, '/')) {
            return '/'.ltrim($link, '/');
        }

        $scheme = parse_url($link, PHP_URL_SCHEME);

        if (! in_array($scheme, ['http', 'https'], true)) {
            throw ValidationException::withMessages([
                'link_url' => 'Link layanan harus berupa path internal atau URL http/https.',
            ]);
        }

        return $link;
    }

    private function deleteStoredLogo(?string $path): void
    {
        $path = trim((string) $path);

        if ($path === '' || Str::startsWith($path, ['http://', 'https://', '/'])) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
