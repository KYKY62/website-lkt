@extends('admin.layout', ['title' => 'Edit Menu'])

@section('content')
    <section class="admin-pagehead">
        <div>
            <p class="admin-brand__eyebrow">Super Admin</p>
            <h1>Edit Menu</h1>
            <p>Perbarui posisi menu, jenis tujuan, dan hubungan master atau submenu.</p>
        </div>
    </section>

    <form method="POST" action="{{ route('admin.menus.update', $menu) }}">
        @method('PUT')
        @include('admin.menus._form', [
            'menu' => $menu,
            'pages' => $pages,
            'parentMenus' => $parentMenus,
            'moduleOptions' => $moduleOptions,
            'submitLabel' => 'Simpan Perubahan',
        ])
    </form>
@endsection
