@extends('admin.layout')

@section('content')
    <section class="admin-pagehead">
        <div>
            <p class="admin-brand__eyebrow">Modul Admin</p>
            <h1>Manajemen Berita</h1>
            <p>Kelola daftar berita yang tampil di website publik, mulai dari draft sampai artikel yang sudah diterbitkan.</p>
        </div>

        <a href="{{ route('admin.news.create') }}" class="button button--primary">Tambah Berita</a>
    </section>

    <section class="card">
        <div class="card__body">
            <div class="toolbar">
                <div>
                    <strong>{{ $articles->total() }}</strong> berita tersimpan
                </div>
                <div style="color: var(--ink-soft); font-size: 0.9rem;">Path modul: <code>/admin/news</code></div>
            </div>

            @if ($articles->isEmpty())
                <div class="empty-state">
                    Belum ada berita di database. Tambahkan artikel pertama untuk mulai membangun konten portal.
                </div>
            @else
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Judul</th>
                                <th>Kategori</th>
                                <th>Status</th>
                                <th>Editor</th>
                                <th>Publikasi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($articles as $article)
                                <tr>
                                    <td>
                                        <div class="table-title">{{ $article->title }}</div>
                                        <div class="table-subtitle">Slug: {{ $article->slug }}</div>
                                    </td>
                                    <td>{{ $article->category }}</td>
                                    <td>
                                        <span class="badge {{ $article->status === 'published' ? 'badge--published' : 'badge--draft' }}">
                                            {{ $article->status }}
                                        </span>
                                    </td>
                                    <td>{{ $article->publishedBy?->name ?? '-' }}</td>
                                    <td>{{ $article->published_at?->format('d M Y H:i') ?? '-' }}</td>
                                    <td>
                                        <div class="button-row">
                                            <a href="{{ route('admin.news.edit', $article) }}" class="button button--secondary">Edit</a>
                                            <a href="{{ url('/berita/'.$article->slug) }}" class="button button--secondary">Preview</a>
                                            <form method="POST" action="{{ route('admin.news.destroy', $article) }}" onsubmit="return confirm('Hapus berita ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="button button--danger">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="toolbar" style="margin-top: 1rem; margin-bottom: 0;">
                    <div style="color: var(--ink-soft); font-size: 0.9rem;">
                        Halaman {{ $articles->currentPage() }} dari {{ $articles->lastPage() }}
                    </div>

                    <div class="button-row">
                        @if ($articles->onFirstPage())
                            <span class="button button--secondary" style="opacity: 0.5; cursor: default;">Sebelumnya</span>
                        @else
                            <a href="{{ $articles->previousPageUrl() }}" class="button button--secondary">Sebelumnya</a>
                        @endif

                        @if ($articles->hasMorePages())
                            <a href="{{ $articles->nextPageUrl() }}" class="button button--secondary">Berikutnya</a>
                        @else
                            <span class="button button--secondary" style="opacity: 0.5; cursor: default;">Berikutnya</span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
