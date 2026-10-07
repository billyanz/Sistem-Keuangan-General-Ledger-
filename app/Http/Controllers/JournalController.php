<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\Journal;
use App\Services\JournalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Exports\JournalExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class JournalController extends Controller
{
    public function __construct(private readonly JournalService $journalService) {}

    public function index(Request $request)
    {
        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
            'status'     => ['nullable', 'in:draft,posted'],
            'search'     => ['nullable', 'string', 'max:100'],
        ]);

        $journals = Journal::with(['branch', 'items.account', 'creator'])
            ->when($request->filled('start_date'), fn ($query) => $query->whereDate('transaction_date', '>=', $request->start_date))
            ->when($request->filled('end_date'), fn ($query) => $query->whereDate('transaction_date', '<=', $request->end_date))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();
                $query->where(fn ($journals) => $journals
                    ->where('journal_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%"));
            })
            ->latest('transaction_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('journals.index', compact('journals'));
    }

    public function create()
    {
        return view('journals.create', [
            'accounts' => ChartOfAccount::where('is_active', true)->orderBy('code')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id'           => ['required', 'exists:branches,id'],
            'transaction_date'    => ['required', 'date'],
            'description'          => ['required', 'string', 'max:255'],
            'items'               => ['required', 'array', 'min:2'],
            'items.*.account_id'  => ['required', 'exists:chart_of_accounts,id'],
            'items.*.debit'       => ['required', 'numeric', 'min:0'],
            'items.*.credit'      => ['required', 'numeric', 'min:0'],
            'items.*.description' => ['nullable', 'string', 'max:255'],
        ]);

        $journal = DB::transaction(fn () => $this->journalService->createJournal($validated, Auth::id()));

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Jurnal berhasil dicatat.',
                'data'    => $journal,
            ], 201);
        }

        return redirect()->route('journals.show', $journal)
            ->with('success', 'Jurnal berhasil dicatat.');
    }

    public function show(Journal $journal)
    {
        $journal->load(['branch', 'items.account', 'creator']);

        return view('journals.show', compact('journal'));
    }

    /**
     * Export data jurnal ke Excel.
     */
    public function exportExcel(Request $request)
    {
        $search    = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');

        return Excel::download(
            new JournalExport($search, $startDate, $endDate),
            'jurnal-umum-' . date('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Export data jurnal ke PDF.
     */
    public function exportPdf(Request $request)
    {
        $search    = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');

        $journals = Journal::with('items.account')
            ->when($search, function ($query) use ($search) {
                $query->where('description', 'like', "%{$search}%")
                    ->orWhere('journal_number', 'like', "%{$search}%");
            })
            ->when($startDate, fn($q) => $q->whereDate('transaction_date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('transaction_date', '<=', $endDate))
            ->latest('transaction_date')
            ->get();

        $pdf = Pdf::loadView('journals.pdf', compact('journals'));
        return $pdf->download('jurnal-umum-' . date('Y-m-d') . '.pdf');
    }
}
