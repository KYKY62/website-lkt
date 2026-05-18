@extends('admin.layout', ['title' => 'Edit Pengguna'])

@section('content')
    <section class="admin-pagehead">
        <div>
            <p class="admin-brand__eyebrow">Super Admin</p>
            <h1>Edit Pengguna Admin</h1>
            <p>Perbarui nama, email, role, atau password untuk akun admin yang dipilih.</p>
        </div>
    </section>

    <form method="POST" action="{{ route('admin.users.update', $user) }}">
        @method('PUT')
        @include('admin.users._form', [
            'user' => $user,
            'submitLabel' => 'Simpan Perubahan',
            'requirePassword' => false,
        ])
    </form>
@endsection
