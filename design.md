# VISUAL IDENTITY SYSTEM — ChatBot Nepal
### Design System Extracted from Ad Reference (ad5_convert_visitors.png)
### Agency-Grade | May 2026

---

> **Source Reference:** This design system is derived directly from the ChatBot Nepal ad creative `ad5_convert_visitors.png`. Every color, spacing rule, typography choice, component pattern, and layout principle below is extracted from that reference image and systematized for scalable production across all future ads, landing pages, social posts, and brand assets.

---

## DESIGN PHILOSOPHY

**"Trustworthy Conversion Energy"**

ChatBot Nepal's visual identity balances three tensions:

| Tension | Resolution |
|--------|-----------|
| Professional vs Approachable | Clean white layouts + human photography |
| Tech product vs Local market | Modern UI patterns + Nepali pricing signals |
| Urgency vs Trust | Green energy CTAs + credibility stats |

The design language says: *"We are a modern, capable tool — and we're made for businesses like yours."*

---

## COLOR PALETTE

Extracted precisely from the reference ad.

### Primary Colors

| Name | HEX | RGB | Usage |
|------|-----|-----|-------|
| **Brand Green** | `#1DB954` | rgb(29, 185, 84) | Primary CTA buttons, key headline accent, icon fills, borders |
| **Deep Navy** | `#0B1E3D` | rgb(11, 30, 61) | Header/footer bars, dark section backgrounds, overlays |
| **Pure White** | `#FFFFFF` | rgb(255, 255, 255) | Main content backgrounds, text on dark, card surfaces |

### Secondary Colors

| Name | HEX | RGB | Usage |
|------|-----|-----|-------|
| **Ink Black** | `#0F172A` | rgb(15, 23, 42) | Primary body text, headline text on light backgrounds |
| **Soft Grey** | `#F1F5F9` | rgb(241, 245, 249) | Icon card backgrounds, secondary sections, dividers |
| **Medium Grey** | `#64748B` | rgb(100, 116, 139) | Subtext, captions, metadata, placeholder text |
| **Border Grey** | `#E2E8F0` | rgb(226, 232, 240) | Card borders, divider lines, input field borders |

### Accent / Status Colors

| Name | HEX | RGB | Usage |
|------|-----|-----|-------|
| **Signal Blue** | `#2563EB` | rgb(37, 99, 235) | Logo icon, secondary links, info callouts |
| **Alert Amber** | `#F59E0B` | rgb(245, 158, 11) | Warning states, highlight badges |
| **Success Teal** | `#0EA5E9` | rgb(14, 165, 233) | Graph/chart elements, data visualization |
| **Danger Red** | `#EF4444` | rgb(239, 68, 68) | Error states, cancellation flows |

### Color Usage Rules

```
DO:
✅ Use Brand Green for ALL primary CTAs — no exceptions
✅ Use Deep Navy for section backgrounds that need authority
✅ Use Pure White as the default page/card background
✅ Use Ink Black for all body and headline text on light backgrounds
✅ Use Soft Grey for subtle card fills and alternate row backgrounds

DON'T:
❌ Never place Brand Green text on Deep Navy (insufficient contrast)
❌ Never use more than 2 accent colors in a single ad/post
❌ Never use a light green — the green must always be the Brand Green HEX
❌ Never use gradient fills on CTA buttons — solid Brand Green only
❌ Never use white text on white or near-white backgrounds
```

### Color Ratio Per Layout (from reference ad)

```
Light-mode layouts:   White 60% | Ink Black 20% | Brand Green 15% | Deep Navy 5%
Dark-mode sections:   Deep Navy 70% | White 20% | Brand Green 10%
CTA banners:          Deep Navy 50% | Brand Green 40% | White 10%
```

---

## TYPOGRAPHY SYSTEM

### Font Family

**Primary Font: Poppins**
- Available free on Google Fonts
- Used for ALL text in the reference ad
- Rounds out the techy product feel with approachable letterforms
- Alternative if unavailable: Inter, Nunito Sans, or Montserrat

