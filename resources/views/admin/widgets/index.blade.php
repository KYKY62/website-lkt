@extends('admin.layout', ['title' => 'Widget Halaman'])

@section('content')
    <section class="admin-pagehead">
        <div>
            <p class="admin-brand__eyebrow">Modul Konten</p>
            <h1>Widget Halaman</h1>
            <p>Kelola area dua kolom sebelum footer untuk beranda, modul bawaan, dan halaman statis.</p>
        </div>

        <a href="{{ route('admin.widgets.create') }}" class="button button--primary">Tambah Widget</a>
    </section>

    <section class="card">
        <div class="card__body">
            <form method="GET" action="{{ route('admin.widgets.index') }}" class="admin-filter">
                <div class="field">
                    <label for="target_key">Target Halaman</label>
                    <select id="target_key" name="target_key">
                        <option value="">Semua halaman</option>
                        @foreach ($targetOptions as $option)
                            <option value="{{ $option['value'] }}" @selected(request('target_key') === $option['value'])>{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label for="display_area">Area Tampil</label>
                    <select id="display_area" name="display_area">
                        <option value="">Semua area</option>
                        @foreach ($areaOptions as $value => $label)
                            <option value="{{ $value }}" @selected(request('display_area') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">Semua status</option>
                        @foreach ($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label for="widget_type">Tipe</label>
                    <select id="widget_type" name="widget_type">
                        <option value="">Semua tipe</option>
                        @foreach ($typeOptions as $value => $label)
                            <option value="{{ $value }}" @selected(request('widget_type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label for="column">Kolom</label>
                    <select id="column" name="column">
                        <option value="">Semua kolom</option>
                        @foreach ($columnOptions as $value => $label)
                            <option value="{{ $value }}" @selected(request('column') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="button-row admin-filter__actions">
                    <button type="submit" class="button button--primary">Terapkan</button>
                    <a href="{{ route('admin.widgets.index') }}" class="button button--secondary">Reset</a>
                </div>
            </form>

            <div class="toolbar">
                <div>
                    <strong>{{ $widgets->total() }}</strong> widget tersimpan
                </div>
                <div style="color: var(--ink-soft); font-size: 0.9rem;">Path modul: <code>/admin/widgets</code></div>
            </div>

            @if ($widgets->count() === 0)
                <div class="empty-state">
                    Belum ada widget halaman. Tambahkan widget published agar area pre-footer muncul pada halaman target.
                </div>
            @else
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Widget</th>
                                <th>Target</th>
                                <th>Area</th>
                                <th>Kolom</th>
                                <th>Tipe</th>
                                <th>Status</th>
                                <th>Urutan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($widgets as $widget)
                                <tr>
                                    <td>
                                        <div class="table-title">{{ $widget->title }}</div>
                                        <div class="table-subtitle">Update: {{ $widget->updated_at?->format('d M Y H:i') }}</div>
                                    </td>
                                    <td>{{ $widget->targetLabel() }}</td>
                                    <td>{{ $areaOptions[$widget->display_area] ?? $widget->display_area }}</td>
                                    <td>{{ $columnOptions[$widget->column] ?? $widget->column }}</td>
                                    <td>{{ $typeOptions[$widget->widget_type] ?? $widget->widget_type }}</td>
                                    <td>
                                        <span class="badge {{ $widget->status === 'published' ? 'badge--published' : 'badge--draft' }}">
                                            {{ $statusOptions[$widget->status] ?? $widget->status }}
                                        </span>
                                    </td>
                                    <td>{{ $widget->sort_order }}</td>
                                    <td>
                                        <div class="button-row">
                                            <a href="{{ route('admin.widgets.edit', $widget) }}" class="button button--secondary">Edit</a>
                                            @if ($widget->targetPath())
                                                <a href="{{ url($widget->targetPath()) }}" class="button button--secondary">Preview</a>
                                            @endif
                                            <form method="POST" action="{{ route('admin.widgets.destroy', $widget) }}" onsubmit="return confirm('Hapus widget ini?');">
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
                        Halaman {{ $widgets->currentPage() }} dari {{ $widgets->lastPage() }}
                    </div>

                    <div class="button-row">
                        @if ($widgets->onFirstPage())
                            <span class="button button--secondary" style="opacity: 0.5; cursor: default;">Sebelumnya</span>
                        @else
                            <a href="{{ $widgets->previousPageUrl() }}" class="button button--secondary">Sebelumnya</a>
                        @endif

                        @if ($widgets->hasMorePages())
                            <a href="{{ $widgets->nextPageUrl() }}" class="button button--secondary">Berikutnya</a>
                        @else
                            <span class="button button--secondary" style="opacity: 0.5; cursor: default;">Berikutnya</span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
