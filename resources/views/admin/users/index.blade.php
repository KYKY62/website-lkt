@extends('admin.layout', ['title' => 'Manajemen Pengguna'])

@section('content')
    <section class="admin-pagehead">
        <div>
            <p class="admin-brand__eyebrow">Super Admin</p>
            <h1>Manajemen Pengguna Admin</h1>
            <p>Super admin dapat menambahkan akun editor berita dan mengatur role akses panel admin.</p>
        </div>

        <a href="{{ route('admin.users.create') }}" class="button button--primary">Tambah Pengguna</a>
    </section>

    <section class="card">
        <div class="card__body">
            @if ($users->isEmpty())
                <div class="empty-state">Belum ada pengguna admin tersimpan.</div>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>
                                    <div class="table-title">{{ $user->name }}</div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <span class="badge {{ $user->role === 'super_admin' ? 'badge--published' : 'badge--draft' }}">
                                        {{ $user->roleLabel() }}
                                    </span>
                                </td>
                                <td>{{ $user->created_at?->format('d M Y H:i') }}</td>
                                <td>
                                    <div class="button-row">
                                        <a href="{{ route('admin.users.edit', $user) }}" class="button button--secondary">Edit</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </section>
@endsection
