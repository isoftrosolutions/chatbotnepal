<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $invoices = $user->invoices()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $pendingTotal = $user->invoices()
            ->where('status', 'pending')
            ->sum('amount');

        return view('client.invoices', compact('invoices', 'pendingTotal'));
    }

    public function pay(Invoice $invoice)
    {
        $user = auth()->user();

        if ($invoice->user_id !== $user->id) {
            abort(403);
        }

        if ($invoice->isPaid()) {
            return redirect()->back()->with('error', 'Invoice already paid');
        }

        $paymentMethod = request('payment_method', 'esewa');

        if ($paymentMethod === 'esewa') {
            return $this->payWithESewa($invoice);
        } elseif ($paymentMethod === 'khalti') {
            return $this->payWithKhalti($invoice);
        }

        return redirect()->back()->with('error', 'Invalid payment method');
    }

    private function payWithESewa(Invoice $invoice): RedirectResponse
    {
        $merchantId = config('services.esewa.merchant_id', env('ESEWA_MERCHANT_ID'));
        $amount = $invoice->amount;
        $taxAmount = 0;
        $totalAmount = $amount + $taxAmount;
        $productId = $invoice->invoice_number;

        $params = [
            'amt' => $amount,
            'txamt' => $taxAmount,
            'tamt' => $totalAmount,
            'pid' => $productId,
            'scd' => $merchantId,
            'su' => url('/client/invoices/'.$invoice->id.'/callback?status=success'),
            'fu' => url('/client/invoices/'.$invoice->id.'/callback?status=fail'),
        ];

        $encodedParams = base64_encode(json_encode($params));
        $signature = hash_hmac('sha256', $encodedParams, config('services.esewa.secret_key'), true);
        $signature = base64_encode($signature);

        $paymentUrl = config('services.esewa.env', 'test') === 'production'
            ? 'https://epay.esewa.com.np/api/epay/main/v2/form'
            : 'https://rc-epay.esewa.com.np/api/epay/main/v2/form';

        return redirect($paymentUrl.'?'.http_build_query([
            'amount' => $amount,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'product_identity' => $productId,
            'product_service_charge' => 0,
            'product_code' => $merchantId,
            'success_url' => $params['su'],
            'failure_url' => $params['fu'],
        ]));
    }

    private function payWithKhalti(Invoice $invoice): RedirectResponse
    {
        $secretKey = config('services.khalti.secret_key', env('KHALTI_SECRET_KEY'));
        $amount = (int) ($invoice->amount * 100);

        $payload = [
            'return_url' => url('/client/invoices/'.$invoice->id.'/callback'),
            'website_url' => url('/'),
            'amount' => $amount,
            'purchase_order_id' => $invoice->invoice_number,
            'customer_info' => [
                'name' => auth()->user()->name,
                'email' => auth()->user()->email,
            ],
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://a.khalti.com/api/v2/epayment/initiate/');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Key '.$secretKey,
            'Content-Type: application/json',
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);

        if (isset($result['payment_url'])) {
            return redirect($result['payment_url']);
        }

        return redirect()->back()->with('error', 'Failed to initiate Khalti payment');
    }

    public function callback(Invoice $invoice, Request $request)
    {
        if ($request->status === 'success') {
            $invoice->markAsPaid('esewa', $request->ref_id ?? 'unknown');

            return redirect()->route('client.invoices.index')
                ->with('success', 'Payment successful! Your chatbot is now active.');
        }

        return redirect()->route('client.invoices.index')
            ->with('error', 'Payment failed. Please try again.');
    }
}
