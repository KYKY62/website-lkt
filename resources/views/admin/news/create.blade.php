@extends('admin.layout', ['title' => 'Tambah Berita'])

@section('content')
    <section class="admin-pagehead">
        <div>
            <p class="admin-brand__eyebrow">Manajemen Berita</p>
            <h1>Tambah Berita</h1>
            <p>Buat artikel baru untuk website publik. Draft dapat disimpan lebih dulu sebelum diterbitkan.</p>
        </div>
    </section>

    <form method="POST" action="{{ route('admin.news.store') }}" enctype="multipart/form-data">
        @include('admin.news._form', [
            'article' => $article,
            'submitLabel' => 'Simpan Berita',
        ])
    </form>
@endsection
