<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Product;
use App\Models\Project;
use App\Models\Quote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->query('category');

        $expenses = Expense::with('project')
            ->when($category, fn ($q) => $q->where('category', $category))
            ->latest('spent_at')->latest('id')
            ->paginate(20)
            ->withQueryString();

        $monthTotal = (float) Expense::whereYear('spent_at', now()->year)
            ->whereMonth('spent_at', now()->month)
            ->sum('amount');

        $yearTotal = (float) Expense::whereYear('spent_at', now()->year)->sum('amount');

        return view('expenses.index', compact('expenses', 'category', 'monthTotal', 'yearTotal'));
    }

    public function create(): View
    {
        return view('expenses.create', array_merge(
            ['expense' => new Expense(['category' => 'fournitures', 'spent_at' => now()->toDateString()])],
            $this->formData(),
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['company_id'] = $request->user()->company_id;
        $data['user_id'] = $request->user()->id;
        $data = array_merge($data, $this->handleReceipt($request));

        Expense::create($data);

        return redirect()->route('expenses.index')->with('status', 'Dépense enregistrée.');
    }

    public function edit(Expense $expense): View
    {
        return view('expenses.edit', array_merge(['expense' => $expense], $this->formData()));
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'projects' => Project::orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(),
            'quotes'   => Quote::latest('issue_date')->limit(100)->get(),
        ];
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('receipt')) {
            if ($expense->receipt_path) {
                Storage::delete($expense->receipt_path);
            }
            $data = array_merge($data, $this->handleReceipt($request));
        }

        $expense->update($data);

        return redirect()->route('expenses.index')->with('status', 'Dépense mise à jour.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        if ($expense->receipt_path) {
            Storage::delete($expense->receipt_path);
        }
        $expense->delete();

        return redirect()->route('expenses.index')->with('status', 'Dépense supprimée.');
    }

    /** Justificatif affiché dans le visualiseur (inline). */
    public function receipt(Expense $expense): StreamedResponse
    {
        abort_unless($expense->receipt_path && Storage::exists($expense->receipt_path), 404);

        return Storage::response($expense->receipt_path, $expense->receipt_name);
    }

    /** Justificatif en téléchargement forcé. */
    public function receiptDownload(Expense $expense): StreamedResponse
    {
        abort_unless($expense->receipt_path && Storage::exists($expense->receipt_path), 404);

        return Storage::download($expense->receipt_path, $expense->receipt_name);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $companyId = $request->user()->company_id;

        $validated = $request->validate([
            'category'   => ['required', Rule::in(array_keys(Expense::CATEGORIES))],
            'label'      => ['required', 'string', 'max:255'],
            'amount'     => ['required', 'numeric', 'min:0'],
            'vat_rate'   => ['nullable', 'numeric', 'min:0', 'max:100'],
            'quantity'   => ['nullable', 'numeric', 'min:0'],
            'spent_at'   => ['required', 'date'],
            'supplier'   => ['nullable', 'string', 'max:255'],
            'project_id' => ['nullable', Rule::exists('projects', 'id')->where('company_id', $companyId)],
            'product_id' => ['nullable', Rule::exists('products', 'id')->where('company_id', $companyId)],
            'quote_id'   => ['nullable', Rule::exists('quotes', 'id')->where('company_id', $companyId)],
            'notes'      => ['nullable', 'string'],
            'receipt'    => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,gif,webp,pdf'],
        ]);

        unset($validated['receipt']); // géré séparément (stockage fichier)

        return $validated;
    }

    /**
     * @return array<string, mixed>
     */
    private function handleReceipt(Request $request): array
    {
        if (! $request->hasFile('receipt')) {
            return [];
        }

        $file = $request->file('receipt');

        return [
            'receipt_path' => $file->store('expenses/' . $request->user()->company_id),
            'receipt_name' => $file->getClientOriginalName(),
            'receipt_mime' => $file->getClientMimeType(),
            'receipt_size' => $file->getSize(),
        ];
    }
}
