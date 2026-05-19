@extends('admin.layout', ['title' => 'Tambah Dokumen'])

@section('content')
    <section class="admin-pagehead">
        <div>
            <p class="admin-brand__eyebrow">Download</p>
            <h1>Tambah Dokumen</h1>
            <p>Tambahkan dokumen publik yang dapat diunduh masyarakat.</p>
        </div>
    </section>

    <form method="POST" action="{{ route('admin.downloads.store') }}" enctype="multipart/form-data">
        @include('admin.downloads._form', ['submitLabel' => 'Simpan Dokumen'])
    </form>
@endsection
