# ChatBot Nepal — Stitch AI Design Prompt
## Use this prompt in Google Stitch AI to generate all UI pages

---

## MASTER PROMPT (Paste this first)

```
I am building a B2B SaaS platform called "ChatBot Nepal" — a service that sells AI-powered chatbot widgets to Nepali businesses. I need you to design ALL the UI pages for this platform.

BRAND & DESIGN SYSTEM:
- Brand Name: ChatBot Nepal
- Tagline: "AI-Powered Chatbots for Nepali Businesses"
- Primary Color: #4F46E5 (Indigo)
- Secondary Color: #10B981 (Emerald Green)
- Accent Color: #F59E0B (Amber for warnings/highlights)
- Danger Color: #EF4444 (Red)
- Background: #F9FAFB (Light gray)
- Sidebar Background: #1E1B4B (Dark indigo)
- Text Primary: #111827
- Text Secondary: #6B7280
- Font: Inter (Google Font)
- Border Radius: 8px for cards, 6px for buttons, 12px for modals
- Shadows: Subtle drop shadows on cards
- Style: Clean, modern, minimal SaaS dashboard. Similar to Stripe Dashboard, Intercom, or Crisp Chat admin panels.
- Icons: Heroicons or Lucide icons
- Charts: Clean line charts and bar charts with brand colors
- Tables: Zebra-striped rows, hover highlight, action buttons on right
- Forms: Full-width inputs with labels above, primary color focus ring
- The design should feel professional but not overly corporate — this is for small/medium Nepali businesses

The platform has 3 user interfaces:
1. LOGIN PAGE (shared)
2. ADMIN PANEL (8 pages — for the platform owner, Devbarat)
3. CLIENT DASHBOARD (6 pages — for business clients who buy the chatbot service)

Below I will describe each page in full detail. Please design them all.
```

---

## PAGE 1: LOGIN PAGE

```
PAGE: Login Page
URL: /login

Design a clean, centered login page for ChatBot Nepal.

LEFT SIDE (60% width):
- Dark indigo background (#1E1B4B)
- Large ChatBot Nepal logo (chat bubble icon + text)
- Tagline: "AI-Powered Chatbots for Nepali Businesses"
- Below tagline, show 3 feature highlights with icons:
  • "24/7 AI Customer Support" with a clock icon
  • "Works on Any Website" with a globe icon
  • "Built for Nepal" with a Nepal flag or map pin icon
- At the bottom: "Trusted by 50+ businesses across Nepal" (social proof text)

RIGHT SIDE (40% width):
- White card with padding
- Title: "Sign in to your account"
- Subtitle: "Enter your credentials to access the dashboard"
- Form fields:
  • Email Address (text input with envelope icon)
  • Password (text input with lock icon, show/hide toggle)
  • "Remember me" checkbox
  • "Forgot password?" link (right-aligned)
- "Sign In" button (full width, primary indigo color, rounded)
- Below button: "Need help? Contact support" link
- Footer: "© 2026 ChatBot Nepal by iSoftro"

Mobile: Stack vertically — hide left panel, show only the login form with a small logo on top.
```

---

## PAGE 2: ADMIN DASHBOARD

