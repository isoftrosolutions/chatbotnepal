<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\User;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    private InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function index(Request $request): View
    {
        $query = Invoice::with('user')->orderBy('created_at', 'desc');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $invoices = $query->paginate(20);
        $stats = [
            'pending' => Invoice::where('status', 'pending')->count(),
            'paid' => Invoice::where('status', 'paid')->count(),
            'overdue' => Invoice::where('status', 'overdue')->count(),
        ];

        return view('admin.invoices', compact('invoices', 'stats'));
    }

    public function create(Request $request): RedirectResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:setup,monthly,yearly',
            'amount' => 'required|numeric|min:0',
        ]);

        $client = User::findOrFail($request->user_id);

        $this->invoiceService->createInvoice(
            $client,
            $request->type,
            $request->amount
        );

        return redirect()->route('admin.invoices.index')
            ->with('success', 'Invoice created successfully');
    }

    public function markPaid(int $id): RedirectResponse
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->markAsPaid('manual');

        return redirect()->back()
            ->with('success', 'Invoice marked as paid');
    }

    public function destroy(int $id): RedirectResponse
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->update(['status' => 'cancelled']);

        return redirect()->back()
            ->with('success', 'Invoice cancelled');
    }
}
