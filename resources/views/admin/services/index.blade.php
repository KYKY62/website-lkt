@extends('admin.layout', ['title' => 'Layanan'])

@section('content')
    <section class="admin-pagehead">
        <div>
            <p class="admin-brand__eyebrow">Modul Konten</p>
            <h1>Layanan</h1>
            <p>Kelola shortcut aplikasi atau halaman layanan yang diselenggarakan perangkat daerah.</p>
        </div>

        <a href="{{ route('admin.services.create') }}" class="button button--primary">Tambah Layanan</a>
    </section>

    <section class="card">
        <div class="card__body">
            <form method="GET" action="{{ route('admin.services.index') }}" class="admin-filter admin-filter--compact">
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
                    <a href="{{ route('admin.services.index') }}" class="button button--secondary">Reset</a>
                </div>
            </form>

            <div class="toolbar">
                <div>
                    <strong>{{ $services->total() }}</strong> layanan tersimpan
                </div>
                <div style="color: var(--ink-soft); font-size: 0.9rem;">Path modul: <code>/admin/services</code></div>
            </div>

            @if ($services->count() === 0)
                <div class="empty-state">Belum ada layanan tersimpan.</div>
            @else
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Layanan</th>
                                <th>Penyelenggara</th>
                                <th>Status</th>
                                <th>Urutan</th>
                                <th>Link</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($services as $service)
                                <tr>
                                    <td>
                                        <div class="admin-service-title">
                                            @if ($service->logoUrl())
                                                <img src="{{ $service->logoUrl() }}" alt="Logo {{ $service->title }}" class="admin-service-logo">
                                            @else
                                                <span class="admin-service-logo admin-service-logo--text">{{ mb_substr($service->title, 0, 2) }}</span>
                                            @endif
                                            <div>
                                                <div class="table-title">{{ $service->title }}</div>
                                                <div class="table-subtitle">{{ \Illuminate\Support\Str::limit($service->description, 120) }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $service->organizer }}</td>
                                    <td>
                                        <span class="badge {{ $service->status === 'published' ? 'badge--published' : 'badge--draft' }}">
                                            {{ $statusOptions[$service->status] ?? $service->status }}
                                        </span>
                                    </td>
                                    <td>{{ $service->sort_order }}</td>
                                    <td>
                                        <a href="{{ $service->link_url }}" target="{{ $service->link_target }}" rel="{{ $service->link_target === '_blank' ? 'noopener noreferrer' : '' }}" class="section-link">
                                            Buka link
                                        </a>
                                    </td>
                                    <td>
                                        <div class="button-row">
                                            <a href="{{ route('admin.services.edit', $service) }}" class="button button--secondary">Edit</a>
                                            <form method="POST" action="{{ route('admin.services.destroy', $service) }}" onsubmit="return confirm('Hapus layanan ini?');">
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
                        Halaman {{ $services->currentPage() }} dari {{ $services->lastPage() }}
                    </div>

                    <div class="button-row">
                        @if ($services->onFirstPage())
                            <span class="button button--secondary" style="opacity: 0.5; cursor: default;">Sebelumnya</span>
                        @else
                            <a href="{{ $services->previousPageUrl() }}" class="button button--secondary">Sebelumnya</a>
                        @endif

                        @if ($services->hasMorePages())
                            <a href="{{ $services->nextPageUrl() }}" class="button button--secondary">Berikutnya</a>
                        @else
                            <span class="button button--secondary" style="opacity: 0.5; cursor: default;">Berikutnya</span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