```
PAGE: Admin Dashboard
URL: /admin/dashboard

This is the main overview page the platform owner (Devbarat) sees after login.

SIDEBAR (left, dark indigo #1E1B4B, 260px wide, fixed):
- Top: ChatBot Nepal logo + "Admin" badge
- Navigation items with icons:
  • Dashboard (home icon) — ACTIVE/HIGHLIGHTED
  • Clients (users icon)
  • Invoices (receipt icon)
  • Token Usage (chart-bar icon)
  • Settings (cog icon)
- Bottom of sidebar: Admin name "Devbarat" with avatar circle, logout button

TOP BAR (white, sticky):
- Left: Page title "Dashboard"
- Right: Notification bell icon (with red dot), "Add New Client" button (primary color)

MAIN CONTENT:

Row 1 — 4 Stat Cards (equal width, horizontal):
  Card 1: "Total Clients" — big number (e.g., 12), icon: users, subtitle: "3 active this week", color accent: indigo
  Card 2: "Conversations Today" — big number (e.g., 47), icon: chat-bubbles, subtitle: "+12% from yesterday", color accent: green
  Card 3: "Tokens Used Today" — big number (e.g., 23,450), icon: cpu-chip, subtitle: "Est. Rs. 85", color accent: amber
  Card 4: "Revenue This Month" — big number (e.g., "Rs. 45,000"), icon: currency, subtitle: "8 invoices paid", color accent: green

Row 2 — Two Column Layout:
  LEFT (60%): "Conversations Trend" — Line chart showing conversations per day for the last 30 days. X-axis: dates. Y-axis: count. Line color: indigo. Area fill: light indigo.

  RIGHT (40%): "Client Distribution by Plan" — Donut/pie chart showing how many clients are on each plan (Basic, Standard, Growth, Pro). Use different shades of indigo/green.

Row 3 — Full Width:
  "Recent Activity" — Table with columns:
  | Time | Event | Client | Details |
  Example rows:
  - "2 min ago | New conversation | Jim Sathi Gym | Visitor asked about pricing"
  - "15 min ago | Invoice paid | Hotel Everest | Rs. 3,999 via eSewa"
  - "1 hour ago | Chatbot disabled | ABC School | Overdue invoice"
  - "3 hours ago | KB updated | Dental Care Plus | Updated services.md"
  Show 10 rows with pagination.
```

---

## PAGE 3: ADMIN — CLIENT MANAGEMENT

```
PAGE: Client Management (List View)
URL: /admin/clients

SIDEBAR: Same as dashboard, "Clients" is now ACTIVE/HIGHLIGHTED.

TOP BAR:
- Left: Page title "Clients" + subtitle "Manage all your chatbot clients"
- Right: Search bar (search by name/email/company) + "Add New Client" button (primary)

MAIN CONTENT:

Filter Tabs (horizontal, pill-style):
- All (12) | Active (9) | Inactive (2) | Suspended (1)
Active tab has primary color background.

Client Table:
| Column | Description |
|--------|-------------|
| Client | Avatar circle with initials + Name + Email (stacked) |
| Company | Company name |
| Plan | Badge showing plan name — color coded: Basic=gray, Standard=blue, Growth=green, Pro=purple |
| Status | Badge — Active=green, Inactive=gray, Suspended=red |
| Chatbot | Toggle switch (green=ON, gray=OFF) — clickable to toggle |
| Conversations | Number (e.g., "234") |
| Tokens This Month | Number (e.g., "12,450") |
| Actions | Three-dot menu → Edit, Knowledge Base, View Usage, Delete |

Each row has subtle hover effect. Zebra-striped rows.
Pagination at bottom: "Showing 1-10 of 12 clients" with page numbers.

EMPTY STATE (if no clients):
- Illustration of a chat bubble with a plus sign
- Text: "No clients yet"
- Subtitle: "Add your first client to get started"
- "Add New Client" button
```

---

## PAGE 4: ADMIN — ADD/EDIT CLIENT

```
PAGE: Add New Client / Edit Client
URL: /admin/clients/create OR /admin/clients/{id}/edit

SIDEBAR: Same, "Clients" active.

TOP BAR:
- Left: Breadcrumb "Clients > Add New Client" (or "Edit: Jim Sathi Gym")
- Right: "Save Client" button (primary) + "Cancel" link

MAIN CONTENT — White card with form:

Section 1: "Basic Information"
- Name (text input, required) — placeholder: "Full name"
- Email (email input, required) — placeholder: "client@example.com"
- Phone (text input) — placeholder: "+977 98XXXXXXXX"
- Password (password input, required for create, optional for edit) — placeholder: "Minimum 8 characters"
- Confirm Password (password input)

Section 2: "Business Details"
- Company Name (text input) — placeholder: "Business or company name"
- Website URL (url input) — placeholder: "https://example.com"

Section 3: "Plan & Status"
- Plan (dropdown): Basic / Standard / Growth / Pro — each option shows the monthly price beside it
- Status (dropdown): Active / Inactive / Suspended
- Chatbot Enabled (toggle switch with label "Enable chatbot widget for this client")

Section 4: "Widget Customization"
- Bot Name (text input) — placeholder: "Assistant" — "Name shown in the chat widget header"
- Welcome Message (textarea) — placeholder: "Namaste! How can I help you today?"
- Primary Color (color picker input) — default: #4F46E5
- Widget Position (radio buttons): Bottom Right / Bottom Left
- Show "Powered by ChatBot Nepal" (toggle switch) — default: ON

FOOTER of card:
- "Save Client" button (primary, large) + "Cancel" button (outline/secondary)

ON SAVE (for new client):
- Show success toast: "Client created successfully!"
- Show the auto-generated API token in a modal/alert with copy button:
  "Client API Token: a8f3b2c1d4e5f6... (Click to copy)"
  "This token is used in the embed script. Save it securely."
```