**Fallback Stack:**
```css
font-family: 'Poppins', 'Inter', 'Segoe UI', -apple-system, sans-serif;
```

---

### Type Scale (extracted from ad reference)

| Level | Name | Weight | Size (px) | Line Height | Color | Usage |
|-------|------|--------|-----------|-------------|-------|-------|
| T1 | Hero Headline | ExtraBold (800) | 36–48px | 1.1 | Ink Black | "DON'T JUST GET VISITORS." |
| T2 | Hero Accent | ExtraBold (800) | 36–48px | 1.1 | Brand Green | "CONVERT THEM!" |
| T3 | Section Title | Bold (700) | 22–28px | 1.2 | White or Ink Black | Banner headlines |
| T4 | Subheadline | SemiBold (600) | 16–20px | 1.4 | Ink Black or Medium Grey | Descriptive subtext |
| T5 | Body Text | Regular (400) | 14–16px | 1.6 | Ink Black / Medium Grey | Paragraph copy |
| T6 | Label / Tag | SemiBold (600) | 12–13px | 1.3 | Medium Grey or White | Icon labels, captions |
| T7 | Stat Number | Bold (700) | 18–22px | 1.0 | Ink Black | Stats widget numbers |
| T8 | CTA Button | SemiBold (600) | 15–18px | 1.0 | White | Button text |
| T9 | Logo Text | Bold (700) | 18–22px | 1.0 | Ink Black | Brand name "ChatBot Nepal" |

### Typography Rules

1. **Never use more than 2 font weights in a single layout**
2. **Hero headline always all-caps** — "DON'T JUST GET VISITORS." not "Don't just get visitors."
3. **Accent line (T2) always Brand Green** — color emphasis is the visual anchor of the ad
4. **Button text is never bold** — SemiBold only, uppercase letters, tracked out slightly
5. **Minimum body text: 14px** — never smaller for accessibility on mobile
6. **Stats numbers use Bold 700** — must feel data-heavy and credible
7. **Line breaks must be intentional** — "Don't let them leave" and "unanswered." break mid-sentence for rhythm

---

## SPACING & LAYOUT SYSTEM

### Base Unit
**8px grid system** — all spacing is a multiple of 8

```
4px  — micro gap (icon to label)
8px  — tight spacing (within a component)
16px — standard inner padding
24px — component breathing room
32px — section spacing
48px — major section separation
64px — hero section padding
```

### Container Rules (from reference ad layout)

```
Max content width:      1200px
Mobile breakpoint:      768px
Content side padding:   48px desktop / 20px mobile
Section top/bottom:     64px desktop / 40px mobile
Card inner padding:     20px all sides
Icon card padding:      16px all sides
```

### Grid Structure (from reference ad)

**Two-Column Hero Layout:**
```
┌────────────────────────────────────────────────────┐
│ [Left col — 55%]          [Right col — 45%]        │
│                                                    │
│  Logo                     [Person + Stats Card]    │
│  Hero Headline                                     │
│  Subtext                                           │
│  4 Feature Icons                                   │
│                                                    │
└────────────────────────────────────────────────────┘
```

**Bottom CTA Banner:**
```
┌────────────────────────────────────────────────────┐
│ [Left — Dark Navy 50%]    [Right — Brand Green 50%]│
│  "AI CHATBOT THAT..."     "START NOW →"            │
│  "24/7 TO GROW..."        "Starting at Rs. 999"    │
└────────────────────────────────────────────────────┘
```

---

## COMPONENT LIBRARY

### 1. Primary CTA Button

Extracted directly from "GET STARTED TODAY!" button.

```
Background:     Brand Green #1DB954
Text:           White #FFFFFF
Font:           Poppins SemiBold 16px, uppercase
Padding:        14px 28px
Border radius:  50px (fully rounded / pill shape)
Icon:           Arrow → circle icon on right
Border:         None
Hover state:    Darken to #18A348 (8% darker)
Shadow:         0 4px 16px rgba(29, 185, 84, 0.35)
```

**Visual:**
```
[ GET STARTED TODAY!  → ]
```

---

### 2. Secondary CTA Button (Dark variant)

