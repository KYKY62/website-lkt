@extends('admin.layout', ['title' => 'Tambah Halaman'])

@section('content')
    <section class="admin-pagehead">
        <div>
            <p class="admin-brand__eyebrow">Super Admin</p>
            <h1>Tambah Halaman Statis</h1>
            <p>Buat halaman konten statis yang bisa dipakai untuk profil tambahan, visi misi, layanan tertentu, atau konten informatif lainnya.</p>
        </div>
    </section>

    <form method="POST" action="{{ route('admin.pages.store') }}">
        @include('admin.pages._form', [
            'page' => $page,
            'submitLabel' => 'Simpan Halaman',
        ])
    </form>
@endsection
