<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'invoice_number',
        'amount',
        'type',
        'billing_period_start',
        'billing_period_end',
        'status',
        'payment_method',
        'payment_reference',
        'paid_at',
        'due_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'billing_period_start' => 'date',
        'billing_period_end' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markAsPaid(?string $method = null, ?string $reference = null): void
    {
        $this->update([
            'status' => 'paid',
            'payment_method' => $method,
            'payment_reference' => $reference,
            'paid_at' => now(),
        ]);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public static function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $lastInvoice = static::whereYear('created_at', $year)->latest('id')->first();
        $number = $lastInvoice ? ((int) substr($lastInvoice->invoice_number, -4)) + 1 : 1;

        return sprintf('INV-%s-%04d', $year, $number);
    }
}
