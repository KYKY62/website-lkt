@csrf

<div class="card">
    <div class="card__body">
        <div class="form-grid">
            <div class="form-grid form-grid--two">
                <div class="field">
                    <label for="title">Judul Layanan</label>
                    <input id="title" name="title" type="text" value="{{ old('title', $service->title) }}" required>
                    @error('title')
                        <div class="field__error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="organizer">Penyelenggara</label>
                    <input id="organizer" name="organizer" type="text" value="{{ old('organizer', $service->organizer) }}" placeholder="Nama perangkat daerah penyelenggara" required>
                    @error('organizer')
                        <div class="field__error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="field">
                <label for="description">Deskripsi</label>
                <textarea id="description" name="description" rows="4" maxlength="700" required>{{ old('description', $service->description) }}</textarea>
                <small>Jelaskan fungsi layanan secara singkat dan jelas. Maksimal 700 karakter.</small>
                @error('description')
                    <div class="field__error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-grid form-grid--two">
                <div class="field">
                    <label for="logo">Icon / Logo</label>
                    <input id="logo" name="logo" type="file" accept="image/png,image/jpeg,image/webp">
                    <small>Format JPG, PNG, atau WEBP. Jika kosong, website memakai inisial layanan.</small>
                    @error('logo')
                        <div class="field__error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="sort_order">Urutan</label>
                    <input id="sort_order" name="sort_order" type="number" min="1" max="999" value="{{ old('sort_order', $service->sort_order ?? 1) }}" required>
                    @error('sort_order')
                        <div class="field__error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            @if ($service->logoUrl())
                <div class="widget-image-preview">
                    <img src="{{ $service->logoUrl() }}" alt="Logo {{ $service->title }}">
                    <span>Logo saat ini. Upload file baru untuk menggantinya.</span>
                </div>
            @endif

            <div class="form-grid form-grid--two">
                <div class="field">
                    <label for="link_url">Link Web / Aplikasi</label>
                    <input id="link_url" name="link_url" type="text" value="{{ old('link_url', $service->link_url) }}" placeholder="/halaman-layanan atau https://domain-resmi.go.id" required>
                    @error('link_url')
                        <div class="field__error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="link_target">Target Jendela</label>
                    <select id="link_target" name="link_target" required>
                        <option value="_self" @selected(old('link_target', $service->link_target ?? '_blank') === '_self')>_self</option>
                        <option value="_blank" @selected(old('link_target', $service->link_target ?? '_blank') === '_blank')>_blank</option>
                    </select>
                    @error('link_target')
                        <div class="field__error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="field">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $service->status ?? 'draft') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status')
                    <div class="field__error">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</div>

<div class="button-row" style="margin-top: 1rem;">
    <button type="submit" class="button button--primary">{{ $submitLabel }}</button>
    <a href="{{ route('admin.services.index') }}" class="button button--secondary">Kembali</a>
</div>
