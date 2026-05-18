@extends('admin.layout', ['title' => 'Tambah Menu'])

@section('content')
    <section class="admin-pagehead">
        <div>
            <p class="admin-brand__eyebrow">Super Admin</p>
            <h1>Tambah Menu</h1>
            <p>Buat menu baru untuk navigasi utama atau submenu website.</p>
        </div>
    </section>

    <form method="POST" action="{{ route('admin.menus.store') }}">
        @include('admin.menus._form', [
            'menu' => $menu,
            'pages' => $pages,
            'parentMenus' => $parentMenus,
            'moduleOptions' => $moduleOptions,
            'submitLabel' => 'Simpan Menu',
        ])
    </form>
@endsection
