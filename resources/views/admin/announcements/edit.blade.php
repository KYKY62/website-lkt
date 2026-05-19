@extends('admin.layout', ['title' => 'Edit Pengumuman'])

@section('content')
    <section class="admin-pagehead">
        <div>
            <p class="admin-brand__eyebrow">Pengumuman</p>
            <h1>Edit Pengumuman</h1>
            <p>Perbarui konten, file, status, dan tanggal publikasi pengumuman.</p>
        </div>
    </section>

    <form method="POST" action="{{ route('admin.announcements.update', $announcement) }}" enctype="multipart/form-data">
        @method('PUT')
        @include('admin.announcements._form', ['submitLabel' => 'Simpan Perubahan'])
    </form>
@endsection
