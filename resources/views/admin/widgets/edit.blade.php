@extends('admin.layout', ['title' => 'Edit Widget Halaman'])

@section('content')
    <section class="admin-pagehead">
        <div>
            <p class="admin-brand__eyebrow">Widget Halaman</p>
            <h1>Edit Widget</h1>
            <p>Perbarui konten pre-footer tanpa mengubah struktur halaman utamanya.</p>
        </div>
    </section>

    <form method="POST" action="{{ route('admin.widgets.update', $widget) }}" enctype="multipart/form-data">
        @method('PUT')
        @include('admin.widgets._form', [
            'submitLabel' => 'Simpan Perubahan',
        ])
    </form>
@endsection
