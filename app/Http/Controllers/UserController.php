<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Tampilkan daftar seluruh pengguna beserta role-nya.
     */
    public function index()
    {
        $users = User::with('roles')->latest()->paginate(10);

        return view('users.index', compact('users'));
    }

    /**
     * Tampilkan form pembuatan pengguna baru.
     */
    public function create()
    {
        $roles = Role::pluck('name', 'name');

        return view('users.create', compact('roles'));
    }

    /**
     * Simpan pengguna baru dan assign role.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role'     => ['required', 'string', 'exists:roles,name'],
        ], [
            'name.required'     => 'Nama lengkap wajib diisi.',
            'email.required'    => 'Alamat email wajib diisi.',
            'email.unique'      => 'Email sudah terdaftar.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min'      => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'role.required'     => 'Role pengguna wajib dipilih.',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('users.index')
            ->with('success', 'Pengguna ' . $user->name . ' berhasil ditambahkan.');
    }

    /**
     * Tampilkan form edit pengguna dan role.
     */
    public function edit(User $user)
    {
        $roles = Role::pluck('name', 'name');
        $userRole = $user->roles->pluck('name')->first();

        return view('users.edit', compact('user', 'roles', 'userRole'));
    }

    /**
     * Perbarui data pengguna dan role.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role'     => ['required', 'string', 'exists:roles,name'],
        ], [
            'name.required'  => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.unique'   => 'Email sudah digunakan oleh akun lain.',
            'password.min'   => 'Kata sandi minimal 8 karakter.',
            'role.required'  => 'Role pengguna wajib dipilih.',
        ]);

        $userData = [
            'name'  => $validated['name'],
            'email' => $validated['email'],
        ];

        // Hanya update password jika diisi
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $user->update($userData);
        $user->syncRoles([$validated['role']]);

        return redirect()->route('users.index')
            ->with('success', 'Data pengguna ' . $user->name . ' berhasil diperbarui.');
    }

    /**
     * Hapus pengguna dari sistem.
     */
        public function destroy(User $user)
{
        // Mencegah pengguna menghapus akunnya sendiri
        if (request()->user()->id === $user->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Pengguna ' . $userName . ' berhasil dihapus.');
    }
}
