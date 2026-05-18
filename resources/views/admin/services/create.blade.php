@extends('admin.layout', ['title' => 'Tambah Layanan'])

@section('content')
    <section class="admin-pagehead">
        <div>
            <p class="admin-brand__eyebrow">Layanan</p>
            <h1>Tambah Layanan</h1>
            <p>Tambahkan shortcut menuju aplikasi atau halaman layanan perangkat daerah.</p>
        </div>
    </section>

    <form method="POST" action="{{ route('admin.services.store') }}" enctype="multipart/form-data">
        @include('admin.services._form', [
            'submitLabel' => 'Simpan Layanan',
        ])
    </form>
@endsection
