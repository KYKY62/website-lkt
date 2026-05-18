@extends('admin.layout', ['title' => 'Halaman Statis'])

@section('content')
    <section class="admin-pagehead">
        <div>
            <p class="admin-brand__eyebrow">Super Admin</p>
            <h1>Halaman Statis</h1>
            <p>Kelola halaman konten statis yang dapat dipakai pada menu website atau diakses langsung lewat path publik.</p>
        </div>

        <a href="{{ route('admin.pages.create') }}" class="button button--primary">Tambah Halaman</a>
    </section>

    <section class="card">
        <div class="card__body">
            @if ($pages->isEmpty())
                <div class="empty-state">Belum ada halaman statis tersimpan.</div>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Path</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pages as $page)
                            <tr>
                                <td>
                                    <div class="table-title">{{ $page->title }}</div>
                                    <div class="table-subtitle">Slug: {{ $page->slug }}</div>
                                </td>
                                <td>{{ $page->path }}</td>
                                <td>
                                    <span class="badge {{ $page->status === 'published' ? 'badge--published' : 'badge--draft' }}">
                                        {{ $page->status }}
                                    </span>
                                </td>
                                <td>
                                    <div class="button-row">
                                        <a href="{{ route('admin.pages.edit', $page) }}" class="button button--secondary">Edit</a>
                                        <form method="POST" action="{{ route('admin.pages.destroy', $page) }}" onsubmit="return confirm('Hapus halaman ini?');">
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
            @endif
        </div>
    </section>
@endsection
