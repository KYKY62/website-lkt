<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? 'Admin Panel' }} | Pemerintah Kabupaten Langkat</title>
        <link rel="icon" type="image/png" href="{{ Vite::asset('resources/img/logo_langkat.png') }}">
        <link rel="apple-touch-icon" href="{{ Vite::asset('resources/img/logo_langkat.png') }}">
        @vite(['resources/css/app.css'])
    </head>
    <body class="admin-body">
        <div class="admin-shell">
            <header class="admin-topbar">
                <div class="admin-topbar__inner">
                    <div class="admin-brand">
                        <div class="admin-brand__logo">
                            <img src="{{ Vite::asset('resources/img/logo_langkat.png') }}" alt="Lambang Kabupaten Langkat" class="admin-brand__logo-image">
                        </div>
                        <div>
                            <p class="admin-brand__eyebrow">Admin Panel</p>
                            <p class="admin-brand__title">Pemerintah Kabupaten Langkat</p>
                        </div>
                    </div>

                    <div class="admin-topbar__links">
                        <a href="{{ route('admin.news.index') }}" class="admin-link {{ request()->routeIs('admin.news.*') ? 'is-active' : '' }}">Manajemen Berita</a>
                        <a href="{{ route('admin.announcements.index') }}" class="admin-link {{ request()->routeIs('admin.announcements.*') ? 'is-active' : '' }}">Pengumuman</a>
                        <a href="{{ route('admin.downloads.index') }}" class="admin-link {{ request()->routeIs('admin.downloads.*') ? 'is-active' : '' }}">Download</a>
                        <a href="{{ route('admin.services.index') }}" class="admin-link {{ request()->routeIs('admin.services.*') ? 'is-active' : '' }}">Layanan</a>
                        <a href="{{ route('admin.department-news.edit') }}" class="admin-link {{ request()->routeIs('admin.department-news.*') ? 'is-active' : '' }}">Kabar OPD</a>
                        <a href="{{ route('admin.widgets.index') }}" class="admin-link {{ request()->routeIs('admin.widgets.*') ? 'is-active' : '' }}">Widget Halaman</a>
                        @if (auth()->user()->isSuperAdmin())
                            <a href="{{ route('admin.pages.index') }}" class="admin-link {{ request()->routeIs('admin.pages.*') ? 'is-active' : '' }}">Halaman Statis</a>
                            <a href="{{ route('admin.menus.index') }}" class="admin-link {{ request()->routeIs('admin.menus.*') ? 'is-active' : '' }}">Manajemen Menu</a>
                            <a href="{{ route('admin.users.index') }}" class="admin-link {{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}">Pengguna Admin</a>
                        @endif
                        <a href="{{ route('admin.account.edit') }}" class="admin-link {{ request()->routeIs('admin.account.*') ? 'is-active' : '' }}">Akun Admin</a>
                        <a href="{{ route('public-site') }}" class="admin-link admin-link--button">Lihat Website</a>
                        <div class="admin-user">
                            <div class="admin-user__meta">
                                <span class="admin-user__name">{{ auth()->user()->name }}</span>
                                <span class="admin-user__email">{{ auth()->user()->email }}</span>
                                <span class="admin-user__role">{{ auth()->user()->roleLabel() }}</span>
                            </div>
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit" class="button button--ghost">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <main class="admin-main">
                @if (session('status'))
                    <div class="flash">{{ session('status') }}</div>
                @endif

                @yield('content')
            </main>
        </div>
    </body>
</html>
