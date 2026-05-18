@extends('admin.layout', ['title' => 'Tambah Pengguna'])

@section('content')
    <section class="admin-pagehead">
        <div>
            <p class="admin-brand__eyebrow">Super Admin</p>
            <h1>Tambah Pengguna Admin</h1>
            <p>Buat akun baru untuk super admin atau editor berita.</p>
        </div>
    </section>

    <form method="POST" action="{{ route('admin.users.store') }}">
        @include('admin.users._form', [
            'user' => $user,
            'submitLabel' => 'Simpan Pengguna',
            'requirePassword' => true,
        ])
    </form>
@endsection
