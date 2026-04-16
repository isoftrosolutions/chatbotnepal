# ChatBot Nepal — Full Project Specification for Claude Code
> **This document describes EVERYTHING needed to build the ChatBot Nepal platform.**
> **All coding will be done by Claude Code (terminal).**
> **Author:** Devbarat Prasad Patel — Nepal Cyber Firm / iSoftro
> **Date:** April 2026

---

## 1. What Is This Project?

ChatBot Nepal is a **B2B SaaS platform** that sells AI-powered chatbot widgets to Nepali businesses. Business owners embed a single script tag on their website, and their visitors get an intelligent chatbot that answers questions using the business's own knowledge base (FAQs, services, pricing, etc.).

**In simple terms:** You build a chatbot for a client → they paste one line of code on their site → their customers get 24/7 instant answers → you charge monthly.

---

## 2. Tech Stack

| Component | Technology |
|-----------|-----------|
| **Backend Framework** | Laravel 11 (PHP 8.3) |
| **Database** | MySQL / MariaDB 10.11 |
| **AI Engine** | Grok API (xAI) — via HTTP POST requests |
| **Frontend (Admin/Client Dashboard)** | Blade templates + Tailwind CSS + Alpine.js |
| **Chatbot Widget** | Vanilla JavaScript (single `widget.js` file) |
| **Authentication** | Laravel Breeze (simple auth scaffolding) |
| **Payment Gateways** | eSewa + Khalti (Nepal-specific) |
| **Hosting** | Existing Hostinger VPS (shared with iSoftro ERP) |
| **Web Server** | OpenLiteSpeed (already running on VPS) |
| **Domain** | `chatbotnepal.isoftroerp.com` (subdomain) |
| **SSL** | Let's Encrypt (wildcard `*.isoftroerp.com`) |

---

## 3. Server & Deployment Context

The VPS already has these running — **do NOT touch them:**
- `isoftroerp.com` → iSoftro ERP (Laravel + MySQL `isof_isoftro_db`)
- `easyshoppinga.r.s` → ARS E-Commerce (PHP + MySQL `ars_ecommerce`)

ChatBot Nepal will be deployed at:
```
/home/chatbotnepal.isoftroerp.com/public_html/
```

Database: `chatbotnepal_db` (separate from other projects)

---

## 4. Database Schema

### Table: `users`
Stores admin and client accounts.

| Column | Type | Notes |
|--------|------|-------|
| id | BIGINT UNSIGNED AUTO_INCREMENT | Primary key |
| name | VARCHAR(255) | Full name |
| email | VARCHAR(255) UNIQUE | Login email |
| phone | VARCHAR(20) | Contact phone |
| password | VARCHAR(255) | Hashed password |
| role | ENUM('admin', 'client') | Default: 'client' |
| company_name | VARCHAR(255) NULLABLE | Client's business name |
| website_url | VARCHAR(500) NULLABLE | Client's website |
| plan | ENUM('basic', 'standard', 'growth', 'pro') | Default: 'basic' |
| status | ENUM('active', 'inactive', 'suspended') | Default: 'active' |
| api_token | VARCHAR(64) UNIQUE | Unique token for embed widget |
| chatbot_enabled | BOOLEAN | Default: true, set false if unpaid |
| email_verified_at | TIMESTAMP NULLABLE | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

### Table: `knowledge_bases`
Each client can have multiple knowledge base files.

| Column | Type | Notes |
|--------|------|-------|
| id | BIGINT UNSIGNED AUTO_INCREMENT | Primary key |
| user_id | BIGINT UNSIGNED | FK → users.id |
| file_name | VARCHAR(255) | e.g., "about.md", "services.md" |
| file_type | ENUM('about', 'services', 'faq', 'contact', 'custom') | Category of content |
| content | LONGTEXT | Markdown content |
| is_active | BOOLEAN | Default: true |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

### Table: `chat_conversations`
Groups messages into conversations per visitor session.

