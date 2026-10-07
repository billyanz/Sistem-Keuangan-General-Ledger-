@extends('layouts.app')

@section('title', 'Laporan Laba Rugi')
@section('page-title', 'Laporan Laba Rugi')
@section('page-subtitle', 'Ringkasan kinerja pendapatan dan beban dalam periode terpilih.')

@section('content')
<div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm mb-6 print:hidden">
    <form action="{{ route('reports.profit-loss') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
        <div>
            <label for="branch_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Cabang</label>
            <select name="branch_id" id="branch_id" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500">
                <option value="">Semua cabang</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" @selected((string) $branchId === (string) $branch->id)>{{ $branch->code }} - {{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="start_date" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Mulai tanggal</label>
            <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label for="end_date" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Sampai tanggal</label>
            <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500">
        </div>
        <button type="submit" class="py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-xl shadow-md shadow-indigo-100 transition-all">
            Tampilkan laporan
        </button>
    </form>
</div>

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8">
    <div class="text-center border-b border-slate-100 pb-6 mb-6">
        <h2 class="text-xl font-bold uppercase tracking-wider text-slate-900">Sistem Informasi Keuangan</h2>
        <h3 class="text-lg font-semibold text-slate-700">Laporan Laba Rugi</h3>
        <p class="text-xs text-slate-400 mt-1">
            {{ $selectedBranch ? 'Cabang: ' . $selectedBranch->code . ' - ' . $selectedBranch->name : 'Konsolidasi (Seluruh Cabang)' }}
        </p>
        <p class="text-xs text-slate-400 mt-1">
            Periode: <span class="font-semibold text-slate-600">{{ date('d/m/Y', strtotime($startDate)) }}</span> s/d <span class="font-semibold text-slate-600">{{ date('d/m/Y', strtotime($endDate)) }}</span>
        </p>
    </div>

    <div class="space-y-8">
        <div>
            <h4 class="text-sm font-bold uppercase text-slate-800 border-b-2 border-slate-800 pb-1.5 mb-3">1. Pendapatan</h4>
            <table class="w-full text-sm">
                <tbody class="divide-y divide-slate-100">
                    @forelse($revenues as $revenue)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-2.5 font-mono text-slate-500 w-28">{{ $revenue->code }}</td>
                            <td class="py-2.5 text-slate-800 font-medium">{{ $revenue->name }}</td>
                            <td class="py-2.5 text-right font-medium text-slate-700 w-44">Rp {{ number_format($revenue->amount, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-3 text-slate-400 italic text-xs">Tidak ada data pendapatan pada periode ini.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="font-bold text-slate-800 bg-slate-50/80 border-t border-slate-200">
                        <td colspan="2" class="py-2.5 pl-3 uppercase text-xs tracking-wider">Total pendapatan</td>
                        <td class="py-2.5 pr-3 text-right text-indigo-600 font-mono text-base">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div>
            <h4 class="text-sm font-bold uppercase text-slate-800 border-b-2 border-slate-800 pb-1.5 mb-3">2. Beban operasional</h4>
            <table class="w-full text-sm">
                <tbody class="divide-y divide-slate-100">
                    @forelse($expenses as $expense)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-2.5 font-mono text-slate-500 w-28">{{ $expense->code }}</td>
                            <td class="py-2.5 text-slate-800 font-medium">{{ $expense->name }}</td>
                            <td class="py-2.5 text-right font-medium text-slate-700 w-44">Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-3 text-slate-400 italic text-xs">Tidak ada data beban pada periode ini.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="font-bold text-slate-800 bg-slate-50/80 border-t border-slate-200">
                        <td colspan="2" class="py-2.5 pl-3 uppercase text-xs tracking-wider">Total beban operasional</td>
                        <td class="py-2.5 pr-3 text-right text-rose-600 font-mono text-base">Rp {{ number_format($totalExpense, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="pt-4 border-t-2 border-slate-900">
            <div class="p-4 rounded-xl flex items-center justify-between {{ $netProfit >= 0 ? 'bg-indigo-50 border border-indigo-100 text-indigo-900' : 'bg-rose-50 border border-rose-100 text-rose-900' }}">
                <div>
                    <span class="text-xs uppercase tracking-wider font-bold block text-slate-500">Hasil akhir periode</span>
                    <span class="text-base font-bold uppercase">{{ $netProfit >= 0 ? 'Laba bersih' : 'Rugi bersih' }}</span>
                </div>
                <span class="text-xl font-bold font-mono">Rp {{ number_format($netProfit, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
