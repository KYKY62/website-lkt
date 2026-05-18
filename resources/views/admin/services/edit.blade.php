@extends('admin.layout', ['title' => 'Edit Layanan'])

@section('content')
    <section class="admin-pagehead">
        <div>
            <p class="admin-brand__eyebrow">Layanan</p>
            <h1>Edit Layanan</h1>
            <p>Perbarui informasi shortcut layanan dan link aplikasi publik.</p>
        </div>
    </section>

    <form method="POST" action="{{ route('admin.services.update', $service) }}" enctype="multipart/form-data">
        @method('PUT')
        @include('admin.services._form', [
            'submitLabel' => 'Simpan Perubahan',
        ])
    </form>
@endsection