| Column | Type | Notes |
|--------|------|-------|
| id | BIGINT UNSIGNED AUTO_INCREMENT | Primary key |
| user_id | BIGINT UNSIGNED | FK → users.id (the client who owns this chatbot) |
| visitor_id | VARCHAR(64) | Anonymous visitor identifier (UUID stored in cookie) |
| visitor_name | VARCHAR(255) NULLABLE | If visitor provides name |
| visitor_email | VARCHAR(255) NULLABLE | If visitor provides email |
| source_url | VARCHAR(500) NULLABLE | Page URL where chat started |
| status | ENUM('active', 'closed') | Default: 'active' |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

### Table: `chat_messages`
Individual messages in a conversation.

| Column | Type | Notes |
|--------|------|-------|
| id | BIGINT UNSIGNED AUTO_INCREMENT | Primary key |
| conversation_id | BIGINT UNSIGNED | FK → chat_conversations.id |
| role | ENUM('visitor', 'bot') | Who sent the message |
| message | TEXT | Message content |
| tokens_used | INT | Grok API tokens consumed for this response |
| created_at | TIMESTAMP | |

### Table: `invoices`
Billing records for each client.

| Column | Type | Notes |
|--------|------|-------|
| id | BIGINT UNSIGNED AUTO_INCREMENT | Primary key |
| user_id | BIGINT UNSIGNED | FK → users.id |
| invoice_number | VARCHAR(50) UNIQUE | e.g., "INV-2026-0001" |
| amount | DECIMAL(10,2) | Amount in NPR |
| type | ENUM('setup', 'monthly', 'yearly') | |
| billing_period_start | DATE | |
| billing_period_end | DATE | |
| status | ENUM('pending', 'paid', 'overdue', 'cancelled') | Default: 'pending' |
| payment_method | VARCHAR(50) NULLABLE | 'esewa', 'khalti', 'manual' |
| payment_reference | VARCHAR(255) NULLABLE | Transaction ID from payment gateway |
| paid_at | TIMESTAMP NULLABLE | |
| due_date | DATE | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

### Table: `token_usage_logs`
Track Grok API token consumption per client per day.

| Column | Type | Notes |
|--------|------|-------|
| id | BIGINT UNSIGNED AUTO_INCREMENT | Primary key |
| user_id | BIGINT UNSIGNED | FK → users.id |
| date | DATE | Usage date |
| tokens_input | INT | Input tokens consumed |
| tokens_output | INT | Output tokens consumed |
| total_tokens | INT | Total tokens |
| api_calls | INT | Number of API calls |
| estimated_cost_npr | DECIMAL(10,4) | Estimated cost in NPR |
| created_at | TIMESTAMP | |

### Table: `settings`
Global platform settings.

| Column | Type | Notes |
|--------|------|-------|
| id | BIGINT UNSIGNED AUTO_INCREMENT | Primary key |
| key | VARCHAR(255) UNIQUE | Setting key |
| value | TEXT | Setting value |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

**Default settings to seed:**
```
grok_api_key → (empty, admin sets it)
grok_model → grok-3-mini
grok_max_tokens → 500
grok_temperature → 0.7
grok_system_prompt → "You are a helpful assistant for {business_name}. Answer questions using ONLY the provided knowledge base. Be friendly, concise, and helpful. If you don't know the answer, say so politely and suggest contacting the business directly."
platform_name → ChatBot Nepal
admin_email → isoftrosolutions@gmail.com
esewa_merchant_id → (empty, set later)
khalti_secret_key → (empty, set later)
billing_reminder_days → 3
auto_disable_after_days → 7
```

---

## 5. API Endpoints

### Public (No Auth)

| Method | Endpoint | Purpose |
|--------|----------|---------|
| POST | `/api/chat` | Main chat endpoint — widget sends messages here |
| GET | `/api/widget-config/{token}` | Widget fetches client's chatbot config (name, colors, welcome message) |