---

## PAGE 5: ADMIN — KNOWLEDGE BASE EDITOR

```
PAGE: Knowledge Base Editor
URL: /admin/clients/{id}/knowledge-base

SIDEBAR: Same, "Clients" active.

TOP BAR:
- Left: Breadcrumb "Clients > Jim Sathi Gym > Knowledge Base"
- Right: "Add New File" button (primary) + "Test Chatbot" button (outline, with chat icon)

MAIN CONTENT — Two Column Layout:

LEFT COLUMN (35% width) — File List Panel:
- Card with header "Knowledge Base Files"
- List of files, each showing:
  • File icon (document icon)
  • File name: "about.md"
  • Type badge: "About" (blue), "Services" (green), "FAQ" (amber), "Contact" (purple), "Custom" (gray)
  • Last updated: "2 days ago"
  • Click to select/edit
- Active/selected file has left border highlight (primary color)
- Each file has a delete icon button (trash, red on hover) with confirmation
- At the bottom: "+ Add New File" button

RIGHT COLUMN (65% width) — Editor Panel:
- Card with header showing selected file name: "Editing: services.md"
- Below header: Two tab buttons: "Edit" (active) | "Preview"
- EDIT TAB:
  • Full-height textarea (monospace font, code-editor style, dark background #1F2937 with light text)
  • Content shows markdown text
  • Line numbers on the left side (like a code editor)
  • Character count at bottom right: "342 characters"
- PREVIEW TAB:
  • Renders the markdown as formatted HTML
  • Shows how the content will look (headings, bold, lists, etc.)
- Bottom of card: "Save Changes" button (primary) + "Discard" button (outline)
- Auto-save indicator: "Last saved: 2 minutes ago" or "Unsaved changes" (amber warning)

"TEST CHATBOT" MODAL (when clicking the Test Chatbot button):
- Modal overlay (centered, 400px wide, 600px tall)
- Title: "Test Chatbot — Jim Sathi Gym"
- Looks exactly like the actual embed widget but inside a modal
- Chat interface: message bubbles, input field, send button
- Uses the actual /api/chat endpoint with this client's token
- "Close" button at top right

EMPTY STATE (if no KB files):
- Illustration of a document with a plus sign
- Text: "No knowledge base files yet"
- Subtitle: "Add files to teach the chatbot about this business"
- "Add First File" button

"ADD NEW FILE" MODAL:
- Title: "Add Knowledge Base File"
- File Name (text input) — placeholder: "e.g., services.md"
- File Type (dropdown): About / Services / FAQ / Contact / Custom
- Content (textarea, large) — placeholder: "Write your markdown content here..."
- "Create File" button (primary) + "Cancel" button
```

---

## PAGE 6: ADMIN — INVOICES

