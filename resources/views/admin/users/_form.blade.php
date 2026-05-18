@csrf

<div class="card">
    <div class="card__body">
        <div class="form-grid">
            <div class="form-grid form-grid--two">
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

            <div class="form-grid form-grid--two">
                <div class="field">
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        <option value="super_admin" @selected(old('role', $user->role) === 'super_admin')>Super Admin</option>
                        <option value="news_editor" @selected(old('role', $user->role) === 'news_editor')>Editor Berita</option>
                    </select>
                    @error('role')
                        <div class="field__error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="password">Password {{ $requirePassword ? '' : '(opsional)' }}</label>
                    <input id="password" name="password" type="password" {{ $requirePassword ? 'required' : '' }}>
                    <small>{{ $requirePassword ? 'Minimal 8 karakter.' : 'Isi hanya jika ingin mengganti password.' }}</small>
                    @error('password')
                        <div class="field__error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="field">
                <label for="password_confirmation">Konfirmasi Password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" {{ $requirePassword ? 'required' : '' }}>
            </div>
        </div>
    </div>
</div>

<div class="button-row" style="margin-top: 1rem;">
    <button type="submit" class="button button--primary">{{ $submitLabel }}</button>
    <a href="{{ route('admin.users.index') }}" class="button button--secondary">Kembali</a>
</div>