From top section "GET STARTED TODAY" on dark navy background:

```
Background:     Brand Green #1DB954
Text:           White #FFFFFF
Font:           Poppins Bold 16px
Padding:        14px 32px
Border radius:  6px (slightly rounded, not pill)
Arrow icon:     Contained circle → right
```

---

### 3. Stats Widget Card

Extracted from "TODAY'S STATS" floating card.

```
Background:     White #FFFFFF
Border:         1px solid #E2E8F0
Border radius:  12px
Shadow:         0 8px 32px rgba(0, 0, 0, 0.12)
Padding:        20px 24px
Header text:    "TODAY'S STATS" — Poppins SemiBold 11px, uppercase, letter-spacing: 0.08em, color: #64748B
```

**Row structure:**
```
[Icon]  [Label Text]         [Number]
 🧑     Visitors              128
 💬     Chats                  45
 📋     Leads Captured         23
 📈     Conversions             8
```

**Row specs:**
```
Icon:       20px, Brand Green or Signal Blue color
Label:      Poppins Regular 13px, Ink Black
Number:     Poppins Bold 14px, Ink Black
Row height: 36px
Divider:    None (spacing only, 8px gap between rows)
```

**Graph element at bottom:**
```
Line chart, Signal Blue #0EA5E9, upward trend
Height: 48px, no axis labels, purely visual/emotional
```

---

### 4. Feature Icon Cards

Extracted from the 4 icon blocks (Engage, Capture, Qualify, Increase).

```
Background:     Soft Grey #F1F5F9
Border:         1px solid #E2E8F0
Border radius:  12px
Padding:        16px
Icon container: 48px × 48px circle or rounded square, Brand Green fill or outline
Icon color:     Brand Green #1DB954
Title:          Poppins SemiBold 13px, Ink Black, line 1
Subtitle:       Poppins Regular 12px, Medium Grey, line 2
Max width:      Each card = 25% of container minus gaps
Gap:            12px between cards
```

**Card Layout:**
```
┌──────────┐
│  [Icon]  │
│  Title   │
│  Line 2  │
└──────────┘
```

---

### 5. Logo Component

Extracted from "ChatBot Nepal" logo.

```
Icon:       Rounded chat bubble with robot face, Signal Blue #2563EB
Icon size:  40px × 40px
Brand name: "ChatBot" — Poppins Bold, Ink Black
Sub-brand:  "NEPAL" — Poppins ExtraBold, Brand Green, all-caps, smaller (70% of main text)
Gap:        10px between icon and text
Alignment:  Horizontal (icon left, text right)
```

---

### 6. Trust Badge / Friction Reducer

Extracted from "No Setup Fee · Cancel Anytime" elements.

```
Icon:       Checkmark circle, Brand Green
Text:       Poppins Regular 13px, Medium Grey or White
Layout:     Icon left + text right, horizontal inline
Gap:        6px
Multiple:   Stack vertically with 8px gap, or inline with bullet separator (·)
```

---

### 7. Bottom CTA Banner (Split Block)

```
Full width, no margin
Height:     80–100px
Left half:  Deep Navy #0B1E3D background
  - Line 1: "AI CHATBOT THAT WORKS" — Poppins Bold 18px, White
  - Line 2: "24/7 TO GROW YOUR BUSINESS" — Poppins ExtraBold 20px, White
Right half: Brand Green #1DB954 background
  - Label:  "START NOW" — Poppins ExtraBold 22px, White
  - Sub:    "Starting at Rs. 999 / month" — Poppins Regular 13px, White 80% opacity
  - Arrow:  → circle icon, White
```

---

### 8. Section Header Bar (Dark Top Strip)

Extracted from the dark top strip with checkmarks.

```
Background:     Deep Navy #0B1E3D
Height:         48px
Padding:        0 48px
Content:        Trust badges inline (checkmark + text)
Font:           Poppins Regular 13px, White
Icon:           Checkmark circle, Brand Green
Gap between items: 32px
```

---

## PHOTOGRAPHY & IMAGERY RULES

