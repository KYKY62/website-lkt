@extends('admin.layout', ['title' => 'Tambah Pengumuman'])

@section('content')
    <section class="admin-pagehead">
        <div>
            <p class="admin-brand__eyebrow">Pengumuman</p>
            <h1>Tambah Pengumuman</h1>
            <p>Buat informasi resmi yang akan tampil di kanal pengumuman publik.</p>
        </div>
    </section>

    <form method="POST" action="{{ route('admin.announcements.store') }}" enctype="multipart/form-data">
        @include('admin.announcements._form', ['submitLabel' => 'Simpan Pengumuman'])
    </form>
@endsection
