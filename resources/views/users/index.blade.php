@extends('layouts.app')

@section('title', 'Manajemen Pengguna')
@section('page-title', 'Kelola Pengguna & Hak Akses')
@section('page-subtitle', 'Daftar pengguna terdaftar dan alokasi role dalam sistem.')

@section('page-actions')
    <a href="{{ route('users.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-indigo-100 transition-all flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Tambah Pengguna Baru
    </a>
@endsection

@section('content')

@if(session('success'))
    <div class="mb-5 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-xl flex items-center justify-between">
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="mb-5 p-4 bg-rose-50 border border-rose-200 text-rose-700 text-sm rounded-xl flex items-center justify-between">
        <span>{{ session('error') }}</span>
    </div>
@endif

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-400 border-b border-slate-100">
                <tr>
                    <th class="px-6 py-3.5">Nama</th>
                    <th class="px-6 py-3.5">Email</th>
                    <th class="px-6 py-3.5">Role</th>
                    <th class="px-6 py-3.5 text-center">Tanggal Dibuat</th>
                    <th class="px-6 py-3.5 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($users as $u)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-6 py-4 font-semibold text-slate-800">
                            {{ $u->name }}
                            @if(auth()->id() === $u->id)
                                <span class="ml-1 px-2 py-0.5 text-[10px] bg-indigo-50 text-indigo-600 rounded-full font-normal">Anda</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-slate-600">{{ $u->email }}</td>
                        <td class="px-6 py-4">
                            @foreach($u->roles as $role)
                                @php
                                    $badgeStyle = match($role->name) {
                                        'Admin' => 'bg-purple-50 text-purple-700 border-purple-100',
                                        'Staff' => 'bg-indigo-50 text-indigo-700 border-indigo-100',
                                        default => 'bg-slate-50 text-slate-700 border-slate-100',
                                    };
                                @endphp
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-md border {{ $badgeStyle }}">
                                    {{ $role->name }}
                                </span>
                            @endforeach
                        </td>
                        <td class="px-6 py-4 text-center text-xs text-slate-400">
                            {{ $u->created_at?->format('d/m/Y H:i') ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('users.edit', $u->id) }}" class="p-1.5 text-slate-400 hover:text-indigo-600 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>
                                @if(auth()->id() !== $u->id)
                                    <form action="{{ route('users.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus pengguna ini?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-slate-400 text-sm">Belum ada data pengguna.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $users->links() }}
        </div>
    @endif
</div>

@endsection
