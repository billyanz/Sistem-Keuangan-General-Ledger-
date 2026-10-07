@extends('layouts.app')

@section('title', 'Daftar Akun')
@section('page-title', 'Daftar Akun')
@section('page-subtitle', 'Kelola struktur akun dan saldo normal perusahaan.')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">{{ session('error') }}</div>
    @endif

    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[.16em] text-emerald-700">Master data</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Chart of Accounts</h1>
            <p class="mt-1 text-sm text-slate-500">Satu sumber terstruktur untuk seluruh pencatatan keuangan.</p>
        </div>
        <a href="{{ route('accounts.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#174735] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#103626]">
            <span class="text-lg leading-none">+</span> Tambah akun
        </a>
    </div>

    <div class="grid grid-cols-2 gap-3 md:grid-cols-5">
        @foreach(['asset' => 'Aset', 'liability' => 'Liabilitas', 'equity' => 'Ekuitas', 'revenue' => 'Pendapatan', 'expense' => 'Beban'] as $type => $label)
            <a href="{{ route('accounts.index', array_merge(request()->except('page'), ['type' => request('type') === $type ? null : $type])) }}"
               class="rounded-xl border p-4 transition {{ request('type') === $type ? 'border-emerald-700 bg-emerald-50' : 'border-slate-200 bg-white hover:border-emerald-300' }}">
                <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $label }}</span>
                <span class="mt-1 block text-sm font-semibold text-slate-800">{{ ucfirst($type) }}</span>
            </a>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-slate-100 p-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-800">Bagan akun</h2>
                <p class="mt-0.5 text-xs text-slate-400">{{ $accounts->total() }} akun terdaftar</p>
            </div>
            <form method="GET" action="{{ route('accounts.index') }}" class="flex gap-2">
                @if(request('type'))<input type="hidden" name="type" value="{{ request('type') }}">@endif
                <input name="search" value="{{ request('search') }}" placeholder="Cari kode atau nama akun" class="w-full rounded-lg border-slate-200 text-sm focus:border-emerald-600 focus:ring-emerald-600 sm:w-64">
                <button class="rounded-lg border border-slate-200 px-3 text-sm font-medium text-slate-600 hover:bg-slate-50">Cari</button>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-left text-sm">
                <thead class="bg-slate-50 text-[10px] uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-5 py-3">Kode akun</th>
                        <th class="px-5 py-3">Nama akun</th>
                        <th class="px-5 py-3">Kategori</th>
                        <th class="px-5 py-3">Saldo normal</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($accounts as $account)
                        <tr class="transition hover:bg-slate-50/80">
                            <td class="whitespace-nowrap px-5 py-4 font-mono text-xs font-semibold text-emerald-800">{{ $account->code }}</td>
                            <td class="px-5 py-4">
                                <span class="font-semibold text-slate-800">{{ $account->name }}</span>
                                @if($account->parent)
                                    <span class="mt-0.5 block text-xs text-slate-400">Induk: {{ $account->parent->code }} · {{ $account->parent->name }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4"><span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-medium capitalize text-slate-600">{{ $account->type }}</span></td>
                            <td class="px-5 py-4 text-xs capitalize text-slate-600">{{ $account->normal_balance }}</td>
                            <td class="px-5 py-4"><span class="inline-flex items-center gap-1.5 text-xs {{ $account->is_active ? 'text-emerald-700' : 'text-slate-400' }}"><span class="h-1.5 w-1.5 rounded-full {{ $account->is_active ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>{{ $account->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('accounts.show', $account) }}" class="text-xs font-semibold text-emerald-800 hover:underline">Detail</a>
                                <span class="px-2 text-slate-300">·</span>
                                <a href="{{ route('accounts.edit', $account) }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">Ubah</a>
                                <form action="{{ route('accounts.destroy', $account) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus akun ini?');" class="ml-2 inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-xs font-semibold text-rose-600 hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-14 text-center"><span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-xl text-slate-400">▦</span><p class="mt-3 text-sm font-semibold text-slate-700">Belum ada akun</p><p class="mt-1 text-xs text-slate-400">Tambahkan akun pertama untuk memulai pencatatan.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($accounts->hasPages())<div class="border-t border-slate-100 px-5 py-4">{{ $accounts->links() }}</div>@endif
    </div>
</div>
@endsection