### Auth Required (Client)

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/api/client/dashboard` | Dashboard stats (total chats, messages today, etc.) |
| GET | `/api/client/conversations` | List all conversations |
| GET | `/api/client/conversations/{id}` | View single conversation messages |
| GET | `/api/client/invoices` | List invoices |
| GET | `/api/client/usage` | Token usage stats |
| GET | `/api/client/embed-code` | Get their embed script |
| POST | `/api/client/kb-update-request` | Request knowledge base update |

### Auth Required (Admin)

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/api/admin/clients` | List all clients |
| POST | `/api/admin/clients` | Create new client |
| PUT | `/api/admin/clients/{id}` | Update client |
| DELETE | `/api/admin/clients/{id}` | Delete client |
| GET | `/api/admin/clients/{id}/kb` | Get client's knowledge base files |
| POST | `/api/admin/clients/{id}/kb` | Add/update knowledge base file |
| DELETE | `/api/admin/clients/{id}/kb/{kbId}` | Delete knowledge base file |
| POST | `/api/admin/clients/{id}/toggle` | Enable/disable chatbot |
| GET | `/api/admin/clients/{id}/usage` | Client's token usage |
| GET | `/api/admin/invoices` | All invoices |
| POST | `/api/admin/invoices` | Generate invoice |
| PUT | `/api/admin/invoices/{id}/mark-paid` | Mark invoice as paid |
| GET | `/api/admin/settings` | Get platform settings |
| PUT | `/api/admin/settings` | Update platform settings |
| GET | `/api/admin/dashboard` | Admin dashboard stats |

### Payment Webhooks

| Method | Endpoint | Purpose |
|--------|----------|---------|
| POST | `/api/webhooks/esewa` | eSewa payment confirmation |
| POST | `/api/webhooks/khalti` | Khalti payment confirmation |

---

## 6. The Chat API Logic (`/api/chat`)

This is the **core endpoint** — how the chatbot actually works.

### Request Body
```json
{
  "token": "CLIENT_UNIQUE_TOKEN",
  "message": "What are your prices?",
  "visitor_id": "uuid-from-cookie",
  "conversation_id": null,
  "source_url": "https://clientwebsite.com/pricing"
}
```

### Backend Logic (Step by Step)
1. **Validate token** → find the client (user) with this `api_token`
2. **Check if chatbot is enabled** → if `chatbot_enabled = false`, return error: "Chatbot is currently offline"
3. **Check if client status is active** → if suspended/inactive, return error
4. **Load knowledge base** → get all `knowledge_bases` where `user_id = client.id` AND `is_active = true`, concatenate all markdown content
5. **Get or create conversation** → if `conversation_id` is null, create new `chat_conversations` row; otherwise load existing
6. **Load conversation history** → get last 10 messages from this conversation for context
7. **Build Grok API request:**
   ```
   System prompt: (from settings, with {business_name} replaced)
   
   Knowledge Base:
   ---
   [all markdown content concatenated]
   ---
   
   Conversation history:
   [last 10 messages]
   
   User: [new message]
   ```
8. **Call Grok API** → `POST https://api.x.ai/v1/chat/completions`
   ```json
   {
     "model": "grok-3-mini",
     "messages": [...],
     "max_tokens": 500,
     "temperature": 0.7
   }
   ```
   Headers: `Authorization: Bearer {grok_api_key}`, `Content-Type: application/json`
9. **Save visitor message** → insert into `chat_messages` (role: visitor)
10. **Save bot response** → insert into `chat_messages` (role: bot, tokens_used from API response)
11. **Log token usage** → upsert into `token_usage_logs` for today
12. **Return response:**
    ```json
    {
      "reply": "Our pricing starts at...",
      "conversation_id": 42
    }
    ```

---

## 7. The Embed Widget (`widget.js`)

This is the JavaScript file that clients paste on their website. It must be **self-contained, lightweight, and work on any website**.

### What It Does
- Adds a floating chat button (bottom-right corner)
- Opens a chat window when clicked
- Sends messages to `/api/chat` endpoint
- Displays bot responses with typing indicator
- Stores `visitor_id` in localStorage
- Stores `conversation_id` in sessionStorage (new session = new conversation)

### Widget Features
- **Floating button:** Circle with chat icon, bottom-right, customizable color
- **Chat window:** Header with business name, message area, input field
- **Welcome message:** Displayed when chat opens for first time
- **Typing indicator:** Three bouncing dots while waiting for API response
- **Auto-scroll:** Scrolls to latest message
- **Mobile responsive:** Works on all screen sizes
- **Close button:** Minimizes back to floating button
- **Powered by:** Small "Powered by ChatBot Nepal" link at bottom

### Embed Code
```html
<script src="https://chatbotnepal.isoftroerp.com/widget.js" data-token="CLIENT_TOKEN"></script>
```

