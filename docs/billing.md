# Billing System Documentation

## Overview

ChatBot Nepal now uses a flexible per-client billing system instead of hardcoded plan prices. Each client has editable billing settings that control when and how much they get invoiced.

## How Billing Works

### Automatic Invoice Generation
- **Daily Check**: A scheduler runs every day at 00:10 and checks all clients
- **Eligibility**: Only clients with `subscription_status = 'active'` and a valid `monthly_amount` get new invoices
- **Trigger**: When `next_billing_date <= today()`, a new invoice is created
- **Amount**: Uses the client's `monthly_amount` field (can be monthly, quarterly, or yearly amounts)
- **Due Date**: 7 days from invoice creation date
- **Type**: Set to the client's `billing_cycle` ('monthly', 'quarterly', or 'yearly')

### Next Billing Date Management
- After creating an invoice, `next_billing_date` is automatically advanced:
  - Monthly: `+1 month`
  - Quarterly: `+3 months`
  - Yearly: `+1 year`

## Client Management

### Giving a Client a Custom Price
1. Go to Admin → Clients → Edit Client
2. In the "Billing & Subscription" section:
   - Set `Plan Display Name`: "Custom Discounted" or "Enterprise Plan"
   - Set `Billing Amount`: The negotiated price (e.g., 1200.00 for a discounted basic plan)
   - Set `Billing Cycle`: Choose monthly/quarterly/yearly
   - Set `Next Billing Date`: When their first invoice should generate

### Pausing Billing Temporarily
1. Edit the client
2. Set `Subscription Status` to "Paused"
3. The client won't get new invoices until you set it back to "Active"

### Handling Yearly Customers
- Set `Billing Cycle` to "Yearly"
- Set `Billing Amount` to the full annual amount (e.g., 18000.00 for a yearly plan)
- Note: The field is named `monthly_amount` but can contain any cycle amount

## Database Schema

### New Fields in `users` table:
- `plan_name` VARCHAR(100) — Display label
- `monthly_amount` DECIMAL(10,2) — Billing amount (can be monthly/quarterly/yearly)
- `billing_cycle` ENUM('monthly','quarterly','yearly') — How often to bill
- `next_billing_date` DATE — When next invoice generates
- `subscription_started_at` TIMESTAMP — When subscription began
- `subscription_status` ENUM('active','paused','cancelled') — Billing state

## Migration Notes

The migration includes a data migration that:
- Sets `plan_name` based on existing `plan` field (ucfirst format)
- Sets `monthly_amount` using old hardcoded prices: basic=1500, standard=3000, growth=5000, pro=8000
- Sets `billing_cycle = 'monthly'` for all existing clients
- Sets `next_billing_date = first day of next month`
- Sets `subscription_started_at = users.created_at`
- Sets `subscription_status = 'active'` (or 'paused' if chatbot was disabled)

## Scheduler Changes

**Before**: Monthly on 1st, hardcoded $planPrices array, looped all clients

**After**: Daily at 00:10, per-client pricing, only bills eligible active clients

## Future Improvements

Consider renaming `monthly_amount` to `cycle_amount` in a future refactor to be more accurate for yearly billing.