```
PAGE: Invoice Management
URL: /admin/invoices

SIDEBAR: Same, "Invoices" is ACTIVE.

TOP BAR:
- Left: Page title "Invoices" + subtitle "Track all billing and payments"
- Right: "Generate Invoice" button (primary)

MAIN CONTENT:

Row 1 — 3 Stat Cards:
  Card 1: "Total Revenue" — "Rs. 1,45,000" — subtitle: "All time", icon: banknote, color: green
  Card 2: "Pending Amount" — "Rs. 12,998" — subtitle: "4 unpaid invoices", icon: clock, color: amber
  Card 3: "Overdue" — "Rs. 3,999" — subtitle: "1 overdue invoice", icon: exclamation-triangle, color: red

Filter Tabs:
- All (24) | Pending (4) | Paid (18) | Overdue (1) | Cancelled (1)

Invoice Table:
| Column | Description |
|--------|-------------|
| Invoice # | e.g., "INV-2026-0015" |
| Client | Name + company (stacked) |
| Amount | "Rs. 3,999" (bold) |
| Type | Badge — Setup=blue, Monthly=green, Yearly=purple |
| Period | "Apr 1 – Apr 30, 2026" |
| Due Date | "Apr 30, 2026" — if overdue, show in red text |
| Status | Badge — Pending=amber, Paid=green, Overdue=red, Cancelled=gray |
| Payment | "eSewa" or "Khalti" or "Manual" or "—" (if unpaid) |
| Actions | "Mark Paid" button (green, small) if pending/overdue, "View" link |

"GENERATE INVOICE" MODAL:
- Title: "Generate New Invoice"
- Client (dropdown — searchable, lists all active clients)
- Type (dropdown): Setup Fee / Monthly / Yearly
- Amount (number input, auto-filled based on type + client plan, editable)
- Billing Period Start (date picker)
- Billing Period End (date picker)
- Due Date (date picker)
- "Generate Invoice" button (primary) + "Cancel"

"MARK AS PAID" MODAL:
- Title: "Mark Invoice as Paid"
- Shows invoice summary: Invoice #, Client, Amount
- Payment Method (dropdown): eSewa / Khalti / Bank Transfer / Cash / Manual
- Payment Reference (text input) — placeholder: "Transaction ID or reference"
- Payment Date (date picker, default: today)
- "Confirm Payment" button (green) + "Cancel"
```

---

## PAGE 7: ADMIN — TOKEN USAGE

```
PAGE: Token Usage Analytics
URL: /admin/usage

SIDEBAR: Same, "Token Usage" is ACTIVE.

TOP BAR:
- Left: Page title "Token Usage" + subtitle "Monitor API consumption and costs"
- Right: Date range picker (Last 7 days / Last 30 days / This Month / Custom Range)

MAIN CONTENT:

Row 1 — 4 Stat Cards:
  Card 1: "Total Tokens Today" — "45,230" — subtitle: "1,245 API calls", icon: cpu-chip, color: indigo
  Card 2: "Estimated Cost Today" — "Rs. 165" — subtitle: "Based on Grok pricing", icon: calculator, color: amber
  Card 3: "Total Tokens This Month" — "892,450" — icon: chart-bar, color: indigo
  Card 4: "Estimated Cost This Month" — "Rs. 3,250" — icon: banknote, color: amber

Row 2 — Full Width Chart:
  "Daily Token Usage" — Bar chart showing tokens used per day for selected period.
  X-axis: dates. Y-axis: token count.
  Two bar colors: Input Tokens (indigo) and Output Tokens (emerald).
  Hover tooltip shows: "Apr 10: Input: 12,340 | Output: 5,670 | Cost: Rs. 65"

Row 3 — Full Width Table:
  "Usage by Client"
  | Client | Company | Plan | API Calls | Input Tokens | Output Tokens | Total Tokens | Est. Cost |
  Example rows:
  - Jim Sathi Gym | Jim Sathi | Basic | 234 | 45,000 | 18,000 | 63,000 | Rs. 230
  - Hotel Everest | Hotel Everest | Growth | 567 | 112,000 | 45,000 | 157,000 | Rs. 570
  Sort by Total Tokens descending by default.
  Each row clickable → goes to that client's detailed usage.

Row 4 — Alert Banner (conditional):
  If any client's token usage exceeds their plan's expected range:
  Yellow warning banner: "⚠️ Jim Sathi Gym is using 3x more tokens than typical Basic plan clients. Consider upgrading their plan."
  With "View Details" and "Dismiss" buttons.
```

---

## PAGE 8: ADMIN — PLATFORM SETTINGS