### Widget Config Fetch
On load, the widget calls `GET /api/widget-config/{token}` to get:
```json
{
  "business_name": "Jim Sathi Fitness",
  "welcome_message": "Namaste! How can I help you today?",
  "primary_color": "#4F46E5",
  "position": "bottom-right"
}
```

These widget config fields should be stored in the `users` table (add columns) or in a separate `widget_configs` table:

### Table: `widget_configs`

| Column | Type | Notes |
|--------|------|-------|
| id | BIGINT UNSIGNED AUTO_INCREMENT | Primary key |
| user_id | BIGINT UNSIGNED | FK → users.id |
| welcome_message | TEXT | Default: "Namaste! How can I help you today?" |
| primary_color | VARCHAR(7) | Default: "#4F46E5" |
| position | ENUM('bottom-right', 'bottom-left') | Default: 'bottom-right' |
| bot_name | VARCHAR(100) | Default: "Assistant" |
| bot_avatar_url | VARCHAR(500) NULLABLE | Custom avatar image URL |
| show_powered_by | BOOLEAN | Default: true |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

---

## 8. Admin Panel Pages (Web Routes, Blade Views)

### Layout
- Sidebar navigation (dark theme preferred)
- Top bar with admin name + logout
- Tailwind CSS + Alpine.js for interactivity

### Pages

#### 8.1 — Admin Dashboard (`/admin/dashboard`)
- Total clients (active / inactive / suspended)
- Total conversations today / this month
- Total tokens used today / this month
- Revenue this month (from paid invoices)
- Recent activity feed

#### 8.2 — Client Management (`/admin/clients`)
- Table: name, email, company, plan, status, chatbot on/off, actions
- Actions: Edit, View KB, Toggle chatbot, View usage, Delete
- Button: "Add New Client"

#### 8.3 — Add/Edit Client (`/admin/clients/create` and `/admin/clients/{id}/edit`)
- Form: name, email, phone, company_name, website_url, plan, status
- Auto-generate api_token on create
- Set password (hashed)

#### 8.4 — Knowledge Base Editor (`/admin/clients/{id}/knowledge-base`)
- List of markdown files for this client
- Add new file (choose type: about/services/faq/contact/custom)
- Edit file content (textarea with markdown preview)
- Delete file
- "Test Chatbot" button → opens a mini chat window to test with this client's KB

#### 8.5 — Conversations Viewer (`/admin/clients/{id}/conversations`)
- List of all conversations for this client
- Click to view full message history
- Show visitor info, source URL, timestamps

#### 8.6 — Invoices (`/admin/invoices`)
- Table: invoice #, client, amount, type, status, due date, paid date
- Filter by status (pending/paid/overdue)
- "Generate Invoice" button
- "Mark as Paid" action

#### 8.7 — Token Usage (`/admin/usage`)
- Chart showing daily token usage (all clients combined)
- Table: client-wise usage breakdown
- Estimated cost per client

#### 8.8 — Platform Settings (`/admin/settings`)
- Form to update all settings (Grok API key, model, prompts, payment keys, etc.)

---

## 9. Client Dashboard Pages (Web Routes, Blade Views)

### Pages

#### 9.1 — Client Dashboard (`/client/dashboard`)
- Chatbot status: Online / Offline
- Total conversations (all time / this month)
- Most asked questions (top 5)
- Current plan + next billing date

#### 9.2 — Chat History (`/client/conversations`)
- List of recent conversations
- Click to view full message thread
- Search conversations

#### 9.3 — My Invoices (`/client/invoices`)
- List of invoices with status
- "Pay Now" button → redirects to eSewa or Khalti
- Download invoice as PDF (optional, nice-to-have)

#### 9.4 — Embed Code (`/client/embed-code`)
- Shows their embed script with copy button
- Instructions on how to install
- Preview of what the widget looks like

#### 9.5 — Request Update (`/client/request-update`)
- Simple form: "What changed in your business? (new services, pricing, hours, etc.)"
- Submits to admin for review
- Admin updates KB files accordingly

#### 9.6 — My Profile (`/client/profile`)
- Edit name, email, phone, company name, website URL
- Change password

