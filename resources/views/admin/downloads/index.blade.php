@extends('admin.layout', ['title' => 'Download'])

@section('content')
    <section class="admin-pagehead">
        <div>
            <p class="admin-brand__eyebrow">Modul Konten</p>
            <h1>Download</h1>
            <p>Kelola dokumen publik dan file resmi yang tersedia untuk diunduh masyarakat.</p>
        </div>

        <a href="{{ route('admin.downloads.create') }}" class="button button--primary">Tambah Dokumen</a>
    </section>

    <section class="card">
        <div class="card__body">
            <form method="GET" action="{{ route('admin.downloads.index') }}" class="admin-filter admin-filter--compact">
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
                    <a href="{{ route('admin.downloads.index') }}" class="button button--secondary">Reset</a>
                </div>
            </form>

            <div class="toolbar">
                <div><strong>{{ $downloads->total() }}</strong> dokumen tersimpan</div>
                <div style="color: var(--ink-soft); font-size: 0.9rem;">Path modul: <code>/admin/downloads</code></div>
            </div>

            @if ($downloads->isEmpty())
                <div class="empty-state">Belum ada dokumen download tersimpan.</div>
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
                            @foreach ($downloads as $download)
                                <tr>
                                    <td>
                                        <div class="table-title">{{ $download->title }}</div>
                                        <div class="table-subtitle">Slug: {{ $download->slug }}{{ $download->legacy_id ? ' | Legacy ID: '.$download->legacy_id : '' }}</div>
                                    </td>
                                    <td>{{ $download->category }}</td>
                                    <td>
                                        <span class="badge {{ $download->status === 'published' ? 'badge--published' : 'badge--draft' }}">
                                            {{ $statusOptions[$download->status] ?? $download->status }}
                                        </span>
                                    </td>
                                    <td>{{ $download->published_at?->format('d M Y H:i') ?? '-' }}</td>
                                    <td>{{ $download->file_name ?: '-' }}</td>
                                    <td>
                                        <div class="button-row">
                                            <a href="{{ route('admin.downloads.edit', $download) }}" class="button button--secondary">Edit</a>
                                            @if ($download->file_path)
                                                <a href="{{ route('downloads.file', ['download' => $download->slug]) }}" class="button button--secondary">Unduh</a>
                                            @endif
                                            <form method="POST" action="{{ route('admin.downloads.destroy', $download) }}" onsubmit="return confirm('Hapus dokumen ini?');">
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
            @endif
        </div>
    </section>
@endsection
