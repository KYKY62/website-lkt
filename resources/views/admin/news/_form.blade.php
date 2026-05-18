@csrf

@php
    $existingImages = $article->galleryImages();
    $storedImagePaths = $article->image_urls ?? [];
@endphp

<div class="card">
    <div class="card__body">
        <div class="form-grid">
            <div class="field">
                <label for="title">Judul Berita</label>
                <input id="title" name="title" type="text" value="{{ old('title', $article->title) }}" required>
                @error('title')
                    <div class="field__error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-grid form-grid--two">
                <div class="field">
                    <label for="slug">Slug</label>
                    <input id="slug" name="slug" type="text" value="{{ old('slug', $article->slug) }}" placeholder="otomatis dari judul jika kosong">
                    <small>Gunakan huruf kecil, angka, dan tanda hubung.</small>
                    @error('slug')
                        <div class="field__error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="category">Kategori</label>
                    <input id="category" name="category" type="text" value="{{ old('category', $article->category) }}" required>
                    @error('category')
                        <div class="field__error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="field">
                <label for="excerpt">Ringkasan</label>
                <textarea id="excerpt" name="excerpt" rows="4" required>{{ old('excerpt', $article->excerpt) }}</textarea>
                <small>Ringkasan ini ditampilkan pada daftar berita dan preview publik.</small>
                @error('excerpt')
                    <div class="field__error">{{ $message }}</div>
                @enderror
            </div>

            <div class="field">
                <label for="content-editor">Isi Berita</label>
                <input id="content" name="content" type="hidden" value="{{ old('content', $article->content) }}">

                <div class="wysiwyg" data-wysiwyg-root>
                    <div class="wysiwyg__toolbar">
                        <button type="button" class="button button--ghost wysiwyg__tool" data-command="bold">Bold</button>
                        <button type="button" class="button button--ghost wysiwyg__tool" data-command="italic">Italic</button>
                        <button type="button" class="button button--ghost wysiwyg__tool" data-command="insertUnorderedList">Bullet</button>
                        <button type="button" class="button button--ghost wysiwyg__tool" data-command="formatBlock" data-value="h2">H2</button>
                        <button type="button" class="button button--ghost wysiwyg__tool" data-command="formatBlock" data-value="blockquote">Quote</button>
                        <button type="button" class="button button--ghost wysiwyg__tool" data-command="createLink">Link</button>
                        <button type="button" class="button button--ghost wysiwyg__tool" data-command="removeFormat">Reset</button>
                    </div>

                    <div
                        id="content-editor"
                        class="wysiwyg__editor"
                        contenteditable="true"
                        data-wysiwyg-editor
                    ></div>
                </div>

                <small>Gunakan editor visual untuk paragraf, heading, bullet list, kutipan, dan tautan.</small>
                @error('content')
                    <div class="field__error">{{ $message }}</div>
                @enderror
            </div>

            <div class="field">
                <label for="images">Upload Gambar Berita</label>
                <input id="images" name="images[]" type="file" accept="image/png,image/jpeg,image/webp" multiple>
                <small>Upload beberapa gambar sekaligus. Gambar pertama akan menjadi sampul, sisanya tampil sebagai thumbnail di halaman detail. Jika Anda upload ulang saat edit, galeri lama akan diganti.</small>
                @error('images')
                    <div class="field__error">{{ $message }}</div>
                @enderror
                @error('images.*')
                    <div class="field__error">{{ $message }}</div>
                @enderror
            </div>

            <div class="field" data-upload-preview hidden>
                <label>Preview Upload Baru</label>
                <div class="admin-gallery" data-upload-preview-list></div>
            </div>

            @if ($existingImages !== [] && $storedImagePaths !== [])
                <div class="field">
                    <label>Thumbnail Saat Ini</label>
                    <small>Gunakan tombol naik dan turun untuk mengatur urutan gambar yang sudah tersimpan. Gambar paling atas akan menjadi sampul jika Anda tidak upload ulang gambar baru.</small>
                    <div class="admin-gallery" data-existing-gallery-list>
                        @foreach ($existingImages as $imageIndex => $imageUrl)
                            <div class="admin-gallery__item" data-gallery-item>
                                <input type="hidden" name="existing_image_order[]" value="{{ $storedImagePaths[$imageIndex] ?? '' }}" data-existing-image-order>
                                <img src="{{ $imageUrl }}" alt="Thumbnail berita {{ $imageIndex + 1 }}" class="admin-gallery__image">
                                <div class="admin-gallery__caption">
                                    {{ $imageIndex === 0 ? 'Sampul berita' : 'Galeri '.($imageIndex + 1) }}
                                </div>
                                <div class="admin-gallery__actions">
                                    <button type="button" class="button button--ghost admin-gallery__move" data-move-up>Naik</button>
                                    <button type="button" class="button button--ghost admin-gallery__move" data-move-down>Turun</button>
                                    <button type="button" class="button button--danger admin-gallery__move" data-remove-existing>Hapus</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="form-grid form-grid--two">
                <div class="field">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="draft" @selected(old('status', $article->status ?? 'draft') === 'draft')>Draft</option>
                        <option value="published" @selected(old('status', $article->status) === 'published')>Published</option>
                    </select>
                    <small>Ketika dipublish, nama editor yang menyimpan artikel akan ditampilkan di detail berita.</small>
                    @error('status')
                        <div class="field__error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="published_at">Tanggal Publikasi</label>
                    <input
                        id="published_at"
                        name="published_at"
                        type="datetime-local"
                        value="{{ old('published_at', optional($article->published_at)->format('Y-m-d\TH:i')) }}"
                    >
                    <small>Kosongkan jika ingin memakai waktu saat artikel dipublish.</small>
                    @error('published_at')
                        <div class="field__error">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</div>

