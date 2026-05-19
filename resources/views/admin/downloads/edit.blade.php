@extends('admin.layout', ['title' => 'Edit Dokumen'])

@section('content')
    <section class="admin-pagehead">
        <div>
            <p class="admin-brand__eyebrow">Download</p>
            <h1>Edit Dokumen</h1>
            <p>Perbarui dokumen, kategori, file, dan status publikasi.</p>
        </div>
    </section>

    <form method="POST" action="{{ route('admin.downloads.update', $download) }}" enctype="multipart/form-data">
        @method('PUT')
        @include('admin.downloads._form', ['submitLabel' => 'Simpan Perubahan'])
    </form>
@endsection