---

## 10. Payment Integration

### eSewa Integration
- **Test URL:** `https://rc-epay.esewa.com.np/api/epay/main/v2/form`
- **Production URL:** `https://epay.esewa.com.np/api/epay/main/v2/form`
- **Flow:** Client clicks "Pay Now" → redirect to eSewa → eSewa redirects back with success/failure → webhook confirms payment → update invoice status → enable chatbot if it was disabled

### Khalti Integration
- **Test URL:** `https://a.khalti.com/api/v2/epayment/initiate/`
- **Production URL:** `https://khalti.com/api/v2/epayment/initiate/`
- **Flow:** Similar to eSewa — initiate payment → user pays → callback URL receives confirmation → update invoice → enable chatbot

### After Payment Confirmed
1. Mark invoice as `paid`
2. Set `chatbot_enabled = true` for the client
3. Log payment reference

---

## 11. Scheduled Tasks (Laravel Scheduler)

Add these to `app/Console/Kernel.php`:

| Schedule | Task |
|----------|------|
| Daily at midnight | Check overdue invoices → if unpaid past `auto_disable_after_days`, set `chatbot_enabled = false` |
| Daily at 9 AM | Send billing reminders → if invoice due within `billing_reminder_days`, send WhatsApp/email reminder |
| Daily at midnight | Aggregate token usage → calculate daily cost estimates |
| Monthly on 1st | Auto-generate new monthly invoices for active clients |

---

## 12. Grok API Integration Details

### API Endpoint
```
POST https://api.x.ai/v1/chat/completions
```

### Headers
```
Authorization: Bearer {grok_api_key}
Content-Type: application/json
```

### Request Body
```json
{
  "model": "grok-3-mini",
  "messages": [
    {
      "role": "system",
      "content": "You are a helpful assistant for {business_name}. Answer questions using ONLY the provided knowledge base. Be friendly, concise, and helpful. If you don't know the answer, say so politely and suggest contacting the business directly.\n\n--- KNOWLEDGE BASE ---\n{all_markdown_content}\n--- END KNOWLEDGE BASE ---"
    },
    { "role": "user", "content": "previous message 1" },
    { "role": "assistant", "content": "previous response 1" },
    { "role": "user", "content": "new question" }
  ],
  "max_tokens": 500,
  "temperature": 0.7
}
```

### Response
```json
{
  "choices": [
    {
      "message": {
        "content": "Bot reply text here"
      }
    }
  ],
  "usage": {
    "prompt_tokens": 350,
    "completion_tokens": 120,
    "total_tokens": 470
  }
}
```

Extract `choices[0].message.content` as the bot reply.
Extract `usage.total_tokens` for logging.

---

## 13. Folder Structure (Laravel Project)

