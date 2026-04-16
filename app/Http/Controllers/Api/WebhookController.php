<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    private InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function esewa(Request $request): JsonResponse
    {
        Log::info('eSewa webhook received', $request->all());

        $secret = config('services.esewa.secret_key');
        $merchantId = config('services.esewa.merchant_id');

        $productId = $request->product_id;
        $amount = $request->amount;
        $totalAmount = $request->total_amount;
        $refId = $request->ref_id;
        $status = $request->status;

        $invoice = Invoice::where('invoice_number', $productId)->first();

        if (! $invoice) {
            return response()->json(['error' => 'Invoice not found'], 404);
        }

        if ($status === 'success' && $invoice->status !== 'paid') {
            $this->invoiceService->processPayment($invoice, 'esewa', $refId);

            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'failed']);
    }

    public function khalti(Request $request): JsonResponse
    {
        Log::info('Khalti webhook received', $request->all());

        $token = $request->token;
        $amount = $request->amount;
        $productIdentity = $request->product_identity;
        $productName = $request->product_name;

        $invoice = Invoice::where('invoice_number', $productIdentity)->first();

        if (! $invoice) {
            return response()->json(['error' => 'Invoice not found'], 404);
        }

        $secretKey = config('services.khalti.secret_key');
        $args = http_build_query([
            'token' => $token,
            'amount' => $amount,
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://khalti.com/api/v2/payment/verify/');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $args);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Key '.$secretKey,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $responseData = json_decode($response, true);

        if (isset($responseData['success']) && $responseData['success'] === true) {
            if ($invoice->status !== 'paid') {
                $this->invoiceService->processPayment($invoice, 'khalti', $token);
            }

            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'failed']);
    }
}
