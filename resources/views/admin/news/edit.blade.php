@extends('admin.layout', ['title' => 'Edit Berita'])

@section('content')
    <section class="admin-pagehead">
        <div>
            <p class="admin-brand__eyebrow">Manajemen Berita</p>
            <h1>Edit Berita</h1>
            <p>Perbarui isi berita, status publikasi, dan slug artikel sesuai kebutuhan editor.</p>
        </div>
    </section>

    <form method="POST" action="{{ route('admin.news.update', $article) }}" enctype="multipart/form-data">
        @method('PUT')
        @include('admin.news._form', [
            'article' => $article,
            'submitLabel' => 'Simpan Perubahan',
        ])
    </form>
@endsection