```
chatbotnepal/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── ClientController.php
│   │   │   │   ├── KnowledgeBaseController.php
│   │   │   │   ├── InvoiceController.php
│   │   │   │   ├── UsageController.php
│   │   │   │   └── SettingController.php
│   │   │   ├── Client/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── ConversationController.php
│   │   │   │   ├── InvoiceController.php
│   │   │   │   ├── EmbedController.php
│   │   │   │   ├── UpdateRequestController.php
│   │   │   │   └── ProfileController.php
│   │   │   └── Api/
│   │   │       ├── ChatController.php
│   │   │       ├── WidgetConfigController.php
│   │   │       └── WebhookController.php
│   │   └── Middleware/
│   │       ├── AdminMiddleware.php
│   │       └── ClientMiddleware.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── KnowledgeBase.php
│   │   ├── ChatConversation.php
│   │   ├── ChatMessage.php
│   │   ├── Invoice.php
│   │   ├── TokenUsageLog.php
│   │   ├── WidgetConfig.php
│   │   └── Setting.php
│   ├── Services/
│   │   ├── GrokService.php          ← Handles all Grok API communication
│   │   ├── ChatService.php          ← Chat logic (load KB, build prompt, save messages)
│   │   ├── InvoiceService.php       ← Invoice generation + payment processing
│   │   └── TokenUsageService.php    ← Token tracking and cost calculation
│   └── Console/
│       └── Kernel.php               ← Scheduled tasks
├── database/
│   ├── migrations/
│   │   ├── create_users_table.php
│   │   ├── create_knowledge_bases_table.php
│   │   ├── create_chat_conversations_table.php
│   │   ├── create_chat_messages_table.php
│   │   ├── create_invoices_table.php
│   │   ├── create_token_usage_logs_table.php
│   │   ├── create_widget_configs_table.php
│   │   └── create_settings_table.php
│   └── seeders/
│       ├── AdminUserSeeder.php
│       └── SettingsSeeder.php
├── public/
│   └── widget.js                    ← THE EMBED WIDGET
├── resources/
│   └── views/
│       ├── layouts/
│       │   ├── admin.blade.php
│       │   └── client.blade.php
│       ├── admin/
│       │   ├── dashboard.blade.php
│       │   ├── clients/
│       │   │   ├── index.blade.php
│       │   │   ├── create.blade.php
│       │   │   ├── edit.blade.php
│       │   │   └── knowledge-base.blade.php
│       │   ├── invoices.blade.php
│       │   ├── usage.blade.php
│       │   └── settings.blade.php
│       ├── client/
│       │   ├── dashboard.blade.php
│       │   ├── conversations.blade.php
│       │   ├── invoices.blade.php
│       │   ├── embed-code.blade.php
│       │   ├── request-update.blade.php
│       │   └── profile.blade.php
│       └── auth/
│           ├── login.blade.php
│           └── register.blade.php     (admin-only registration or disabled)
├── routes/
│   ├── web.php
│   └── api.php
├── .env
└── composer.json
```

---

## 14. Environment Variables (`.env`)

```
APP_NAME="ChatBot Nepal"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://chatbotnepal.isoftroerp.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=chatbotnepal_db
DB_USERNAME=chatbot_user
DB_PASSWORD=ChatBot@Db2026!

GROK_API_KEY=your-grok-api-key-here
GROK_API_URL=https://api.x.ai/v1/chat/completions
GROK_MODEL=grok-3-mini
GROK_MAX_TOKENS=500
GROK_TEMPERATURE=0.7

ESEWA_MERCHANT_ID=
ESEWA_SECRET_KEY=
ESEWA_ENV=test

KHALTI_SECRET_KEY=
KHALTI_ENV=test
```

---

## 15. Key Business Rules

1. **Chatbot is OFF if unpaid** — `chatbot_enabled` must be `false` if invoice is overdue past grace period
2. **Token usage must be logged** — every single API call logs tokens used, per client, per day
3. **Knowledge base is markdown** — stored in DB, not files on disk (easier to manage)
4. **One embed script per client** — each client gets a unique `api_token`
5. **Conversations are per-session** — new browser session = new conversation
6. **Admin can create clients** — clients don't self-register (admin onboards them manually)
7. **Widget must work on ANY website** — WordPress, Wix, HTML, Laravel, Shopify, etc.
8. **CORS must be configured** — widget makes cross-origin requests to the API
9. **Rate limiting** — limit chat API to prevent abuse (e.g., 30 requests per minute per token)

---

## 16. CORS Configuration

Since the widget runs on client websites (different domains) and calls your API, you need CORS headers:

```php
// In Laravel CORS config or middleware:
'allowed_origins' => ['*'],  // Widget can be on any domain
'allowed_methods' => ['GET', 'POST'],
'allowed_headers' => ['Content-Type', 'Accept'],
```

Only apply wildcard CORS to the `/api/chat` and `/api/widget-config/*` routes. Admin/client API routes should be same-origin only.

---

## 17. Security Considerations

1. **Rate limit** the `/api/chat` endpoint (30 requests/min per token)
2. **Validate api_token** on every chat request
3. **Sanitize all markdown** content before sending to Grok (prevent prompt injection)
4. **Hash all passwords** with bcrypt
5. **CSRF protection** on all web forms
6. **API authentication** using Laravel Sanctum for admin/client routes
7. **Input validation** on all endpoints
8. **SQL injection prevention** — use Eloquent ORM (never raw queries with user input)

---

## 18. Seeder Data

### Admin User
```
Name: Devbarat Prasad Patel
Email: isoftrosolutions@gmail.com
Password: Admin@ChatBot2026 (hashed)
Role: admin
```