### Human Subject Style
- **Emotion:** Celebration, success, relief, confidence — never neutral or anxious
- **Pose:** Active (fist pump, typing, looking at screen, pointing) — not static
- **Composition:** Subject placed on RIGHT side of frame, facing LEFT (toward copy text)
- **Background removal:** Subject always isolated on transparent or minimal background
- **Demographic:** South Asian or Nepali-presenting where possible — avoid generic Western stock
- **Props:** Laptop, phone, or tablet — contextualizes the digital product

### Screenshot / UI Mockup Rules
- Always shown in a device frame (laptop, phone, browser window)
- Dark overlay at 20% to ensure text readability if overlapping
- Brand Green glow border: `box-shadow: 0 0 0 2px #1DB954, 0 8px 32px rgba(29,185,84,0.2)`
- Stats, metrics, or dashboard visuals preferred over generic app screens

### Icon Style
- **Style:** Outline icons with Brand Green fill or stroke
- **Family:** Lucide, Phosphor, or Heroicons (never mix families)
- **Size:** 24px functional, 40–48px decorative
- **Background:** Soft Grey rounded container or Brand Green circle (for hero icons)

---

## AD LAYOUT TEMPLATES

### Template 1 — Hero Split Ad (Reference Ad Style)

**Use for:** Facebook/Instagram feed ads, website hero sections
**Ratio:** 16:9 (landscape) or 1:1 (square)

```
┌────────────────────────────────────────────┐
│ [Dark strip: checkmarks + trust badges]    │ ← 48px
├─────────────────────┬──────────────────────┤
│                     │                      │
│  Logo               │   [Person photo]     │
│                     │                      │
│  HEADLINE LINE 1    │   [Stats Card]       │
│  HEADLINE LINE 2    │                      │
│  (Brand Green)      │                      │
│                     │                      │
│  Subtext body       │                      │
│                     │                      │
│  [Icon][Icon][Icon][Icon]                  │
│                     │                      │
├─────────────────────┴──────────────────────┤
│ [Dark Navy: Brand message] [Green: CTA]    │ ← 80px
└────────────────────────────────────────────┘
```

---

### Template 2 — Feature Focus Ad

**Use for:** Carousel ads, feature announcement posts
**Ratio:** 1:1 or 4:5

```
┌────────────────────────────────────────────┐
│  [Large icon — centered, 80px]             │
│                                            │
│  FEATURE NAME                              │
│  Short description of what this does       │
│  and why it matters to you.                │
│                                            │
│  [Subtle divider]                          │
│                                            │
│  ✅ Benefit one                            │
│  ✅ Benefit two                            │
│  ✅ Benefit three                          │
│                                            │
│  [ TRY IT FREE → ]   [Logo bottom right]  │
└────────────────────────────────────────────┘
Background: White. Accent: Brand Green.
```

---

### Template 3 — Stats / Proof Ad

**Use for:** Retargeting ads, trust-building posts
**Ratio:** 1:1

```
┌────────────────────────────────────────────┐
│  "BUSINESSES USING                         │
│   CHATBOT NEPAL SEE:"                      │
│                                            │
│  ┌──────────┐  ┌──────────┐               │
│  │  3.2×    │  │  68%     │               │
│  │ More     │  │ Lower    │               │
│  │ Leads    │  │ Bounce   │               │
│  └──────────┘  └──────────┘               │
│                                            │
│  ┌──────────┐  ┌──────────┐               │
│  │  24/7    │  │  Rs.999  │               │
│  │ Response │  │ /month   │               │
│  └──────────┘  └──────────┘               │
│                                            │
│  [GET STARTED TODAY →]                     │
└────────────────────────────────────────────┘
Background: White. Cards: Soft Grey. Numbers: Brand Green.
```

---

### Template 4 — Dark Authority Ad

**Use for:** Awareness posts, retargeting, video thumbnails
**Ratio:** 16:9 or 9:16 (Reels/Stories)

