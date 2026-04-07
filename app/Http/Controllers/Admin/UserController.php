<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::query()
                ->withCount('articles')
                ->orderByDesc('id')
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'user' => new User([
                'role' => 'wartawan',
                'is_active' => true,
            ]),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = new User;
        $user->fill($this->payload($request->validated(), true));
        $user->save();

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('status', 'User berhasil dibuat.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user->loadCount('articles'),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $payload = $this->payload($request->validated(), false);
        $actor = $request->user();

        if ($actor->is($user)) {
            if (($payload['role'] ?? $user->role) !== 'admin') {
                throw ValidationException::withMessages([
                    'role' => 'Admin tidak dapat menurunkan role akun sendiri.',
                ]);
            }

            if (($payload['is_active'] ?? $user->is_active) === false) {
                throw ValidationException::withMessages([
                    'is_active' => 'Admin tidak dapat menonaktifkan akun sendiri.',
                ]);
            }
        }

        $user->fill($payload);
        $user->save();

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('status', 'User berhasil diperbarui.');
    }

    public function destroy(User $user, \Illuminate\Http\Request $request): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return redirect()
                ->route('admin.users.index')
                ->with('status', 'Akun yang sedang dipakai tidak dapat dihapus.');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User berhasil dihapus.');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function payload(array $data, bool $creating): array
    {
        $payload = [
            'name' => trim((string) $data['name']),
            'email' => strtolower(trim((string) $data['email'])),
            'role' => (string) $data['role'],
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];

        $password = (string) ($data['password'] ?? '');

        if ($creating || trim($password) !== '') {
            $payload['password'] = $password;
        }

        return $payload;
    }
}
