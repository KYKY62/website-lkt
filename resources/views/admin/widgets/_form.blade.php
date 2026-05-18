@csrf

@php
    $activeType = old('widget_type', $widget->widget_type ?? 'text_cta');
    $activeTarget = old('target_key', $selectedTargetKey);
    $activeArea = old('display_area', $widget->display_area ?? 'pre_footer');
@endphp

<div class="card">
    <div class="card__body">
        <div class="form-grid">
            <div class="form-grid form-grid--two">
                <div class="field">
                    <label for="display_area">Area Tampil</label>
                    <select id="display_area" name="display_area" required>
                        @foreach ($areaOptions as $value => $label)
                            <option value="{{ $value }}" @selected($activeArea === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <small data-area-note></small>
                    @error('display_area')
                        <div class="field__error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="title">Judul Internal</label>
                    <input id="title" name="title" type="text" value="{{ old('title', $widget->title) }}" required>
                    <small>Judul ini dipakai di admin dan sebagai fallback alt gambar.</small>
                    @error('title')
                        <div class="field__error">{{ $message }}</div>
                    @enderror
                </div>
                <div class="field" data-target-field>
                    <label for="target_key">Target Halaman</label>
                    <select id="target_key" name="target_key" required>
                        @foreach ($targetOptions as $option)
                            <option value="{{ $option['value'] }}" @selected($activeTarget === $option['value'])>{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                    @error('target_key')
                        <div class="field__error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-grid form-grid--two">
                <div class="field" data-column-field>
                    <label for="column">Kolom</label>
                    <select id="column" name="column" required>
                        @foreach ($columnOptions as $value => $label)
                            <option value="{{ $value }}" @selected(old('column', $widget->column ?? 'left') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('column')
                        <div class="field__error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="sort_order">Urutan</label>
                    <input id="sort_order" name="sort_order" type="number" min="1" max="999" value="{{ old('sort_order', $widget->sort_order ?? 1) }}" required>
                    @error('sort_order')
                        <div class="field__error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-grid form-grid--two">
                <div class="field">
                    <label for="widget_type">Tipe Widget</label>
                    <select id="widget_type" name="widget_type" required>
                        @foreach ($typeOptions as $value => $label)
                            <option value="{{ $value }}" @selected($activeType === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('widget_type')
                        <div class="field__error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        @foreach ($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $widget->status ?? 'draft') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status')
                        <div class="field__error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="widget-form-panel" data-widget-group="image">
                <div class="form-grid form-grid--two">
                    <div class="field">
                        <label for="image">Upload Gambar</label>
                        <input id="image" name="image" type="file" accept="image/png,image/jpeg,image/webp">
                        <small>Wajib untuk Static Image dan Link Banner. Format: JPG, PNG, atau WEBP, maksimal 4 MB.</small>
                        @error('image')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="image_alt">Alt Gambar</label>
                        <input id="image_alt" name="image_alt" type="text" value="{{ old('image_alt', $widget->image_alt) }}" placeholder="Deskripsi singkat gambar">
                        @error('image_alt')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                @if ($widget->imageUrl())
                    <div class="widget-image-preview">
                        <img src="{{ $widget->imageUrl() }}" alt="{{ $widget->image_alt ?: $widget->title }}">
                        <span>Gambar saat ini. Upload gambar baru untuk menggantinya.</span>
                    </div>
                @endif
            </div>

            <div class="widget-form-panel" data-widget-group="link">
                <div class="form-grid form-grid--two">
                    <div class="field">
                        <label for="link_url">URL Link</label>
                        <input id="link_url" name="link_url" type="text" value="{{ old('link_url', $widget->link_url) }}" placeholder="/layanan atau https://domain-resmi.go.id">
                        @error('link_url')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="link_target">Target Jendela</label>
                        <select id="link_target" name="link_target">
                            <option value="_self" @selected(old('link_target', $widget->link_target ?? '_self') === '_self')>_self</option>
                            <option value="_blank" @selected(old('link_target', $widget->link_target) === '_blank')>_blank</option>
                        </select>
                        @error('link_target')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="widget-form-panel" data-widget-group="html">
                <div class="field">
                    <label for="html_content">HTML Terbatas</label>
                    <textarea id="html_content" name="html_content" rows="8" placeholder="<p>Konten widget...</p>">{{ old('html_content', $widget->html_content) }}</textarea>
                    <small>Tag yang dipertahankan: p, br, strong, em, ul, ol, li, a, h2-h4, blockquote, span. Script dan event attribute akan dibuang.</small>
                    @error('html_content')
                        <div class="field__error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="widget-form-panel" data-widget-group="embed">
                <div class="field">
                    <label for="embed_url">URL Embed</label>
                    <input id="embed_url" name="embed_url" type="text" value="{{ old('embed_url', $widget->embed_url) }}" placeholder="https://www.youtube.com/embed/...">
                    <small>Domain whitelist: {{ implode(', ', $allowedEmbedDomains) }}.</small>
                    @error('embed_url')
                        <div class="field__error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="widget-form-panel" data-widget-group="text_cta">
                <div class="field">
                    <label for="text_body">Teks CTA</label>
                    <textarea id="text_body" name="text_body" rows="4" placeholder="Tulis pesan singkat untuk pengunjung.">{{ old('text_body', $widget->text_body) }}</textarea>
                    @error('text_body')
                        <div class="field__error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="cta_label">Label Tombol</label>
                    <input id="cta_label" name="cta_label" type="text" value="{{ old('cta_label', $widget->cta_label) }}" placeholder="Buka Layanan">
                    @error('cta_label')
                        <div class="field__error">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</div>

<div class="button-row" style="margin-top: 1rem;">
    <button type="submit" class="button button--primary">{{ $submitLabel }}</button>
    <a href="{{ route('admin.widgets.index') }}" class="button button--secondary">Kembali</a>
</div>

<script>
    (() => {
        const type = document.querySelector('#widget_type');
        const area = document.querySelector('#display_area');
        const groups = document.querySelectorAll('[data-widget-group]');
        const targetField = document.querySelector('[data-target-field]');
        const columnField = document.querySelector('[data-column-field]');
        const areaNote = document.querySelector('[data-area-note]');

        const visibleGroups = {
            static_image: ['image'],
            link_banner: ['image', 'link'],
            html: ['html'],
            embed: ['embed'],
            text_cta: ['text_cta', 'link'],
        };

        const syncVisibility = () => {
            const activeGroups = visibleGroups[type?.value] ?? [];

            groups.forEach((group) => {
                group.hidden = !activeGroups.includes(group.dataset.widgetGroup);
            });

            const isHero = area?.value === 'home_hero';

            targetField.hidden = isHero;
            columnField.hidden = isHero;
            areaNote.textContent = isHero
                ? 'Area hero hanya tampil di kanan hero beranda. Target otomatis dikunci ke Beranda.'
                : 'Area pre-footer tampil sebelum footer pada halaman target yang dipilih.';
        };

        type?.addEventListener('change', syncVisibility);
        area?.addEventListener('change', syncVisibility);
        syncVisibility();
    })();
</script>