```
┌────────────────────────────────────────────┐
│ Background: Deep Navy #0B1E3D              │
│                                            │
│  [Logo — white version]                    │
│                                            │
│  IS YOUR WEBSITE                           │
│  LOSING LEADS                              │
│  EVERY NIGHT?                              │
│                                            │
│  Subtext in white 70% opacity              │
│                                            │
│  [ START FREE TRIAL → ]   Brand Green btn  │
│                                            │
│  No Setup Fee · Cancel Anytime             │
└────────────────────────────────────────────┘
```

---

## SHADOW & ELEVATION SYSTEM

| Level | Value | Use |
|-------|-------|-----|
| Level 0 | None | Flat elements, buttons |
| Level 1 | `0 1px 3px rgba(0,0,0,0.08)` | Subtle card lift |
| Level 2 | `0 4px 16px rgba(0,0,0,0.10)` | Standard card (feature icons) |
| Level 3 | `0 8px 32px rgba(0,0,0,0.12)` | Stats widget, floating cards |
| Level 4 | `0 16px 48px rgba(0,0,0,0.18)` | Modals, overlays, hero elements |
| Green Glow | `0 4px 20px rgba(29,185,84,0.30)` | CTA buttons, active states |

---

## BORDER RADIUS SYSTEM

| Element | Radius | Notes |
|---------|--------|-------|
| Pills (CTA buttons) | `50px` | Fully rounded — primary action buttons |
| Cards (stats, feature) | `12px` | Standard card corner |
| Icon containers | `10px` or `50%` circle | Rounded square or full circle |
| Input fields | `8px` | Subtle rounding |
| Section blocks | `0px` | Full-width strips have no radius |
| Image frames | `12px` | Photo containers |

---

## MOTION & ANIMATION SYSTEM

### Timing Functions
```css
--ease-out:  cubic-bezier(0.0, 0.0, 0.2, 1)  /* Elements entering */
--ease-in:   cubic-bezier(0.4, 0.0, 1, 1)     /* Elements leaving */
--standard:  cubic-bezier(0.4, 0.0, 0.2, 1)   /* General movement */
```

### Duration Scale
```
Micro:    100ms  — hover state color changes, icon flips
Short:    200ms  — button hover, tooltip appear
Medium:   300ms  — card fade-in, dropdown open
Long:     500ms  — page section transitions, modals
```

### Standard Animations for Ads/Reels

**Text entrance (hero headline):**
```
Transform: translateY(16px) → translateY(0)
Opacity: 0 → 1
Duration: 400ms, ease-out
Stagger: 100ms between each line
```

**Stats counter animation:**
```
Numbers count up from 0 to final value
Duration: 1200ms, ease-out
Start: When element enters viewport
```

**CTA button pulse:**
```
Box-shadow oscillates between:
  0 4px 12px rgba(29,185,84,0.3)
  0 4px 28px rgba(29,185,84,0.55)
Duration: 2000ms, ease-in-out, infinite
```

**Card hover lift:**
```
Transform: translateY(-4px)
Shadow: Level 2 → Level 3
Duration: 200ms, ease-out
```

---

## REEL / VIDEO STYLE GUIDE

### Color Application in Video
- Open on Deep Navy background for first 0.5 seconds (brand establishment)
- Brand Green appears on the key benefit word or stat reveal
- White text on all dark backgrounds
- Ink Black text on all light backgrounds

### Text Overlay Style (Reels)
```
Font:           Poppins Bold
Size:           32–44px (mobile-optimized)
Color:          White with Brand Green for emphasis words
Background:     Semi-transparent Dark Navy pill behind text (60% opacity)
Border radius:  50px on text background pill
Position:       Center-frame or lower-third
```

### Subtitle Bar Style
```
Background:     rgba(11, 30, 61, 0.75) — Deep Navy 75%
Text:           White, Poppins Regular 18px
Padding:        8px 16px
Border radius:  4px
Position:       Bottom 20% of frame
```

### Transition Style
- Hard cuts for fast-paced tool demos
- 200ms cross-dissolve for topic changes
- Zoom cut (1.05× scale) on key stat reveals
- Never: wipes, spins, or slides

---

## BRAND VOICE × DESIGN ALIGNMENT

