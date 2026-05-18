@csrf

@php
    $menuPosition = old('menu_position', $menu->parent_id ? 'submenu' : 'master');
    $menuType = old('item_type', $menu->item_type ?? 'module');
@endphp

<div class="card">
    <div class="card__body">
        <div class="form-grid">
            <div class="field">
                <label for="label">Label Menu</label>
                <input id="label" name="label" type="text" value="{{ old('label', $menu->label) }}" required>
                @error('label')
                    <div class="field__error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-grid form-grid--two">
                <div class="field">
                    <label for="menu_position">Posisi Menu</label>
                    <select id="menu_position" name="menu_position" required>
                        <option value="master" @selected($menuPosition === 'master')>Master</option>
                        <option value="submenu" @selected($menuPosition === 'submenu')>Submenu</option>
                    </select>
                </div>

                <div class="field" data-parent-wrapper>
                    <label for="parent_id">Menu Induk</label>
                    <select id="parent_id" name="parent_id">
                        <option value="">Pilih menu induk</option>
                        @foreach ($parentMenus as $parentMenu)
                            <option value="{{ $parentMenu->id }}" @selected((string) old('parent_id', $menu->parent_id) === (string) $parentMenu->id)>{{ $parentMenu->label }}</option>
                        @endforeach
                    </select>
                    @error('parent_id')
                        <div class="field__error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-grid form-grid--two">
                <div class="field">
                    <label for="item_type">Jenis Menu</label>
                    <select id="item_type" name="item_type" required>
                        <option value="page" @selected($menuType === 'page')>Page</option>
                        <option value="link" @selected($menuType === 'link')>Link</option>
                        <option value="module" @selected($menuType === 'module')>Module</option>
                    </select>
                </div>

                <div class="field">
                    <label for="sort_order">Urutan</label>
                    <input id="sort_order" name="sort_order" type="number" min="1" max="999" value="{{ old('sort_order', $menu->sort_order ?? 1) }}" required>
                </div>
            </div>

            <div class="field" data-page-wrapper>
                <label for="page_id">Halaman Statis</label>
                <select id="page_id" name="page_id">
                    <option value="">Pilih halaman</option>
                    @foreach ($pages as $page)
                        <option value="{{ $page->id }}" @selected((string) old('page_id', $menu->page_id) === (string) $page->id)>{{ $page->title }} ({{ $page->path }})</option>
                    @endforeach
                </select>
            </div>

            <div class="form-grid form-grid--two" data-link-wrapper>
                <div class="field">
                    <label for="url">Link</label>
                    <input id="url" name="url" type="text" value="{{ old('url', $menu->url) }}" placeholder="https://... atau /halaman">
                </div>

                <div class="field">
                    <label for="target">Tipe Jendela</label>
                    <select id="target" name="target">
                        <option value="_self" @selected(old('target', $menu->target ?? '_self') === '_self')>_self</option>
                        <option value="_blank" @selected(old('target', $menu->target) === '_blank')>_blank</option>
                    </select>
                </div>
            </div>

            <div class="field" data-module-wrapper>
                <label for="module_key">Modul Website</label>
                <select id="module_key" name="module_key">
                    <option value="">Pilih modul</option>
                    @foreach ($moduleOptions as $option)
                        <option value="{{ $option['key'] }}" @selected(old('module_key', $menu->module_key) === $option['key'])>{{ $option['label'] }}</option>
                    @endforeach
                </select>
            </div>

            <label class="field" style="gap: 0.8rem;">
                <span>Status Menu</span>
                <span style="display: flex; align-items: center; gap: 0.65rem;">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $menu->is_active ?? true))>
                    Aktif ditampilkan di website
                </span>
            </label>
        </div>
    </div>
</div>

<div class="button-row" style="margin-top: 1rem;">
    <button type="submit" class="button button--primary">{{ $submitLabel }}</button>
    <a href="{{ route('admin.menus.index') }}" class="button button--secondary">Kembali</a>
</div>

<script>
    (() => {
        const position = document.querySelector('#menu_position');
        const type = document.querySelector('#item_type');
        const parentWrapper = document.querySelector('[data-parent-wrapper]');
        const pageWrapper = document.querySelector('[data-page-wrapper]');
        const linkWrapper = document.querySelector('[data-link-wrapper]');
        const moduleWrapper = document.querySelector('[data-module-wrapper]');

        const syncVisibility = () => {
            parentWrapper.style.display = position?.value !== 'submenu' ? 'none' : '';
            pageWrapper.style.display = type?.value !== 'page' ? 'none' : '';
            linkWrapper.style.display = type?.value !== 'link' ? 'none' : '';
            moduleWrapper.style.display = type?.value !== 'module' ? 'none' : '';
        };

        position?.addEventListener('change', syncVisibility);
        type?.addEventListener('change', syncVisibility);
        syncVisibility();
    })();
</script>
