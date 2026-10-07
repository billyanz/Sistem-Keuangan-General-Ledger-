<?php

namespace App\Exports;

use App\Models\ChartOfAccount;
use App\Models\JournalItem;
use Illuminate\Support\Collection; // 1. Import Collection
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class IncomeStatementExport implements FromCollection, WithHeadings
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate = null, $endDate = null)
    {
        $this->startDate = $startDate ?? date('Y-m-01');
        $this->endDate   = $endDate ?? date('Y-m-t');
    }

    /**
     * Tambahkan ": Collection" sebagai return type agar cocok dengan interface FromCollection
     */
    public function collection(): Collection
    {
        $data = collect();

        // 1. Ambil Pendapatan
        $revenues = ChartOfAccount::where('account_type', 'Revenue')->get();
        $totalRevenue = 0;

        $data->push(['-- PENDAPATAN --', '', '']);
        foreach ($revenues as $rev) {
            $credit = JournalItem::where('account_id', $rev->id)
                ->whereHas('journal', fn($q) => $q->whereBetween('transaction_date', [$this->startDate, $this->endDate]))
                ->sum('credit');
            $debit = JournalItem::where('account_id', $rev->id)
                ->whereHas('journal', fn($q) => $q->whereBetween('transaction_date', [$this->startDate, $this->endDate]))
                ->sum('debit');

            $amount = $credit - $debit;
            $totalRevenue += $amount;

            $data->push([$rev->account_code, $rev->account_name, $amount]);
        }
        $data->push(['TOTAL PENDAPATAN', '', $totalRevenue]);
        $data->push(['', '', '']); // Baris Kosong

        // 2. Ambil Beban
        $expenses = ChartOfAccount::where('account_type', 'Expense')->get();
        $totalExpense = 0;

        $data->push(['-- BEBAN OPERASIONAL --', '', '']);
        foreach ($expenses as $exp) {
            $debit = JournalItem::where('account_id', $exp->id)
                ->whereHas('journal', fn($q) => $q->whereBetween('transaction_date', [$this->startDate, $this->endDate]))
                ->sum('debit');
            $credit = JournalItem::where('account_id', $exp->id)
                ->whereHas('journal', fn($q) => $q->whereBetween('transaction_date', [$this->startDate, $this->endDate]))
                ->sum('credit');

            $amount = $debit - $credit;
            $totalExpense += $amount;

            $data->push([$exp->account_code, $exp->account_name, $amount]);
        }
        $data->push(['TOTAL BEBAN', '', $totalExpense]);
        $data->push(['', '', '']);

        // 3. Laba Rugi Bersih
        $netProfit = $totalRevenue - $totalExpense;
        $data->push(['LABA / RUGI BERSIH', '', $netProfit]);

        return $data;
    }

    public function headings(): array
    {
        return ['Kode Akun', 'Nama Akun / Kategori', 'Nominal (Rp)'];
    }
}
