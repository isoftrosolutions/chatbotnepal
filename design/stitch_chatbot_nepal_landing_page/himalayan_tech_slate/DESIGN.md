```markdown
# Design System Strategy: ChatBot Nepal

## 1. Overview & Creative North Star
**Creative North Star: "The Digital Himalayan Silk"**
This design system moves beyond the generic, boxy SaaS layout to embrace a philosophy of "Fluid Precision." Much like the convergence of ancient craftsmanship and modern innovation in Nepal, the UI should feel tactile, high-end, and deeply intentional. We avoid the "template" look by utilizing generous negative space, asymmetric editorial compositions, and a "layered paper" depth model.

The goal is to position ChatBot Nepal not as a "widget," but as a sophisticated business partner. We achieve this through:
*   **Intentional Asymmetry:** Breaking the 12-column grid with overlapping elements (e.g., a chat bubble overlapping a browser mockup).
*   **Tonal Authority:** Replacing harsh lines with soft tonal shifts to guide the eye.
*   **Editorial Scale:** Using extreme contrast in typography to create a sense of importance and clarity.

## 2. Color Theory & Tonal Depth
We utilize a Material-inspired palette but apply it with a "High-End Editorial" lens.

### The "No-Line" Rule
**Explicit Instruction:** You are prohibited from using 1px solid borders to define sections or containers. Structural boundaries must be created through:
*   **Background Shifts:** Using `surface-container-low` for the main section and `surface-container-lowest` for cards.
*   **Tonal Transitions:** A subtle shift from `background` (#f8f9fa) to `surface-container` (#edeeef) provides all the separation a modern eye needs.

### Surface Hierarchy & Nesting
Treat the UI as a physical stack of layers. 
*   **Base:** `surface` (#f8f9fa).
*   **Secondary Content Areas:** `surface-container-low` (#f3f4f5).
*   **Elevated Components (Cards/Modals):** `surface-container-lowest` (#ffffff).
*   **Instruction:** Never place a high-tier surface directly on a low-tier surface without a transition or significant padding.

### The "Glass & Gradient" Rule
To inject "soul" into the professional teal:
*   **Glassmorphism:** For floating chat bubbles or sticky headers, use `surface-container-lowest` at 80% opacity with a `backdrop-filter: blur(12px)`.
*   **Signature Gradients:** For primary CTAs and Hero backgrounds, blend `primary` (#00535b) into `primary_container` (#006d77). This creates a "glow" that flat colors cannot replicate.

## 3. Typography
We use a dual-font system to balance "Modern Innovation" with "Approachable Utility."

*   **Display & Headlines (Plus Jakarta Sans):** These are our "Voice." Use `display-lg` for hero sections to convey authority. The tight letter-spacing and high x-height of Plus Jakarta Sans feel premium and custom.
*   **Body & Labels (Inter):** This is our "Engine." Inter is used for its mathematical legibility. 
*   **Hierarchy Note:** Maintain a 1.6x line-height for body text to ensure the "approachable" personality isn't lost in dense information. Use `on_surface_variant` (#3e494a) for secondary body text to reduce visual noise.

## 4. Elevation & Depth
Forget traditional drop shadows. We use **Tonal Layering** to create a "Natural Lift."

*   **The Layering Principle:** Place a card using `surface-container-lowest` on top of a `surface-container-low` background. This creates a 3D effect without a single pixel of shadow.
*   **Ambient Shadows:** For floating elements (like chat previews), use:
    *   `box-shadow: 0 20px 40px rgba(0, 109, 119, 0.05);` (A tinted shadow using the primary teal color at very low opacity).
*   **The "Ghost Border" Fallback:** If a container absolutely requires a border (e.g., input fields), use `outline_variant` at 20% opacity. 

## 5. Components

### Buttons
*   **Primary:** Background: `primary_container` (#006d77) with a subtle vertical gradient to `primary`. Text: `on_primary`. Shape: `md` (0.75rem).
*   **Secondary (CTA):** Background: `secondary` (#8e4e14 / Warm Orange). Use this sparingly for "Start Trial" or "Book Demo" to provide high-contrast tension against the teal.
*   **Tertiary:** No background. `primary` text. Hover state uses `surface-container-high` as a soft background highlight.

### Cards & Lists
*   **Rule:** Forbid divider lines. 
*   **Execution:** Separate list items using 16px of vertical white space or a very subtle background color change on hover (`surface-container-highest`). 
*   **Cards:** Use `surface-container-lowest` with an `xl` (1.5rem) corner radius for a friendly, modern feel.

### Chat Bubbles (Custom Component)
*   **User Side:** `primary_container` background, `on_primary` text, bottom-right corner radius set to `none`.
*   **Bot Side:** `surface-container-high` background, `on_surface` text, bottom-left corner radius set to `none`.

### Input Fields
*   **Style:** Minimalist. No heavy borders. Use `surface-container-high` as the fill color. On focus, transition to a `ghost border` using the `primary` color.

## 6. Do's and Don'ts

| Do | Don't |
| :--- | :--- |
| **Do:** Use `display-lg` typography for bold, editorial-style statements. | **Don't:** Use generic 1px grey borders to separate sections. |
| **Do:** Use "Nepali Business Context" through subtle iconography (e.g., local currency symbols, Dhaka fabric patterns as subtle background masks). | **Don't:** Use generic stock photos of blue glowing robots or "matrix" backgrounds. |
| **Do:** Use asymmetric overlapping of browser mockups and chat bubbles. | **Don't:** Force every element into a perfectly aligned, flat grid. |
| **Do:** Use white space as a structural element to convey "Trust" and "Room to Breathe." | **Don't:** Overcrowd the layout with "Features" lists; prioritize results and outcomes. |
| **Do:** Use 4-8% opacity tinted shadows to mimic natural light. | **Don't:** Use high-contrast, black-to-transparent drop shadows. |

## 7. Spacing & Rhythm
This system relies on a **4px Base Grid**. 
*   Use `64px` or `80px` for section padding to maintain the "Editorial" feel.
*   Inside components, use `16px` or `24px` for consistent breathing room.
*   **Pro-Tip:** If a section feels "crowded," do not decrease font size—increase the surrounding white space. High-end SaaS is defined by the space it *doesn't* fill.```