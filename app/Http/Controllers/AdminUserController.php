<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::query()
                ->orderByRaw("case when role = 'super_admin' then 0 else 1 end")
                ->orderBy('name')
                ->paginate(12),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'user' => new User([
                'role' => User::ROLE_NEWS_EDITOR,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = User::query()->create($this->validatedPayload($request));

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('status', 'Pengguna admin berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $user->update($this->validatedPayload($request, $user));

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('status', 'Data pengguna berhasil diperbarui.');
    }

    private function validatedPayload(Request $request, ?User $user = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120', Rule::unique('users', 'email')->ignore($user?->id)],
            'role' => ['required', Rule::in(User::roles())],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
        ]);

        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        }

        $validated['email_verified_at'] = $user?->email_verified_at ?? now();

        return $validated;
    }
}
