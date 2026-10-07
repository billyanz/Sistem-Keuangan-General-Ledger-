@extends('layouts.app')
@section('title', 'Neraca')
@section('page-title', 'Neraca keuangan')
@section('page-subtitle', 'Posisi aset, liabilitas, dan ekuitas pada tanggal terpilih.')
@section('content')
    <div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- Header Page -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Laporan Neraca Keuangan (Balance Sheet)</h1>
                <p class="text-sm text-gray-500 mt-1">
                    Cabang: <span class="font-semibold text-indigo-600">{{ $selectedBranch ? $selectedBranch->code . ' - ' . $selectedBranch->name : 'Konsolidasi (Seluruh Cabang)' }}</span>
                </p>
            </div>
            <button onclick="window.print()" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-md hover:bg-gray-700 print:hidden">
                🖨️ Cetak / PDF
            </button>
        </div>

        <!-- Filter Card -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-6 print:hidden">
            <form method="GET" action="{{ route('reports.balance-sheet') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <!-- Filter Cabang -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Pilih Cabang</label>
                    <select name="branch_id" class="w-full border-gray-300 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- Konsolidasi (Semua Cabang) --</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ ($branchId ?? null) == $branch->id ? 'selected' : '' }}>
                                {{ $branch->code }} - {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Tanggal Posisi Neraca -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Per Tanggal (As of Date)</label>
                    <input type="date" name="as_of_date" value="{{ $asOfDate }}" class="w-full border-gray-300 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit" class="w-full px-5 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                        Tampilkan Neraca
                    </button>
                </div>
            </form>
        </div>

        <!-- Indikator Keseimbangan (Balance Banner) -->
        <div class="mb-6 print:hidden">
            @if($isBalanced)
                <div class="p-4 bg-green-100 border border-green-300 text-green-800 rounded-lg flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-lg">✓</span>
                        <span class="text-sm font-semibold">Neraca Keuangan SEIMBANG (Balanced). Total Aset = Total Kewajiban + Ekuitas.</span>
                    </div>
                    <span class="text-sm font-mono font-bold">Rp {{ number_format($totalAssets, 2, ',', '.') }}</span>
                </div>
            @else
                <div class="p-4 bg-red-100 border border-red-300 text-red-800 rounded-lg flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-lg">⚠️</span>
                        <span class="text-sm font-semibold">Neraca TIDAK SEIMBANG! Periksa kembali entri jurnal transaksi Anda.</span>
                    </div>
                    <span class="text-sm font-mono font-bold">Selisih: Rp {{ number_format(abs($totalAssets - $totalLiabilitiesAndEquity), 2, ',', '.') }}</span>
                </div>
            @endif
        </div>

        <!-- Sheet Neraca Keuangan Dua Kolom -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
            <!-- Header Dokumen Cetak -->
            <div class="text-center border-b pb-6 mb-6">
                <h2 class="text-xl font-bold uppercase tracking-wider text-gray-900">SISTEM INFORMASI KEUANGAN</h2>
                <h3 class="text-lg font-semibold text-gray-700">LAPORAN NERACA KEUANGAN</h3>
                <p class="text-sm text-gray-500">
                    Per Tanggal: {{ \Carbon\Carbon::parse($asOfDate)->locale('id')->isoFormat('D MMMM Y') }}
                </p>
            </div>

            <!-- Layout Side-by-Side (Aset vs Kewajiban & Ekuitas) -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                <!-- KOLOM KIRI: ASET (ASSETS) -->
                <div class="flex flex-col justify-between border-r-0 lg:border-r border-gray-200 pr-0 lg:pr-6">
                    <div>
                        <h3 class="text-md font-bold uppercase text-gray-800 border-b-2 border-gray-800 pb-1 mb-4">
                            1. ASET (ASSETS)
                        </h3>
                        <table class="w-full text-sm mb-6">
                            <tbody>
                                @forelse($assets as $asset)
                                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                                        <td class="py-2 font-mono text-gray-500 w-24">{{ $asset->code }}</td>
                                        <td class="py-2 text-gray-800">{{ $asset->name }}</td>
                                        <td class="py-2 text-right font-medium w-36">Rp {{ number_format($asset->amount, 2, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-2 text-gray-400 italic">Tidak ada saldo aset.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Total Aset -->
                    <div class="border-t-2 border-gray-900 pt-3 bg-indigo-50 p-3 rounded">
                        <div class="flex justify-between items-center font-bold text-indigo-900">
                            <span class="uppercase">TOTAL ASET</span>
                            <span class="text-base font-mono">Rp {{ number_format($totalAssets, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <!-- KOLOM KANAN: KEWAJIBAN & EKUITAS -->
                <div class="flex flex-col justify-between">
                    <div>
                        <!-- 2. KEWAJIBAN (LIABILITIES) -->
                        <div class="mb-6">
                            <h3 class="text-md font-bold uppercase text-gray-800 border-b-2 border-gray-800 pb-1 mb-4">
                                2. KEWAJIBAN (LIABILITIES)
                            </h3>
                            <table class="w-full text-sm">
                                <tbody>
                                    @forelse($liabilities as $liab)
                                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                                            <td class="py-2 font-mono text-gray-500 w-24">{{ $liab->code }}</td>
                                            <td class="py-2 text-gray-800">{{ $liab->name }}</td>
                                            <td class="py-2 text-right font-medium w-36">Rp {{ number_format($liab->amount, 2, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="py-2 text-gray-400 italic">Tidak ada saldo kewajiban.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="font-semibold text-gray-700 bg-gray-50">
                                        <td colspan="2" class="py-2 pl-2">SUBTOTAL KEWAJIBAN</td>
                                        <td class="py-2 text-right">Rp {{ number_format($totalLiabilities, 2, ',', '.') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- 3. EKUITAS (EQUITY) -->
                        <div class="mb-6">
                            <h3 class="text-md font-bold uppercase text-gray-800 border-b-2 border-gray-800 pb-1 mb-4">
                                3. EKUITAS (EQUITY)
                            </h3>
                            <table class="w-full text-sm">
                                <tbody>
                                    @forelse($equities as $eq)
                                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                                            <td class="py-2 font-mono text-gray-500 w-24">{{ $eq->code }}</td>
                                            <td class="py-2 text-gray-800">{{ $eq->name }}</td>
                                            <td class="py-2 text-right font-medium w-36">Rp {{ number_format($eq->amount, 2, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="py-2 text-gray-400 italic">Tidak ada saldo ekuitas.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="font-semibold text-gray-700 bg-gray-50">
                                        <td colspan="2" class="py-2 pl-2">SUBTOTAL EKUITAS</td>
                                        <td class="py-2 text-right">Rp {{ number_format($totalEquities, 2, ',', '.') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Total Kewajiban & Ekuitas -->
                    <div class="border-t-2 border-gray-900 pt-3 bg-indigo-50 p-3 rounded">
                        <div class="flex justify-between items-center font-bold text-indigo-900">
                            <span class="uppercase">TOTAL KEWAJIBAN & EKUITAS</span>
                            <span class="text-base font-mono">Rp {{ number_format($totalLiabilitiesAndEquity, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