```
PAGE: Platform Settings
URL: /admin/settings

SIDEBAR: Same, "Settings" is ACTIVE.

TOP BAR:
- Left: Page title "Settings" + subtitle "Configure your ChatBot Nepal platform"
- Right: "Save All Changes" button (primary)

MAIN CONTENT — Multiple Settings Cards (stacked vertically):

CARD 1: "AI Configuration"
- Grok API Key (password input with show/hide toggle) — "Your xAI API key"
- Model (dropdown): grok-3-mini / grok-3 — "AI model to use for responses"
- Max Tokens (number input, default: 500) — "Maximum response length"
- Temperature (number input with 0.1 steps, range 0-1, default: 0.7) — "Higher = more creative, Lower = more focused"
- System Prompt (large textarea, 5 rows) — "The instruction given to the AI before every conversation"
  Default text shown: "You are a helpful assistant for {business_name}. Answer questions using ONLY the provided knowledge base..."
- "Test API Connection" button (outline) — tests if Grok API key works, shows green checkmark or red error

CARD 2: "Platform Information"
- Platform Name (text input) — default: "ChatBot Nepal"
- Admin Email (email input) — default: "isoftrosolutions@gmail.com"
- Support Phone (text input) — "+977 98XXXXXXXX"

CARD 3: "Payment Gateway — eSewa"
- eSewa Merchant ID (text input)
- eSewa Secret Key (password input with toggle)
- Environment (radio): Test / Production
- "Test Connection" button

CARD 4: "Payment Gateway — Khalti"
- Khalti Secret Key (password input with toggle)
- Environment (radio): Test / Production
- "Test Connection" button

CARD 5: "Billing Automation"
- Billing Reminder Days (number input, default: 3) — "Send payment reminder X days before due date"
- Auto-Disable After Days (number input, default: 7) — "Disable chatbot X days after invoice is overdue"
- Auto-Generate Monthly Invoices (toggle switch) — "Automatically create invoices on the 1st of each month"

CARD 6: "Plan Pricing" (reference table, non-editable or editable)
  | Plan | Monthly | Yearly | Setup Fee |
  | Basic | Rs. 999 | Rs. 9,990 | Rs. 3,000 |
  | Standard | Rs. 2,000 | Rs. 20,000 | Rs. 3,000 |
  | Growth | Rs. 3,999 | Rs. 39,990 | Rs. 3,000 |
  | Pro | Rs. 6,999 | Rs. 69,990 | Rs. 5,000 |
  Each cell is an editable number input.
  "Save Pricing" button.

FOOTER: "Save All Changes" button (primary, large, full width)
```

---

## PAGE 9: ADMIN — CONVERSATION VIEWER

```
PAGE: Client Conversations
URL: /admin/clients/{id}/conversations

SIDEBAR: Same, "Clients" active.

TOP BAR:
- Left: Breadcrumb "Clients > Jim Sathi Gym > Conversations"
- Right: Search bar + Date filter

MAIN CONTENT — Two Column Layout:

LEFT COLUMN (35%): "Conversation List"
- List of conversations, each showing:
  • Visitor icon (anonymous avatar)
  • Visitor ID or name if available: "Visitor #a8f3b2" or "Ram Sharma"
  • Last message preview (truncated): "What are your gym timings..."
  • Timestamp: "2 hours ago"
  • Message count badge: "8 messages"
  • Source URL: "from /pricing page"
- Active conversation highlighted with left border
- Sorted by most recent first
- Scroll to load more

RIGHT COLUMN (65%): "Conversation Detail"
- Header: Visitor info — name/ID, email if provided, source URL, started at, status
- Message area (chat-style):
  • Visitor messages: Left-aligned, gray bubbles
  • Bot messages: Right-aligned, indigo bubbles
  • Timestamp below each message
  • Token count shown subtly on bot messages: "45 tokens"
- Bottom: Summary stats — "8 messages | 340 total tokens | Started 2 hours ago"

EMPTY STATE:
- "No conversations yet for this client"
- "Conversations will appear here once visitors start chatting on the client's website"
```

---

## PAGE 10: CLIENT — DASHBOARD

