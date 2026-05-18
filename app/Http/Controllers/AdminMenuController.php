<?php

namespace App\Http\Controllers;

use App\Models\SiteMenu;
use App\Models\StaticPage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminMenuController extends Controller
{
    public function index(): View
    {
        return view('admin.menus.index', [
            'menus' => SiteMenu::query()
                ->with(['parent', 'page', 'children'])
                ->whereNull('parent_id')
                ->orderBy('sort_order')
                ->orderBy('label')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.menus.create', $this->formData(new SiteMenu([
            'item_type' => SiteMenu::TYPE_MODULE,
            'target' => '_self',
            'sort_order' => 1,
            'is_active' => true,
        ])));
    }

    public function store(Request $request): RedirectResponse
    {
        $menu = SiteMenu::query()->create($this->validatedPayload($request));

        return redirect()
            ->route('admin.menus.edit', $menu)
            ->with('status', 'Menu berhasil ditambahkan.');
    }

    public function edit(SiteMenu $menu): View
    {
        return view('admin.menus.edit', $this->formData($menu));
    }

    public function update(Request $request, SiteMenu $menu): RedirectResponse
    {
        $menu->update($this->validatedPayload($request, $menu));

        return redirect()
            ->route('admin.menus.edit', $menu)
            ->with('status', 'Menu berhasil diperbarui.');
    }

    public function destroy(SiteMenu $menu): RedirectResponse
    {
        $menu->delete();

        return redirect()
            ->route('admin.menus.index')
            ->with('status', 'Menu berhasil dihapus.');
    }

    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tree' => ['required', 'array'],
            'tree.*.id' => ['required', 'integer', 'exists:site_menus,id'],
            'tree.*.children' => ['nullable', 'array'],
            'tree.*.children.*.id' => ['required', 'integer', 'exists:site_menus,id'],
        ]);

        Collection::make($validated['tree'])
            ->values()
            ->each(function (array $item, int $index): void {
                SiteMenu::query()
                    ->whereKey($item['id'])
                    ->update([
                        'parent_id' => null,
                        'sort_order' => $index + 1,
                    ]);

                Collection::make($item['children'] ?? [])
                    ->values()
                    ->each(function (array $child, int $childIndex) use ($item): void {
                        SiteMenu::query()
                            ->whereKey($child['id'])
                            ->update([
                                'parent_id' => $item['id'],
                                'sort_order' => $childIndex + 1,
                            ]);
                    });
            });

        return response()->json([
            'message' => 'Urutan menu berhasil diperbarui.',
        ]);
    }

    private function formData(SiteMenu $menu): array
    {
        return [
            'menu' => $menu,
            'pages' => StaticPage::query()->orderBy('title')->get(),
            'parentMenus' => SiteMenu::query()
                ->when($menu->exists, fn ($query) => $query->whereKeyNot($menu->id))
                ->whereNull('parent_id')
                ->orderBy('sort_order')
                ->orderBy('label')
                ->get(),
            'moduleOptions' => SiteMenu::moduleOptions(),
        ];
    }

    private function validatedPayload(Request $request, ?SiteMenu $menu = null): array
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:120'],
            'menu_position' => ['required', Rule::in(['master', 'submenu'])],
            'parent_id' => ['nullable', 'integer', 'exists:site_menus,id'],
            'item_type' => ['required', Rule::in(SiteMenu::itemTypes())],
            'page_id' => ['nullable', 'integer', 'exists:static_pages,id'],
            'url' => ['nullable', 'string', 'max:500'],
            'target' => ['nullable', Rule::in(['_self', '_blank'])],
            'module_key' => ['nullable', Rule::in(collect(SiteMenu::moduleOptions())->pluck('key')->all())],
            'sort_order' => ['required', 'integer', 'min:1', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['parent_id'] = $validated['menu_position'] === 'submenu'
            ? $validated['parent_id']
            : null;

        if ($validated['menu_position'] === 'submenu' && ! $validated['parent_id']) {
            throw ValidationException::withMessages([
                'parent_id' => 'Submenu harus memiliki menu induk.',
            ]);
        }

        if ($validated['item_type'] === SiteMenu::TYPE_PAGE && ! $validated['page_id']) {
            throw ValidationException::withMessages([
                'page_id' => 'Menu jenis page harus memilih halaman statis.',
            ]);
        }

        if ($validated['item_type'] === SiteMenu::TYPE_LINK && blank($validated['url'] ?? null)) {
            throw ValidationException::withMessages([
                'url' => 'Menu jenis link harus memiliki URL.',
            ]);
        }

        if ($validated['item_type'] === SiteMenu::TYPE_MODULE && blank($validated['module_key'] ?? null)) {
            throw ValidationException::withMessages([
                'module_key' => 'Menu jenis module harus memilih modul website.',
            ]);
        }

        $validated['page_id'] = $validated['item_type'] === SiteMenu::TYPE_PAGE ? $validated['page_id'] : null;
        $validated['url'] = $validated['item_type'] === SiteMenu::TYPE_LINK ? trim((string) ($validated['url'] ?? '')) : null;
        $validated['target'] = $validated['item_type'] === SiteMenu::TYPE_LINK ? ($validated['target'] ?? '_self') : '_self';
        $validated['module_key'] = $validated['item_type'] === SiteMenu::TYPE_MODULE ? $validated['module_key'] : null;
        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);

        unset($validated['menu_position']);

        return $validated;
    }
}
