<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Setting;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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

        $merchantId = Setting::get('esewa_merchant_id');
        $productId  = $request->product_id;
        $refId      = $request->ref_id;
        $amount     = $request->amount;
        $status     = $request->status;

        if (! $merchantId) {
            Log::warning('eSewa merchant ID not configured');
            return response()->json(['error' => 'Payment gateway not configured'], 500);
        }

        // Only process completed payments — ignore cancellations, refunds, etc.
        if ($status !== 'COMPLETE') {
            Log::info('eSewa payment not completed', ['status' => $status, 'product_id' => $productId]);
            return response()->json(['status' => 'ignored'], 200);
        }

        $invoice = Invoice::where('invoice_number', $productId)->first();

        if (! $invoice) {
            return response()->json(['error' => 'Invoice not found'], 404);
        }

        // Verify payment amount matches invoice
        if ((float) $amount !== (float) $invoice->amount) {
            Log::warning('eSewa amount mismatch', [
                'expected' => $invoice->amount,
                'received' => $amount,
                'product'  => $productId,
            ]);
            return response()->json(['status' => 'failed'], 400);
        }

        $verified = $this->verifyEsewa($merchantId, $productId, $amount, $refId);

        if (! $verified) {
            Log::warning('eSewa payment verification failed', compact('productId', 'refId', 'amount'));
            return response()->json(['status' => 'failed'], 400);
        }

        if ($invoice->status !== 'paid') {
            $this->invoiceService->processPayment($invoice, 'esewa', $refId);
        }

        return response()->json(['status' => 'success']);
    }

    public function khalti(Request $request): JsonResponse
    {
        Log::info('Khalti webhook received', $request->all());

        $token           = $request->token;
        $amount          = $request->amount;
        $productIdentity = $request->product_identity;

        $secretKey  = Setting::get('khalti_secret_key');
        $verifyUrl  = Setting::get('khalti_verify_url', 'https://khalti.com/api/v2/payment/verify/');

        if (! $secretKey) {
            Log::warning('Khalti secret key not configured');
            return response()->json(['error' => 'Payment gateway not configured'], 500);
        }

        $invoice = Invoice::where('invoice_number', $productIdentity)->first();

        if (! $invoice) {
            return response()->json(['error' => 'Invoice not found'], 404);
        }

        // Khalti sends amount in paisa (1 Rs = 100 paisa); invoice stores NPR
        $expectedPaisa = (int) ($invoice->amount * 100);
        if ((int) $amount !== $expectedPaisa) {
            Log::warning('Khalti amount mismatch', [
                'expected_paisa' => $expectedPaisa,
                'received'       => $amount,
                'product'        => $productIdentity,
            ]);
            return response()->json(['status' => 'failed'], 400);
        }

        $response = Http::withHeaders(['Authorization' => 'Key '.$secretKey])
            ->post($verifyUrl, ['token' => $token, 'amount' => $amount]);

        if (! $response->successful()) {
            Log::warning('Khalti verification request failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return response()->json(['status' => 'failed'], 400);
        }

        $data = $response->json();

        if (! isset($data['idx'])) {
            return response()->json(['status' => 'failed'], 400);
        }

        if ($invoice->status !== 'paid') {
            $this->invoiceService->processPayment($invoice, 'khalti', $token);
        }

        return response()->json(['status' => 'success']);
    }

    private function verifyEsewa(string $merchantId, string $productId, string $amount, string $refId): bool
    {
        $env        = Setting::get('esewa_env', 'test');
        $verifyUrl  = $env === 'live'
            ? 'https://esewa.com.np/epay/transrec'
            : 'https://uat.esewa.com.np/epay/transrec';

        try {
            $response = Http::get($verifyUrl, [
                'amt' => $amount,
                'scd' => $merchantId,
                'rid' => $refId,
                'pid' => $productId,
            ]);

            return $response->successful()
                && str_contains($response->body(), '<response_code>Success</response_code>');
        } catch (\Exception $e) {
            Log::error('eSewa verification exception', ['message' => $e->getMessage()]);
            return false;
        }
    }
}