```
PAGE: Client Dashboard
URL: /client/dashboard

SIDEBAR (left, dark indigo, 240px wide):
- Top: ChatBot Nepal logo + client's company name below
- Navigation:
  • Dashboard (home icon) — ACTIVE
  • Chat History (chat icon)
  • Invoices (receipt icon)
  • Embed Code (code icon)
  • Request Update (pencil icon)
  • My Profile (user icon)
- Bottom: Client name + logout button

TOP BAR:
- Left: "Welcome back, Jim Sathi Gym!" (greeting with company name)
- Right: Chatbot status badge — green "Online" or red "Offline"

MAIN CONTENT:

Row 1 — Chatbot Status Card (full width, prominent):
- If ONLINE: Green background card
  • Large checkmark icon
  • "Your chatbot is LIVE and helping customers!"
  • "Active since: April 1, 2026"
  • "Embed code installed on: jimsathi.com"
- If OFFLINE: Red/amber background card
  • Warning icon
  • "Your chatbot is currently OFFLINE"
  • Reason: "Payment overdue" or "Disabled by admin"
  • "Contact support" or "Pay Now" button

Row 2 — 3 Stat Cards:
  Card 1: "Total Conversations" — "234" — subtitle: "All time", icon: chat, color: indigo
  Card 2: "Conversations This Month" — "47" — subtitle: "+15% from last month", icon: trending-up, color: green
  Card 3: "Current Plan" — "Basic Plan" — subtitle: "Rs. 999/month | Next billing: May 1", icon: credit-card, color: indigo

Row 3 — Two Column:
  LEFT (50%): "Most Asked Questions" — Card showing top 5 most frequent visitor questions:
    1. "What are your gym timings?" — asked 23 times
    2. "How much is the membership?" — asked 18 times
    3. "Do you have female trainers?" — asked 12 times
    4. "Where are you located?" — asked 10 times
    5. "Student discount available?" — asked 8 times
  Each with a count bar (horizontal progress bar showing relative frequency).

  RIGHT (50%): "Recent Conversations" — Card showing last 5 conversations:
    • Visitor avatar + ID/name
    • First message preview
    • Timestamp
    • "View" link
    At the bottom: "View All Conversations →" link
```

---

## PAGE 11: CLIENT — CHAT HISTORY

```
PAGE: Chat History
URL: /client/conversations

SIDEBAR: Same, "Chat History" active.

TOP BAR:
- Left: Page title "Chat History" + subtitle "See what your customers are asking"
- Right: Search bar + Date filter dropdown (Today / This Week / This Month / All Time)

MAIN CONTENT — Same two-column layout as admin conversation viewer:

LEFT (35%): Conversation list with visitor info, last message preview, timestamp, message count.

RIGHT (65%): Selected conversation messages in chat bubble format.
- Visitor messages on left (gray bubbles)
- Bot responses on right (indigo bubbles)
- Timestamps on each message

Key difference from admin view: No token count shown, no technical details. Keep it simple for the client.

EMPTY STATE:
- Friendly illustration
- "No conversations yet!"
- "Once visitors start chatting on your website, their conversations will appear here."
- "Need help installing the chatbot? Check the Embed Code page →" link
```

---

## PAGE 12: CLIENT — INVOICES

```
PAGE: My Invoices
URL: /client/invoices

SIDEBAR: Same, "Invoices" active.

TOP BAR:
- Left: Page title "My Invoices"
- Right: Nothing (clients can't generate invoices)

MAIN CONTENT:

Current Plan Card (top, full width):
- "Your Plan: Growth Plan — Rs. 3,999/month"
- "Next billing date: May 1, 2026"
- "Payment method: eSewa"

Invoice Table:
| Invoice # | Amount | Type | Period | Due Date | Status | Action |
| INV-2026-0012 | Rs. 3,999 | Monthly | Apr 1-30 | Apr 30, 2026 | Pending | "Pay Now" button |
| INV-2026-0008 | Rs. 3,999 | Monthly | Mar 1-31 | Mar 31, 2026 | Paid ✅ | — |
| INV-2026-0001 | Rs. 3,000 | Setup | — | Jan 15, 2026 | Paid ✅ | — |

"Pay Now" button: Opens a modal or page with two payment options:
- eSewa button (green, with eSewa logo)
- Khalti button (purple, with Khalti logo)
- "Or contact admin for manual payment" text

Status badges: Pending=amber, Paid=green, Overdue=red

OVERDUE ALERT (if applicable):
- Red banner at top: "⚠️ You have an overdue invoice. Your chatbot may be disabled until payment is received."
- "Pay Now" button inside the banner
```

