@extends('admin.layout', ['title' => 'Edit Halaman'])

@section('content')
    <section class="admin-pagehead">
        <div>
            <p class="admin-brand__eyebrow">Super Admin</p>
            <h1>Edit Halaman Statis</h1>
            <p>Perbarui judul, path, status publikasi, dan isi konten halaman statis.</p>
        </div>
    </section>

    <form method="POST" action="{{ route('admin.pages.update', $page) }}">
        @method('PUT')
        @include('admin.pages._form', [
            'page' => $page,
            'submitLabel' => 'Simpan Perubahan',
        ])
    </form>
@endsection
