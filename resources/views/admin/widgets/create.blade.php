@extends('admin.layout', ['title' => 'Tambah Widget Halaman'])

@section('content')
    <section class="admin-pagehead">
        <div>
            <p class="admin-brand__eyebrow">Widget Halaman</p>
            <h1>Tambah Widget</h1>
            <p>Pilih halaman target, kolom, tipe widget, dan status publikasinya.</p>
        </div>
    </section>

    <form method="POST" action="{{ route('admin.widgets.store') }}" enctype="multipart/form-data">
        @include('admin.widgets._form', [
            'submitLabel' => 'Simpan Widget',
        ])
    </form>
@endsection
