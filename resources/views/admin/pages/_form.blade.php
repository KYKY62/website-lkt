@csrf

<div class="card">
    <div class="card__body">
        <div class="form-grid">
            <div class="field">
                <label for="title">Judul Halaman</label>
                <input id="title" name="title" type="text" value="{{ old('title', $page->title) }}" required>
                @error('title')
                    <div class="field__error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-grid form-grid--two">
                <div class="field">
                    <label for="slug">Slug</label>
                    <input id="slug" name="slug" type="text" value="{{ old('slug', $page->slug) }}" placeholder="otomatis dari judul jika kosong">
                    @error('slug')
                        <div class="field__error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="path">Path Publik</label>
                    <input id="path" name="path" type="text" value="{{ old('path', $page->path) }}" placeholder="/tentang-kami">
                    <small>Jika kosong, path diambil dari slug.</small>
                    @error('path')
                        <div class="field__error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="field">
                <label for="excerpt">Ringkasan</label>
                <textarea id="excerpt" name="excerpt" rows="3">{{ old('excerpt', $page->excerpt) }}</textarea>
                @error('excerpt')
                    <div class="field__error">{{ $message }}</div>
                @enderror
            </div>

            <div class="field">
                <label for="content-editor-page">Isi Halaman</label>
                <input id="page-content" name="content" type="hidden" value="{{ old('content', $page->content) }}">

                <div class="wysiwyg">
                    <div class="wysiwyg__toolbar">
                        <button type="button" class="button button--ghost wysiwyg__tool" data-page-command="bold">Bold</button>
                        <button type="button" class="button button--ghost wysiwyg__tool" data-page-command="italic">Italic</button>
                        <button type="button" class="button button--ghost wysiwyg__tool" data-page-command="insertUnorderedList">Bullet</button>
                        <button type="button" class="button button--ghost wysiwyg__tool" data-page-command="formatBlock" data-page-value="h2">H2</button>
                        <button type="button" class="button button--ghost wysiwyg__tool" data-page-command="createLink">Link</button>
                    </div>
                    <div id="content-editor-page" class="wysiwyg__editor" contenteditable="true"></div>
                </div>

                @error('content')
                    <div class="field__error">{{ $message }}</div>
                @enderror
            </div>

            <div class="field">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <option value="draft" @selected(old('status', $page->status ?? 'draft') === 'draft')>Draft</option>
                    <option value="published" @selected(old('status', $page->status) === 'published')>Published</option>
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
    <a href="{{ route('admin.pages.index') }}" class="button button--secondary">Kembali</a>
</div>

<script>
    (() => {
        const hiddenInput = document.querySelector('#page-content');
        const editor = document.querySelector('#content-editor-page');
        const tools = document.querySelectorAll('[data-page-command]');

        if (!hiddenInput || !editor) {
            return;
        }

        editor.innerHTML = hiddenInput.value || '<p></p>';

        const sync = () => {
            hiddenInput.value = editor.innerHTML.trim();
        };

        editor.addEventListener('input', sync);

        tools.forEach((button) => {
            button.addEventListener('click', () => {
                const command = button.dataset.pageCommand;
                const value = button.dataset.pageValue ?? null;

                editor.focus();

                if (command === 'createLink') {
                    const url = window.prompt('Masukkan URL tautan');

                    if (!url) {
                        return;
                    }

                    document.execCommand(command, false, url);
                } else if (value) {
                    document.execCommand(command, false, value);
                } else {
                    document.execCommand(command, false, null);
                }

                sync();
            });
        });
    })();
</script>
