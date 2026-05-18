@extends('admin.layout', ['title' => 'Akun Admin'])

@section('content')
    <section class="admin-pagehead">
        <div>
            <p class="admin-brand__eyebrow">Akun Admin</p>
            <h1>Profil dan Keamanan</h1>
            <p>Kelola identitas akun administrator dan ubah password untuk menjaga keamanan panel.</p>
        </div>
    </section>

    <div class="form-grid form-grid--two">
        <form method="POST" action="{{ route('admin.account.profile.update') }}">
            @csrf
            @method('PUT')
            <div class="card">
                <div class="card__body">
                    <div class="field">
                        <label for="name">Nama</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="button-row" style="margin-top: 1rem;">
                <button type="submit" class="button button--primary">Simpan Profil</button>
            </div>
        </form>

        <form method="POST" action="{{ route('admin.account.password.update') }}">
            @csrf
            @method('PUT')
            <div class="card">
                <div class="card__body">
                    <div class="field">
                        <label for="current_password">Password Saat Ini</label>
                        <input id="current_password" name="current_password" type="password" required>
                        @error('current_password')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="password">Password Baru</label>
                        <input id="password" name="password" type="password" required>
                        @error('password')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="password_confirmation">Konfirmasi Password Baru</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required>
                    </div>
                </div>
            </div>

            <div class="button-row" style="margin-top: 1rem;">
                <button type="submit" class="button button--primary">Ubah Password</button>
            </div>
        </form>
    </div>
@endsection
