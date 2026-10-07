<?php

namespace App\Exports;

use App\Models\Journal;
use Illuminate\Database\Eloquent\Builder; // 1. Import Builder
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class JournalExport implements FromQuery, WithHeadings, WithMapping
{
    protected $search;
    protected $startDate;
    protected $endDate;

    public function __construct($search = null, $startDate = null, $endDate = null)
    {
        $this->search    = $search;
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
    }

    /**
     * Tambahkan ": Builder" sebagai return type agar kompatibel dengan FromQuery
     */
    public function query(): Builder
    {
        return Journal::query()
            ->with('items.account')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('description', 'like', "%{$this->search}%")
                      ->orWhere('journal_number', 'like', "%{$this->search}%");
                });
            })
            ->when($this->startDate, fn($q) => $q->whereDate('transaction_date', '>=', $this->startDate))
            ->when($this->endDate, fn($q) => $q->whereDate('transaction_date', '<=', $this->endDate))
            ->latest('transaction_date');
    }

    public function headings(): array
    {
        return [
            'No. Voucher',
            'Tanggal',
            'Deskripsi',
            'Kode Akun',
            'Nama Akun',
            'Debit (Rp)',
            'Kredit (Rp)',
        ];
    }

    public function map($journal): array
    {
        $rows = [];
        foreach ($journal->items as $item) {
            $rows[] = [
                $journal->journal_number,
                $journal->transaction_date,
                $journal->description,
                $item->account?->account_code ?? '-',
                $item->account?->account_name ?? '-',
                $item->debit,
                $item->credit,
            ];
        }
        return $rows;
    }
}
