# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**ChatBot Nepal** — a B2B SaaS platform that lets businesses embed an AI chatbot on their website. Clients manage knowledge bases, view conversations and visitor analytics, and pay monthly invoices. The AI backend uses the Groq API (llama-3.3-70b-versatile by default).

Stack: Laravel 13, PHP 8.2+, Blade templates, MySQL, Apache 24.

## Commands

```bash
# First-time setup
composer run setup

# Development (starts PHP server + queue + pail + Vite concurrently)
composer run dev

# Run all tests
composer run test

# Run a single test file
php artisan test tests/Feature/ExampleTest.php

# Run a single test by name
php artisan test --filter=test_name

# Code style (Laravel Pint)
./vendor/bin/pint

# Configure Groq API key, SMTP, and other secrets into the DB settings table
php artisan app:setup

# Seed admin user and demo data
php artisan db:seed --class=AdminUserSeeder
php artisan db:seed --class=DemoClientSeeder

# Sync knowledge base files from disk to DB
php artisan kb:sync
```

Tests use an in-memory SQLite database (configured in `phpunit.xml`).

## Architecture

### User Roles & Routing

The `User` model has `role = admin|client`. Two middleware classes enforce this — `AdminMiddleware` and `ClientMiddleware`. Routes are split into:
- `/admin/*` — admin-only (manage clients, knowledge bases, invoices, global settings)
- `/client/*` — client-only (own dashboard, conversations, embed code, invoices)
- `/api/*` — public, used by the embeddable widget

### Settings System

`App\Models\Setting` is a DB-backed key/value store with permanent caching (`Cache::rememberForever`). It is the authoritative source for runtime config — values stored here override `.env` defaults for Groq model/API key, SMTP credentials, plan prices, and payment gateway keys. Always use `Setting::get('key', $default)` / `Setting::set('key', $value)` — never read these directly from `env()` in business logic.

### AI Chat Flow

```
widget.js → POST /api/widget/session   (get X-Session-Token)
          → POST /api/chat             (or /api/chat/stream for SSE)
                ↓
         ChatController / StreamChatController
                ↓
         ChatService::processChat()
           - builds token-budgeted messages (SYSTEM_TOKEN_BUDGET=1500, TOTAL=4000, MAX_HISTORY=6)
           - injects knowledge base into system prompt
           - calls GrokService::chat() or GrokService::streamChat()
           - logs token usage to TokenUsageLog and GroqUsageLog
```

`ChatService::buildTokenBudgetedMessages()` must be called *before* saving the visitor's message to avoid including it twice in history.

### Embeddable Widget

`public/widget.js` is a self-contained IIFE. It reads `data-token` or `data-site-id` from its own `<script>` tag, fetches the widget config from `/api/widget-config/{token}`, creates a session, and renders a WhatsApp-style chat UI. `conversationId` persists per browser tab via `sessionStorage`; `visitorId` persists across sessions via `localStorage`.

### Knowledge Base

Per-client knowledge base entries (`KnowledgeBase` model) are typed: `faq`, `services`, `contact`, `about`, `custom`. They are loaded in priority order and truncated by paragraph (never mid-sentence) to fit the system token budget before being appended to the system prompt.

### Domain Validation

`ValidateWidgetDomain` middleware checks the `Origin`/`Referer` header against the client's registered `website_url`. A valid `X-Session-Token` header bypasses the domain check. Server-side requests without an `Origin` header are allowed through.

### Payments

Nepal-specific gateways: **eSewa** and **Khalti**. Webhook endpoints at `/api/webhooks/esewa` and `/api/webhooks/khalti` verify payments, then call `InvoiceService::processPayment()` which marks the invoice paid and re-enables `chatbot_enabled` on the user in a single DB transaction. Gateway credentials (`esewa_merchant_id`, `khalti_secret_key`) are stored in the Settings table.

### Key Models

| Model | Purpose |
|-------|---------|
| `User` | Clients and the single admin. `site_id` is the public widget identifier (auto-generated slug). `api_token` is a 64-char random string. |
| `KnowledgeBase` | Per-client text content fed into the AI system prompt. |
| `ChatConversation` | Groups messages per visitor session per client. |
| `ChatMessage` | Individual messages; `role` is `visitor` or `bot`. |
| `Visitor` | Deduplicated visitor profiles keyed by `visitor_uuid` + `user_id`. |
| `WidgetConfig` | Per-client widget appearance (colors, bot name, logo, prechat form). |
| `TokenUsageLog` | Daily aggregated token/cost rollup per client. |
| `GroqUsageLog` | Per-conversation Groq token usage for admin reporting. |
| `Invoice` | Monthly or custom invoices. Status: `pending → paid | overdue`. |
| `Setting` | Global platform settings (Groq key/model, SMTP, plan prices, etc.). |

## graphify

This project has a graphify knowledge graph at graphify-out/.

Rules:
- Before answering architecture or codebase questions, read graphify-out/GRAPH_REPORT.md for god nodes and community structure
- If graphify-out/wiki/index.md exists, navigate it instead of reading raw files
- After modifying code files in this session, run `graphify update .` to keep the graph current (AST-only, no API cost)