### Demo Client (for testing)
```
Name: Jim Sathi Fitness
Email: jimsathi@demo.com
Password: Demo@123 (hashed)
Role: client
Plan: basic
Company: Jim Sathi Gym
Website: https://jimsathi.com
api_token: (auto-generate 64-char random string)
chatbot_enabled: true
```

### Demo Knowledge Base Files
Create 4 files for the demo client:

**about.md:**
```markdown
# About Jim Sathi Fitness
Jim Sathi is a premium fitness center located in Bharatpur, Chitwan, Nepal.
We have been serving the community since 2020.
Our gym features modern equipment, experienced trainers, and a friendly environment.
```

**services.md:**
```markdown
# Our Services & Pricing
- Gym Membership: Rs. 2,000/month
- Personal Training: Rs. 5,000/month
- Yoga Classes: Rs. 1,500/month
- Zumba Classes: Rs. 1,500/month
- Combo (Gym + Yoga): Rs. 3,000/month
- Student Discount: 20% off on all plans
```

**faq.md:**
```markdown
# Frequently Asked Questions
**Q: What are your opening hours?**
A: We are open from 5:30 AM to 9:00 PM, Monday to Saturday. Sunday is closed.

**Q: Do you have female trainers?**
A: Yes, we have dedicated female trainers available for female members.

**Q: Is there parking available?**
A: Yes, free parking is available for all members.

**Q: Can I freeze my membership?**
A: Yes, you can freeze up to 15 days per year with prior notice.
```

**contact.md:**
```markdown
# Contact Information
- Phone: 9800000000
- Email: info@jimsathi.com
- Location: Narayangarh Chowk, Bharatpur, Chitwan
- Facebook: facebook.com/jimsathi
- Instagram: @jimsathi
```

---

## 19. Build Order (For Claude Code)

Follow this exact sequence:

### Phase 1: Project Setup
1. Create Laravel 11 project
2. Configure `.env` with database settings
3. Install dependencies: Laravel Breeze, Sanctum, Tailwind CSS, Alpine.js

### Phase 2: Database
4. Create all migrations (8 tables)
5. Create all Eloquent models (8 models)
6. Create seeders (admin user, settings, demo client, demo KB)
7. Run migrations + seeders

### Phase 3: Core Backend
8. Create `GrokService.php` — Grok API integration
9. Create `ChatService.php` — chat logic
10. Create `ChatController.php` — `/api/chat` endpoint
11. Create `WidgetConfigController.php` — widget config endpoint
12. Configure CORS for widget routes
13. Add rate limiting

### Phase 4: Widget
14. Create `widget.js` — the embeddable chatbot widget
15. Test widget locally

### Phase 5: Admin Panel
16. Create admin middleware
17. Create admin layout (Blade + Tailwind)
18. Build all admin pages (dashboard, clients, KB editor, invoices, usage, settings)

### Phase 6: Client Dashboard
19. Create client middleware
20. Create client layout
21. Build all client pages (dashboard, conversations, invoices, embed code, profile)

### Phase 7: Billing
22. Create `InvoiceService.php`
23. Build eSewa integration
24. Build Khalti integration
25. Create webhook handlers
26. Add scheduled tasks (auto-invoice, reminders, auto-disable)

### Phase 8: Polish
27. Add form validation everywhere
28. Add flash messages / notifications
29. Test all flows end-to-end
30. Optimize queries (eager loading, indexes)

---

## 20. Summary

This document contains **everything** Claude Code needs to build the complete ChatBot Nepal platform:

- **8 database tables** with full column definitions
- **20+ API endpoints** with request/response formats
- **Chat API logic** step-by-step
- **Widget specification** with all features
- **8 admin pages** and **6 client pages**
- **Payment integration** (eSewa + Khalti)
- **Scheduled tasks** for automation
- **Grok API** integration details
- **Folder structure** for the entire Laravel project
- **Security, CORS, rate limiting** rules
- **Seeder data** for testing
- **Build order** — exact sequence to follow

**The coding starts now. Claude Code builds everything.**

---

*Project Spec by Devbarat Prasad Patel — Nepal Cyber Firm / iSoftro*
*For use with Claude Code (terminal) — April 2026*
