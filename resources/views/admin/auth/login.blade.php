<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Login Admin | Pemerintah Kabupaten Langkat</title>
        <link rel="icon" type="image/png" href="{{ Vite::asset('resources/img/logo_langkat.png') }}">
        <link rel="apple-touch-icon" href="{{ Vite::asset('resources/img/logo_langkat.png') }}">
        @vite(['resources/css/app.css'])
    </head>
    <body class="auth-body">
        <main class="auth-body__content">
            <div class="auth-shell">
                <div class="auth-card">
                    <div class="auth-card__hero">
                        <div class="auth-badge">
                            <img src="{{ Vite::asset('resources/img/logo_langkat.png') }}" alt="Lambang Kabupaten Langkat" class="auth-badge__image">
                        </div>
                        <p class="auth-eyebrow">Admin Panel</p>
                        <h1 class="auth-title">Login Administrator</h1>
                        <p class="auth-copy">Masuk ke panel admin untuk mengelola berita, halaman statis, menu, dan modul portal Kabupaten Langkat.</p>
                    </div>

                    <div class="auth-body__panel">
                        @if (session('status'))
                            <div class="status-message status-message--info">{{ session('status') }}</div>
                        @endif

                        @if ($errors->any())
                            <div class="status-message status-message--error">{{ $errors->first() }}</div>
                        @endif

                        <form method="POST" action="{{ route('admin.login.store') }}" class="form-grid">
                            @csrf

                            <div class="field">
                                <label for="email">Email</label>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
                            </div>

                            <div class="field">
                                <label for="password">Password</label>
                                <input id="password" type="password" name="password" required>
                            </div>

                            <label class="checkbox" style="display: flex; align-items: center; gap: 0.65rem; color: var(--ink-soft); font-size: 0.92rem;">
                                <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                                <span>Ingat saya di perangkat ini</span>
                            </label>

                            <button type="submit" class="button-primary" style="width: 100%;">Masuk ke Admin</button>
                        </form>

                        <p class="auth-note">
                            Gunakan akun administrator resmi yang telah dibuat oleh super admin.
                        </p>
                    </div>
                </div>
            </div>
        </main>
    </body>
</html>