<div class="button-row" style="margin-top: 1rem;">
    <button type="submit" class="button button--primary">{{ $submitLabel }}</button>
    <a href="{{ route('admin.news.index') }}" class="button button--secondary">Kembali</a>
</div>

<script>
    (() => {
        const hiddenInput = document.querySelector('#content');
        const editor = document.querySelector('[data-wysiwyg-editor]');
        const toolbarButtons = document.querySelectorAll('.wysiwyg__tool');
        const imagesInput = document.querySelector('#images');
        const uploadPreview = document.querySelector('[data-upload-preview]');
        const uploadPreviewList = document.querySelector('[data-upload-preview-list]');
        const existingGalleryList = document.querySelector('[data-existing-gallery-list]');

        const bindMoveButtons = (container) => {
            container?.querySelectorAll('[data-gallery-item]').forEach((item) => {
                const moveUp = item.querySelector('[data-move-up]');
                const moveDown = item.querySelector('[data-move-down]');
                const removeButton = item.querySelector('[data-remove-existing]');

                moveUp?.addEventListener('click', () => {
                    const previous = item.previousElementSibling;

                    if (previous) {
                        container.insertBefore(item, previous);
                    }
                });

                moveDown?.addEventListener('click', () => {
                    const next = item.nextElementSibling;

                    if (next) {
                        container.insertBefore(next, item);
                    }
                });

                removeButton?.addEventListener('click', () => {
                    item.remove();
                });
            });
        };

        if (hiddenInput && editor) {
            editor.innerHTML = hiddenInput.value || '<p></p>';

            const syncContent = () => {
                hiddenInput.value = editor.innerHTML.trim();
            };

            editor.addEventListener('input', syncContent);

            toolbarButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const command = button.dataset.command;
                    const value = button.dataset.value ?? null;

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

                    syncContent();
                });
            });
        }

        bindMoveButtons(existingGalleryList);

        if (imagesInput && uploadPreview && uploadPreviewList) {
            let selectedFiles = [];

            const syncFilesInput = () => {
                const dataTransfer = new DataTransfer();

                selectedFiles.forEach((file) => dataTransfer.items.add(file));
                imagesInput.files = dataTransfer.files;
            };

            const renderSelectedFiles = () => {
                uploadPreviewList.innerHTML = '';

                if (!selectedFiles.length) {
                    uploadPreview.hidden = true;
                    return;
                }

                uploadPreview.hidden = false;

                selectedFiles.forEach((file, index) => {
                    const wrapper = document.createElement('div');
                    const image = document.createElement('img');
                    const caption = document.createElement('div');
                    const actions = document.createElement('div');
                    const moveUp = document.createElement('button');
                    const moveDown = document.createElement('button');

                    wrapper.className = 'admin-gallery__item';
                    wrapper.dataset.galleryItem = 'true';
                    image.className = 'admin-gallery__image';
                    caption.className = 'admin-gallery__caption';
                    actions.className = 'admin-gallery__actions';
                    moveUp.className = 'button button--ghost admin-gallery__move';
                    moveDown.className = 'button button--ghost admin-gallery__move';
                    moveUp.type = 'button';
                    moveDown.type = 'button';
                    moveUp.textContent = 'Naik';
                    moveDown.textContent = 'Turun';

                    image.src = URL.createObjectURL(file);
                    image.alt = file.name;
                    caption.textContent = index === 0 ? 'Sampul baru' : `Galeri baru ${index + 1}`;

                    moveUp.addEventListener('click', () => {
                        if (index === 0) {
                            return;
                        }

                        [selectedFiles[index - 1], selectedFiles[index]] = [selectedFiles[index], selectedFiles[index - 1]];
                        syncFilesInput();
                        renderSelectedFiles();
                    });

                    moveDown.addEventListener('click', () => {
                        if (index === selectedFiles.length - 1) {
                            return;
                        }

                        [selectedFiles[index + 1], selectedFiles[index]] = [selectedFiles[index], selectedFiles[index + 1]];
                        syncFilesInput();
                        renderSelectedFiles();
                    });

                    actions.appendChild(moveUp);
                    actions.appendChild(moveDown);
                    wrapper.appendChild(image);
                    wrapper.appendChild(caption);
                    wrapper.appendChild(actions);
                    uploadPreviewList.appendChild(wrapper);
                });
            };

            imagesInput.addEventListener('change', () => {
                selectedFiles = Array.from(imagesInput.files ?? []);
                syncFilesInput();
                renderSelectedFiles();
            });
        }
    })();
</script>
