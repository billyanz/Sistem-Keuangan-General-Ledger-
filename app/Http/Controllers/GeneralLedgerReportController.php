<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\ChartOfAccount;
use App\Models\JournalItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class GeneralLedgerReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'branch_id'  => ['nullable', 'exists:branches,id'],
            'account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $branchId  = $filters['branch_id'] ?? null;
        $accountId = $filters['account_id'] ?? null;
        $startDate = $filters['start_date'] ?? now()->startOfMonth()->toDateString();
        $endDate   = $filters['end_date'] ?? now()->endOfMonth()->toDateString();

        $branches = Branch::where('is_active', true)->get();
        $accounts = ChartOfAccount::where('is_active', true)
            ->orderBy('account_code')
            ->get();

        $selectedAccount = null;
        $openingBalance  = 0;
        $journalItems    = collect();

        if ($accountId) {
            $selectedAccount = ChartOfAccount::findOrFail($accountId);

            // 1. Hitung Saldo Awal (Transaksi sebelum start_date)
            $previousQuery = JournalItem::whereHas('journal', function ($q) use ($branchId, $startDate) {
                $q->where('transaction_date', '<', $startDate);

                // Tambahkan filter status jika kolom status tersedia
                if (\Schema::hasColumn('journals', 'status')) {
                    $q->where('status', 'posted');
                }

                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }
            })->where('account_id', $accountId);

            $prevDebit  = (float) $previousQuery->sum('debit');
            $prevCredit = (float) $previousQuery->sum('credit');

            // Hitung Saldo Awal berdasarkan Normal Balance
            if (strtolower($selectedAccount->normal_balance) === 'debit') {
                $openingBalance = $prevDebit - $prevCredit;
            } else {
                $openingBalance = $prevCredit - $prevDebit;
            }

            // 2. Ambil Transaksi Mutasi Buku Besar pada Periode Terpilih
            $journalItems = JournalItem::with(['journal.branch', 'journal'])
                ->whereHas('journal', function ($q) use ($branchId, $startDate, $endDate) {
                    $q->whereBetween('transaction_date', [$startDate, $endDate]);

                    if (\Schema::hasColumn('journals', 'status')) {
                        $q->where('status', 'posted');
                    }

                    if ($branchId) {
                        $q->where('branch_id', $branchId);
                    }
                })
                ->where('account_id', $accountId)
                ->get()
                ->sortBy(function ($item) {
                    return $item->journal->transaction_date . '-' . $item->journal->id;
                });
        }

        return view('reports.general-ledger', compact(
            'branches',
            'accounts',
            'branchId',
            'accountId',
            'startDate',
            'endDate',
            'selectedAccount',
            'openingBalance',
            'journalItems'
        ));
    }
}
