@extends('admin.layout', ['title' => 'Pengumuman'])

@section('content')
    <section class="admin-pagehead">
        <div>
            <p class="admin-brand__eyebrow">Modul Konten</p>
            <h1>Pengumuman</h1>
            <p>Kelola surat edaran, agenda, dan informasi penting yang tampil pada kanal pengumuman publik.</p>
        </div>

        <a href="{{ route('admin.announcements.create') }}" class="button button--primary">Tambah Pengumuman</a>
    </section>

    <section class="card">
        <div class="card__body">
            <form method="GET" action="{{ route('admin.announcements.index') }}" class="admin-filter admin-filter--compact">
                <div class="field">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">Semua status</option>
                        @foreach ($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="button-row admin-filter__actions">
                    <button type="submit" class="button button--primary">Terapkan</button>
                    <a href="{{ route('admin.announcements.index') }}" class="button button--secondary">Reset</a>
                </div>
            </form>

            <div class="toolbar">
                <div><strong>{{ $announcements->total() }}</strong> pengumuman tersimpan</div>
                <div style="color: var(--ink-soft); font-size: 0.9rem;">Path modul: <code>/admin/announcements</code></div>
            </div>

            @if ($announcements->isEmpty())
                <div class="empty-state">Belum ada pengumuman tersimpan.</div>
            @else
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Judul</th>
                                <th>Kategori</th>
                                <th>Status</th>
                                <th>Publikasi</th>
                                <th>File</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($announcements as $announcement)
                                <tr>
                                    <td>
                                        <div class="table-title">{{ $announcement->title }}</div>
                                        <div class="table-subtitle">Slug: {{ $announcement->slug }}{{ $announcement->legacy_id ? ' | Legacy ID: '.$announcement->legacy_id : '' }}</div>
                                    </td>
                                    <td>{{ $announcement->category }}</td>
                                    <td>
                                        <span class="badge {{ $announcement->status === 'published' ? 'badge--published' : 'badge--draft' }}">
                                            {{ $statusOptions[$announcement->status] ?? $announcement->status }}
                                        </span>
                                    </td>
                                    <td>{{ $announcement->published_at?->format('d M Y H:i') ?? '-' }}</td>
                                    <td>{{ $announcement->file_name ?: '-' }}</td>
                                    <td>
                                        <div class="button-row">
                                            <a href="{{ route('admin.announcements.edit', $announcement) }}" class="button button--secondary">Edit</a>
                                            <a href="{{ url('/pengumuman/'.$announcement->slug) }}" class="button button--secondary">Preview</a>
                                            <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}" onsubmit="return confirm('Hapus pengumuman ini?');">
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
                        Halaman {{ $announcements->currentPage() }} dari {{ $announcements->lastPage() }}
                    </div>
                    <div class="button-row">
                        @if ($announcements->onFirstPage())
                            <span class="button button--secondary" style="opacity: 0.5; cursor: default;">Sebelumnya</span>
                        @else
                            <a href="{{ $announcements->previousPageUrl() }}" class="button button--secondary">Sebelumnya</a>
                        @endif
                        @if ($announcements->hasMorePages())
                            <a href="{{ $announcements->nextPageUrl() }}" class="button button--secondary">Berikutnya</a>
                        @else
                            <span class="button button--secondary" style="opacity: 0.5; cursor: default;">Berikutnya</span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
