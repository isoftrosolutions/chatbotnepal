# Design System Document: ChatBot Nepal B2B SaaS

## 1. Overview & Creative North Star: "The Intelligent Monolith"
In the saturated market of B2B SaaS, "standard" is the enemy of premium. This design system moves away from the generic "boxes-on-gray" layout to embrace **The Intelligent Monolith**. 

Our Creative North Star focuses on **authoritative clarity**. We treat the dashboard not as a collection of widgets, but as a singular, cohesive digital architecture. We use intentional asymmetry—pairing heavy, dark sidebar masses against airy, breathable content canvases—to create a sense of scale and institutional trust. By replacing structural lines with tonal layering, we achieve a "high-end editorial" feel that suggests the AI is sophisticated, calm, and precise.

---

## 2. Colors & Surface Philosophy

### The Tonal Palette
Our palette utilizes Material-inspired layering to define hierarchy without visual clutter.

*   **Core Brand:** `primary` (#3525cd) acts as the high-contrast anchor, while `primary_container` (#4f46e5) provides the vibrant Indigo energy expected of a modern SaaS.
*   **Success & Growth:** `secondary` (#006c49) and `secondary_container` (#6cf8bb) are used sparingly for positive data trends and "Active" bot statuses.
*   **The Sidebar (Dark Indigo):** The `inverse_surface` (#2e3132) and deep indigos create a heavy anchor on the left, grounding the user's navigation.

### The "No-Line" Rule
**Explicit Instruction:** Designers are prohibited from using 1px solid borders for sectioning. We do not "box" our content. 
*   **Boundary Definition:** Use background color shifts. A section should be defined by moving from `surface` (#f8f9fa) to `surface_container_low` (#f3f4f5).
*   **Nesting:** Treat the UI as stacked sheets. Place a `surface_container_lowest` (pure white) card on top of a `surface_container` background to create a natural, "physical" lift.

### The "Glass & Gradient" Rule
To elevate the interface beyond a flat template, use Glassmorphism for floating elements (like Toast notifications or Command Palettes). 
*   **Implementation:** Use `surface_container_low` at 80% opacity with a `backdrop-filter: blur(12px)`.
*   **Signature Textures:** Main CTAs should utilize a subtle linear gradient: `primary_container` to `primary`. This adds a "lithic" depth that feels tactile and premium.

---

## 3. Typography: Editorial Authority
We use **Inter** not just for readability, but as a tool for branding. 

*   **Display & Headlines:** Use `display-md` (2.75rem) with a negative letter-spacing of `-0.02em`. This "tight" look mimics high-end tech journals and provides an immediate sense of "Modernity."
*   **Titles:** `title-lg` (1.375rem) should be semi-bold (600) to act as clear anchors for data sections.
*   **Body:** `body-md` (0.875rem) is our workhorse. Ensure a line-height of 1.5 to maintain "breathing room" in dense AI chat logs.
*   **Labels:** `label-md` (0.75rem) should be used in All-Caps with a `+0.05em` letter spacing for metadata and table headers to distinguish them from actionable content.

---

## 4. Elevation & Depth: Tonal Layering

### The Layering Principle
Forget shadows as a default. Depth is achieved by "stacking" the surface-container tiers:
1.  **Base Layer:** `surface` (Main canvas background).
2.  **Sectional Layer:** `surface_container_low` (Sidebar or grouped content areas).
3.  **Interactive Layer:** `surface_container_lowest` (Cards, Inputs, Actionable items).

### Ambient Shadows
When an element must "float" (Modals/Dropdowns), use **Ambient Shadows**:
*   **Spec:** `box-shadow: 0 10px 30px -5px rgba(25, 28, 29, 0.05);`
*   **Tone:** The shadow must never be pure black; it should be a tinted version of `on_surface` to mimic natural light scattering.

### The "Ghost Border" Fallback
If accessibility requires a container boundary, use a **Ghost Border**:
*   **Spec:** 1px solid `outline_variant` (#c7c4d8) at **20% opacity**. It should be felt, not seen.

---

## 5. Components

### Buttons & Interaction
*   **Primary:** Indigo gradient (`primary_container` to `primary`) with `rounded-md` (6px). 
*   **States:** On hover, use a `surface_tint` overlay. Use a 2px `primary_fixed` focus ring with a 2px offset for accessibility.
*   **Tertiary:** No background, no border. Use `primary` text weight 600.

### The "Intelligent" Table
*   **Styling:** Zebra-striping using `surface` and `surface_container_low`. 
*   **Constraint:** Forbid vertical divider lines. Use horizontal white space (24px padding) to separate data columns. 
*   **Headers:** Use `label-md` in `on_surface_variant` (#464555).

### AI-Specific Components
*   **Chat Bubbles:** User messages in `primary_container`; Bot responses in `surface_container_high` (#e7e8e9) to signify "System" origin.
*   **Confidence Badges:** Tiny chips using `tertiary_fixed` (#ffddb8) with `on_tertiary_fixed` text to show AI confidence scores without competing with primary actions.
*   **Status Orbs:** Pulsing 8px circles using `secondary` for "Live" bots, replacing text-heavy status labels.

---

## 6. Do’s and Don’ts

### Do:
*   **Do** use asymmetrical padding. Give the top of the dashboard more "sky" (64px+) than the sides to create an editorial feel.
*   **Do** use `Lucide` icons at 1.5px stroke weight for a refined, light-weight appearance.
*   **Do** utilize `surface_bright` for empty state illustrations to keep the UI feeling optimistic.

### Don’t:
*   **Don't** use 100% opaque borders. It creates "visual noise" that fatigues the user.
*   **Don't** use standard "Drop Shadows" (high opacity, small blur). It makes the software look like an early 2010s template.
*   **Don't** use `danger` (#ba1a1a) for anything other than destructive actions. Use `tertiary` (Amber) for warnings to maintain the "Calm AI" aesthetic.