---

## PAGE 13: CLIENT — EMBED CODE

```
PAGE: Embed Code
URL: /client/embed-code

SIDEBAR: Same, "Embed Code" active.

TOP BAR:
- Left: Page title "Embed Code" + subtitle "Install the chatbot on your website"

MAIN CONTENT:

Card 1: "Your Embed Script" (main card, prominent)
- Instruction text: "Copy this code and paste it just before the </body> tag on your website."
- Code block (dark background, monospace font):
  <script src="https://chatbotnepal.isoftroerp.com/widget.js" data-token="a8f3b2c1d4e5..."></script>
- "Copy to Clipboard" button (with clipboard icon, shows "Copied!" toast on click)
- Note: "This code works on any website — WordPress, Wix, Shopify, custom HTML, or any platform."

Card 2: "Installation Instructions" (expandable accordion sections)
- Section 1: "WordPress" — Step by step:
  1. Go to Appearance → Theme Editor (or use a plugin like "Insert Headers & Footers")
  2. Find the footer.php file or the footer section
  3. Paste the embed code just before </body>
  4. Save changes

- Section 2: "HTML Website" — Step by step:
  1. Open your index.html (or main template file)
  2. Find the </body> tag
  3. Paste the embed code on the line above it
  4. Upload the file to your server

- Section 3: "Wix / Shopify / Website Builders" — Step by step:
  1. Go to Settings → Custom Code (or equivalent)
  2. Add a new code snippet
  3. Paste the embed code
  4. Set placement to "Footer" or "Before </body>"
  5. Save and publish

- Section 4: "Need Help?" —
  "Can't install it yourself? No problem! Send us your website login details and we'll do it for you."
  "Contact: isoftrosolutions@gmail.com or WhatsApp: +977 98XXXXXXXX"

Card 3: "Widget Preview"
- A small preview window showing what the chatbot looks like
- Shows the floating chat button (circle, indigo)
- Shows the opened chat window with:
  • Header: "Jim Sathi Gym" + bot name
  • Welcome message: "Namaste! How can I help you today?"
  • Input field with "Type a message..." placeholder
  • Send button
- Below preview: "Want to customize colors or the welcome message? Contact admin." text
```

---

## PAGE 14: CLIENT — REQUEST UPDATE

```
PAGE: Request Knowledge Base Update
URL: /client/request-update

SIDEBAR: Same, "Request Update" active.

TOP BAR:
- Left: Page title "Request Update" + subtitle "Tell us what changed in your business"

MAIN CONTENT:

Card 1: "What's Changed?" (main form)
- Intro text: "Did your services, pricing, hours, or other information change? Let us know and we'll update your chatbot's knowledge base within 24 hours."
- Category (dropdown): Pricing Changed / New Services Added / Hours Changed / Contact Info Changed / Other
- Description (large textarea, 6 rows) — placeholder: "Describe what changed. For example: 'We increased gym membership to Rs. 2,500/month' or 'We added a new Zumba class on Saturdays'"
- Urgency (radio buttons): Normal (update within 24 hours) / Urgent (update within 6 hours)
- Attachment (file upload, optional) — "Upload a file with your updated info (PDF, image, or text file)"
- "Submit Request" button (primary)

Card 2: "Previous Requests" (below the form)
- Table showing past update requests:
  | Date | Category | Description (truncated) | Status |
  | Apr 10, 2026 | Pricing | "Increased monthly fee to..." | Completed ✅ |
  | Mar 25, 2026 | New Service | "Added personal training..." | Completed ✅ |
  Status badges: Pending=amber, In Progress=blue, Completed=green

Success State (after submitting):
- Green checkmark animation
- "Request submitted successfully!"
- "We'll update your chatbot within 24 hours."
- "Submit another request" link
```

---

## PAGE 15: CLIENT — MY PROFILE

