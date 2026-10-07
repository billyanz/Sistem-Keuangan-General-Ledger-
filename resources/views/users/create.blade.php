@extends('layouts.app')

@section('title', 'Tambah Pengguna Baru')
@section('page-title', 'Tambah Pengguna Baru')
@section('page-subtitle', 'Registrasikan pengguna baru dan tetapkan role hak akses (Admin vs Staff).')

@section('content')

<div class="max-w-2xl bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
    <form action="{{ route('users.store') }}" method="POST" class="space-y-5">
        @csrf

        <!-- Nama Lengkap -->
        <div>
            <label for="name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="Contoh: Ahmad Subagja"
                   class="w-full px-4 py-2.5 bg-slate-50 border @error('name') border-rose-500 @else border-slate-200 @enderror rounded-xl text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500">
            @error('name')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Alamat Email -->
        <div>
            <label for="email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Alamat Email</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required placeholder="Contoh: ahmad@perusahaan.com"
                   class="w-full px-4 py-2.5 bg-slate-50 border @error('email') border-rose-500 @else border-slate-200 @enderror rounded-xl text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500">
            @error('email')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Role Pengguna -->
        <div>
            <label for="role" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Pilih Role / Hak Akses</label>
            <select name="role" id="role" required
                    class="w-full px-4 py-2.5 bg-slate-50 border @error('role') border-rose-500 @else border-slate-200 @enderror rounded-xl text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500">
                <option value="" disabled selected>-- Pilih Role --</option>
                @foreach($roles as $role)
                    <option value="{{ $role }}" {{ old('role') == $role ? 'selected' : '' }}>{{ $role }}</option>
                @endforeach
            </select>
            @error('role')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Kata Sandi -->
        <div>
            <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Kata Sandi</label>
            <input type="password" name="password" id="password" required placeholder="Minimal 8 karakter"
                   class="w-full px-4 py-2.5 bg-slate-50 border @error('password') border-rose-500 @else border-slate-200 @enderror rounded-xl text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500">
            @error('password')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Konfirmasi Kata Sandi -->
        <div>
            <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Konfirmasi Kata Sandi</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required placeholder="Ulangi kata sandi"
                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500">
        </div>

        <!-- Tombol Aksi -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="{{ route('users.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition-colors">
                Batal
            </a>
            <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-md shadow-indigo-100 transition-all">
                Simpan Pengguna
            </button>
        </div>
    </form>
</div>

@endsection
