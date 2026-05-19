@csrf

<div class="card">
    <div class="card__body">
        <div class="form-grid">
            <div class="field">
                <label for="title">Judul Pengumuman</label>
                <input id="title" name="title" type="text" value="{{ old('title', $announcement->title) }}" required>
                @error('title') <div class="field__error">{{ $message }}</div> @enderror
            </div>

            <div class="form-grid form-grid--two">
                <div class="field">
                    <label for="slug">Slug</label>
                    <input id="slug" name="slug" type="text" value="{{ old('slug', $announcement->slug) }}" placeholder="otomatis dari judul jika kosong">
                    @error('slug') <div class="field__error">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label for="category">Kategori</label>
                    <input id="category" name="category" type="text" value="{{ old('category', $announcement->category ?? 'Umum') }}" required>
                    @error('category') <div class="field__error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="field">
                <label for="content">Konten Pengumuman</label>
                <textarea id="content" name="content" rows="8">{{ old('content', $announcement->content) }}</textarea>
                <small>HTML dasar diperbolehkan dan akan disanitasi otomatis.</small>
                @error('content') <div class="field__error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label for="file">File Lampiran</label>
                <input id="file" name="file" type="file">
                <small>Maksimal 20 MB. Kosongkan saat edit jika tidak ingin mengganti file.</small>
                @error('file') <div class="field__error">{{ $message }}</div> @enderror
            </div>

            @if ($announcement->file_path)
                <div class="widget-image-preview">
                    <span>File saat ini: <strong>{{ $announcement->file_name ?: $announcement->file_path }}</strong></span>
                    <a href="{{ route('announcements.file', ['announcement' => $announcement->slug]) }}" class="section-link">Unduh file</a>
                </div>
            @endif

            <div class="form-grid form-grid--two">
                <div class="field">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        @foreach ($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $announcement->status ?? 'draft') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status') <div class="field__error">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label for="published_at">Tanggal Publikasi</label>
                    <input id="published_at" name="published_at" type="datetime-local" value="{{ old('published_at', optional($announcement->published_at)->format('Y-m-d\TH:i')) }}">
                    <small>Kosongkan jika ingin memakai waktu publish saat ini.</small>
                    @error('published_at') <div class="field__error">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>
</div>

<div class="button-row" style="margin-top: 1rem;">
    <button type="submit" class="button button--primary">{{ $submitLabel }}</button>
    <a href="{{ route('admin.announcements.index') }}" class="button button--secondary">Kembali</a>
</div>
