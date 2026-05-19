@extends('admin.layout', ['title' => 'Kabar Perangkat Daerah'])

@section('content')
    <section class="admin-pagehead">
        <div>
            <p class="admin-brand__eyebrow">Widget Beranda</p>
            <h1>Kabar Perangkat Daerah</h1>
            <p>Atur agregasi berita dari subdomain perangkat daerah yang tampil di beranda utama.</p>
        </div>

        <div class="button-row">
            <form method="POST" action="{{ route('admin.department-news.refresh') }}">
                @csrf
                <button type="submit" class="button button--secondary">Refresh Cache</button>
            </form>
            <form method="POST" action="{{ route('admin.department-news.clear') }}">
                @csrf
                <button type="submit" class="button button--ghost">Kosongkan Cache</button>
            </form>
        </div>
    </section>

    <form method="POST" action="{{ route('admin.department-news.update') }}">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="card__body">
                <div class="form-grid">
                    <label class="field" style="gap: 0.8rem;">
                        <span>Status Widget</span>
                        <span style="display: flex; align-items: center; gap: 0.65rem;">
                            <input type="checkbox" name="is_enabled" value="1" @checked(old('is_enabled', $setting->is_enabled))>
                            Tampilkan di beranda
                        </span>
                    </label>

                    <div class="field">
                        <label for="title">Judul Section</label>
                        <input id="title" name="title" type="text" value="{{ old('title', $setting->title) }}" required>
                        @error('title')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="description">Deskripsi Singkat</label>
                        <textarea id="description" name="description" rows="3" maxlength="300">{{ old('description', $setting->description) }}</textarea>
                        @error('description')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-grid form-grid--two">
                        <div class="field">
                            <label for="item_limit">Jumlah Item</label>
                            <input id="item_limit" name="item_limit" type="number" min="1" max="20" value="{{ old('item_limit', $setting->item_limit) }}" required>
                            <small>Default 7 item. Request API memakai parameter <code>per_page</code>.</small>
                            @error('item_limit')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="cache_ttl_minutes">Durasi Cache (menit)</label>
                            <input id="cache_ttl_minutes" name="cache_ttl_minutes" type="number" min="1" max="1440" value="{{ old('cache_ttl_minutes', $setting->cache_ttl_minutes) }}" required>
                            <small>Default 10 menit. Cache terakhir dipakai sebagai fallback bila API gagal.</small>
                            @error('cache_ttl_minutes')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="field">
                        <label>Endpoint API</label>
                        <input type="text" value="{{ $apiUrl }}" disabled>
                        <small>Endpoint dikunci lewat konfigurasi <code>DEPARTMENT_NEWS_API_URL</code> agar tidak menjadi input bebas.</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="button-row" style="margin-top: 1rem;">
            <button type="submit" class="button button--primary">Simpan Pengaturan</button>
            <a href="{{ route('public-site') }}" class="button button--secondary">Lihat Website</a>
        </div>
    </form>
@endsection