| Tone | Visual Expression |
|------|-------------------|
| Confident | Bold headline in full caps |
| Trustworthy | White backgrounds, stats card, checkmarks |
| Energetic | Brand Green CTAs, upward chart lines |
| Affordable | Rs. 999 prominently displayed, "No Setup Fee" |
| Local | Nepali context, local photography, Rs. currency |
| Modern | Clean layout, rounded corners, no skeuomorphics |

---

## COMPETITOR BENCHMARKING

### vs. Tidio (Global SaaS Chatbot)
- Tidio uses purple/violet as primary — ChatBot Nepal's green is warmer and more approachable
- Tidio heavy on animation — ChatBot Nepal's cleaner static layouts work better for Facebook feed
- **Keep:** ChatBot Nepal's local pricing signal (Rs. 999) — Tidio can't compete here

### vs. Zendesk (Enterprise)
- Zendesk = cold, corporate, enterprise — ChatBot Nepal = warm, accessible, SME
- **Keep:** The split CTA banner — Zendesk lacks this urgency
- **Adapt:** Tidier stats presentation (Zendesk's data cards are cleaner)

### vs. ManyChat (Facebook-native tool)
- ManyChat is very playful, emoji-heavy — ChatBot Nepal is more professional
- **Keep:** ChatBot Nepal's professional tone for B2B credibility
- **Adapt:** ManyChat's community-proof approach ("10,000+ businesses use this")

### The Gap ChatBot Nepal Should Own
> No local Nepali chatbot brand has a consistent, professional, conversion-focused visual identity. Most are either over-designed (cluttered) or under-designed (plain). ChatBot Nepal's ad already outperforms local competitors — systematizing it creates an insurmountable visual moat.

---

## ASSET PRODUCTION CHECKLIST

For every new ad or post, verify:

- [ ] Background color is from the approved palette (White, Deep Navy, or Soft Grey)
- [ ] Primary CTA button is Brand Green `#1DB954` — solid fill, pill shape
- [ ] Headline uses Poppins ExtraBold, all-caps, Ink Black or White
- [ ] Accent word/line is Brand Green
- [ ] Logo is present and legible
- [ ] Trust reducers are present ("No Setup Fee · Cancel Anytime")
- [ ] Price is visible if ad is conversion-focused
- [ ] Human face or active pose photography used where possible
- [ ] Stats or social proof element present
- [ ] Single, clear CTA — not two competing calls to action
- [ ] Watermark or brand mark in bottom corner
- [ ] Text does not go below bottom 15% of frame (UI overlap zone)
- [ ] Minimum contrast ratio 4.5:1 on all text

---

## QUICK REFERENCE CARD

```
╔══════════════════════════════════════════════════╗
║           CHATBOT NEPAL — DESIGN TOKENS           ║
╠══════════════════════════════════════════════════╣
║  PRIMARY GREEN     #1DB954   rgb(29, 185, 84)    ║
║  DEEP NAVY         #0B1E3D   rgb(11, 30, 61)     ║
║  INK BLACK         #0F172A   rgb(15, 23, 42)     ║
║  PURE WHITE        #FFFFFF   rgb(255, 255, 255)  ║
║  SOFT GREY         #F1F5F9   rgb(241, 245, 249)  ║
║  MEDIUM GREY       #64748B   rgb(100, 116, 139)  ║
╠══════════════════════════════════════════════════╣
║  FONT              Poppins (Google Fonts — free) ║
║  H1 WEIGHT         ExtraBold 800                 ║
║  BODY WEIGHT       Regular 400                   ║
║  CTA WEIGHT        SemiBold 600                  ║
╠══════════════════════════════════════════════════╣
║  BASE UNIT         8px grid                      ║
║  CARD RADIUS       12px                          ║
║  BUTTON RADIUS     50px (pill)                   ║
║  CTA SHADOW        0 4px 20px rgba(29,185,84,.3) ║
╚══════════════════════════════════════════════════╝
```

---

*Design system extracted from: ad5_convert_visitors.png*
*ChatBot Nepal | May 2026 | Version 1.0*
