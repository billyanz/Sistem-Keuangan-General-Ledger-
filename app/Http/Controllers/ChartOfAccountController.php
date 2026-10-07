<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChartOfAccountController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'type'   => ['nullable', Rule::in(['asset', 'liability', 'equity', 'revenue', 'expense'])],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $accounts = ChartOfAccount::query()
            ->with('parent')
            ->withCount('journalItems')
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->type))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();
                $query->where(fn ($q) => $q
                    ->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%"));
            })
            ->orderBy('code')
            ->paginate(15)
            ->withQueryString();

        return view('accounts.index', compact('accounts'));
    }

    public function create()
    {
        return view('accounts.create', [
            'account' => new ChartOfAccount,
            'parents' => ChartOfAccount::orderBy('code')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $account = ChartOfAccount::create($this->validatedData($request));

        return redirect()->route('accounts.show', $account)
            ->with('success', 'Akun berhasil ditambahkan.');
    }

    public function show(ChartOfAccount $account)
    {
        $account->load(['parent', 'children'])
            ->loadCount('journalItems');

        return view('accounts.show', compact('account'));
    }

    public function edit(ChartOfAccount $account)
    {
        return view('accounts.edit', [
            'account' => $account,
            'parents' => ChartOfAccount::where('id', '!=', $account->id)->orderBy('code')->get(),
        ]);
    }

    public function update(Request $request, ChartOfAccount $account)
    {
        $account->update($this->validatedData($request, $account));

        return redirect()->route('accounts.show', $account)
            ->with('success', 'Akun berhasil diperbarui.');
    }

    public function destroy(ChartOfAccount $account)
    {
        if ($account->journalItems()->exists() || $account->children()->exists()) {
            return back()->with('error', 'Akun yang sudah digunakan dalam transaksi atau memiliki sub-akun tidak dapat dihapus.');
        }

        $account->delete();

        return redirect()->route('accounts.index')->with('success', 'Akun berhasil dihapus.');
    }

    private function validatedData(Request $request, ?ChartOfAccount $account = null): array
    {
        $data = $request->validate([
            'code'           => ['required', 'string', 'max:20', Rule::unique('chart_of_accounts', 'code')->ignore($account?->id)],
            'name'           => ['required', 'string', 'max:255'],
            'type'           => ['required', Rule::in(['asset', 'liability', 'equity', 'revenue', 'expense'])],
            'normal_balance' => ['required', Rule::in(['debit', 'credit', 'Debit', 'Credit'])],
            'parent_id'      => [
                'nullable',
                'integer',
                Rule::exists('chart_of_accounts', 'id'),
                ...($account ? [Rule::notIn([$account->id])] : []),
            ],
            'is_active'      => ['sometimes', 'boolean'],
        ]);

        $data['type'] = strtolower($data['type']);
        $data['normal_balance'] = strtolower($data['normal_balance']);

        return $data;
    }
}