```
PAGE: My Profile
URL: /client/profile

SIDEBAR: Same, "My Profile" active.

TOP BAR:
- Left: Page title "My Profile"
- Right: "Save Changes" button (primary)

MAIN CONTENT:

Card 1: "Personal Information"
- Avatar circle (large, with initials, option to upload photo)
- Name (text input)
- Email (text input) — with note: "This is your login email"
- Phone (text input)

Card 2: "Business Information"
- Company Name (text input)
- Website URL (url input)
- Business Category (dropdown): Gym / Restaurant / School / Hotel / Clinic / E-Commerce / Real Estate / Tech / Other

Card 3: "Change Password"
- Current Password (password input)
- New Password (password input)
- Confirm New Password (password input)
- Password strength indicator bar
- "Update Password" button

Card 4: "Your Plan" (read-only info card)
- Plan: "Growth Plan — Rs. 3,999/month"
- Status: "Active" (green badge)
- Member since: "January 15, 2026"
- API Token: "a8f3b2c1..." (partially masked, with "Show" toggle and "Copy" button)
- "To upgrade or change your plan, contact admin." text

FOOTER: "Save Changes" button (primary, full width)
```

---

## PAGE 16: THE CHATBOT WIDGET (Design Reference)

```
COMPONENT: Embeddable Chatbot Widget
This is not a page — it's the floating widget that appears on client websites.

FLOATING BUTTON (collapsed state):
- Circle, 60px diameter, fixed bottom-right corner (24px from edges)
- Background: primary color (default #4F46E5, customizable per client)
- Icon: Chat bubble icon, white, centered
- Subtle shadow and hover scale effect (1.05x)
- Unread indicator: small red dot if there's an unread welcome message
- Click to open chat window

CHAT WINDOW (expanded state):
- Fixed position, bottom-right corner
- Size: 380px wide × 520px tall (desktop), full width × 80vh (mobile)
- Border radius: 16px
- Box shadow: large, prominent

HEADER:
- Background: primary color
- Left: Bot avatar (small circle) + business name (white, bold) + "Online" indicator (green dot)
- Right: Close button (X icon, white)

MESSAGE AREA:
- Background: #F3F4F6 (light gray)
- Scrollable
- Welcome message appears first (from bot, with slight typing delay animation)
- Visitor messages: Right-aligned, primary color background, white text, rounded bubble
- Bot messages: Left-aligned, white background, dark text, rounded bubble, small bot avatar
- Timestamps: Subtle, small, gray, below each message group
- Typing indicator: Three bouncing dots in a gray bubble when waiting for bot response

INPUT AREA:
- White background
- Text input: "Type a message..." placeholder, full width
- Send button: Primary color circle with arrow/send icon
- Input auto-resizes for multi-line (up to 3 lines)
- Enter key sends message, Shift+Enter for new line

FOOTER:
- Small text: "Powered by ChatBot Nepal" (links to chatbotnepal.isoftroerp.com)
- If show_powered_by is false for this client, footer is hidden

MOBILE BEHAVIOR:
- Widget takes full screen width on screens < 480px
- Header becomes a top bar
- Close button becomes a back arrow
- Input sticks to bottom with the keyboard

ANIMATIONS:
- Open: Slide up + fade in (200ms)
- Close: Slide down + fade out (150ms)
- New message: Slight bounce/slide-in animation
- Typing indicator: Three dots bouncing with staggered delay
```

---

## FINAL NOTES FOR STITCH AI

```
DESIGN NOTES:
1. All pages should share the same sidebar navigation, top bar style, and card design system.
2. Use consistent spacing: 24px page padding, 16px card padding, 8px between elements.
3. All tables should have: zebra striping, hover row highlight, responsive horizontal scroll on mobile.
4. All forms should have: labels above inputs, helper text below inputs, validation error states (red border + error message).
5. All modals should have: overlay backdrop (semi-transparent black), centered card, close X button, primary action button + cancel button.
6. Toast notifications: Top-right corner, auto-dismiss after 3 seconds, green for success, red for error, amber for warning.
7. Loading states: Skeleton screens for cards, spinner for buttons during async actions.
8. Empty states: Friendly illustration + descriptive text + action button for every list/table page.
9. The overall feel should be clean, modern, and professional — like Stripe Dashboard or Intercom.
10. Design for both desktop (1440px) and mobile (375px) viewports.
```
