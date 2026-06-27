# Babybib Design System Guidelines

This document specifies the official visual guidelines, color system, and layout tokens for **Babybib**, a professional APA 7th Edition Bibliography Generator.

## 1. Visual Theme & Atmosphere

Babybib features a premium, clean, and highly trustworthy academic/research identity. The interface is built on clean surfaces (light/dark theme adaptive) highlighted by vibrant violet and pink-magenta gradients that denote creativity, automation, and academic excellence.

**Key Characteristics:**
- **Babybib Violet (`#8B5CF6`)** as the primary commanding brand color.
- **Dynamic Gradient System:**
  - *Primary Gradient:* `linear-gradient(135deg, #6366F1 0%, #8B5CF6 45%, #D946EF 100%)` (Indigo to Violet to Fuchsia)
  - *Brand Gradient:* `linear-gradient(to right, #180d36, #8B5CF6, #CF23CF, #180d36)` (Deep Indigo/Purple to Magenta)
 - **Comfortaa** is the designated brand display font (Logo / BB badges), while **Tahoma** (fallback for Thai rendering) and **Inter / SF Pro Display** serve as the UI workhorse fonts.
- Adaptive corner styling: Rounded card corners (8px to 16px radius) for a soft, friendly, yet highly modern dashboard feel.
- Subtle premium shadows (`rgba(139, 92, 246, 0.15)` for highlighted items, `0 4px 6px -1px rgba(0, 0, 0, 0.1)` for standard widgets).

---

## 2. Color Palette & Roles

### Primary Colors
- **Babybib Violet (`#8B5CF6`):** Primary brand accent, text highlights, links.
- **Violet Dark (`#7C3AED`):** Primary button hover state, borders.
- **Violet Light (`#EDE9FE`):** Light backgrounds, subtle badge fills.
- **Magenta Accent (`#D946EF`):** Secondary brand accent, highlights.

### Neutrals (Light Mode)
- **White (`#FFFFFF`):** Base background surfaces, card containers.
- **Light Gray (`#F9FAFB` / `#F3F4F6`):** Section backgrounds, table headings, inputs.
- **Text Primary (`#000000` / `#111827`):** Primary headings and body copy.
- **Text Secondary (`#4B5563`):** Subtitles, meta-data, descriptions.
- **Border Light (`#E5E7EB`):** Standard thin dividing borders.

### Neutrals (Dark Mode)
- **Dark Deep (`#0F0F0F`):** Main viewport background.
- **Dark Surface (`#1A1A1A` / `#262626`):** Card panels, dropdown list backgrounds, input backgrounds.
- **Text Primary (`#F8FAFC`):** Bright text headings, editable labels.
- **Text Secondary (`#CBD5E1` / `#94A3B8`):** Muted info text, descriptions.
- **Border Dark (`rgba(255, 255, 255, 0.08)`):** Dividers, custom card boundaries.

### Semantic
- **Success/Emerald (`#10B981`):** Correct formats, completed statuses.
- **Warning/Amber (`#F59E0B`):** Manual edits needed, missing fields.
- **Danger/Red (`#EF4444`):** Missing values, delete warnings.

---

## 3. Typography Rules

### Font Families
- **Logo / Brand Badge:** `Comfortaa`, sans-serif
- **Thai UI / Body:** `Tahoma`, sans-serif
- **English UI / Body:** `-apple-system`, `BlinkMacSystemFont`, `'SF Pro Display'`, `'Segoe UI'`, `Roboto`, `sans-serif`

### Hierarchy

| Role | Font | Size | Weight | Line Height |
|------|------|------|--------|-------------|
| Hero Title | Tahoma / Inter | 40px - 48px | 800 | 1.1 |
| Section Heading | Tahoma / Inter | 28px - 32px | 700 | 1.3 |
| Sub-heading / Card Title | Tahoma / Inter | 20px - 24px | 600 | 1.3 |
| Body / Form Label | Tahoma / Inter | 16px (1rem) | 500 | 1.6 |
| Caption / Help Text | Tahoma / Inter | 12px - 14px | 400 | 1.4 |

---

## 4. Component Stylings

### Buttons (`.btn`)
- **Primary Violet:** Background using primary gradient (`#6366F1` to `#D946EF`), white text, `var(--shadow-primary)` shadow.
- **Secondary Outlined:** White/transparent bg, `#8B5CF6` text, `1px solid #E5E7EB` border.
- **Danger Red:** Background `#EF4444` with white text.

### Cards (`.card`, `.form-card-new`)
- **Light Mode:** Background `#FFFFFF`, rounded corners `16px`, shadow `0 4px 20px rgba(139, 92, 246, 0.08)`.
- **Dark Mode:** Background `#1A1A1F`, border `1px solid rgba(255, 255, 255, 0.03)`, shadow `0 4px 25px rgba(0, 0, 0, 0.3)`.

---

## 5. Spacing & Radius Tokens

### Spacing Fills
- Small gaps: `4px`, `8px`, `12px`
- Section margins: `16px` (`1rem`), `20px`, `24px`, `32px`

### Border Radius
- `.radius-sm`: `4px`
- `.radius` / `.radius-md`: `8px` - `12px`
- `.radius-lg`: `16px` (Default for cards/modals)
- `.radius-full`: `9999px` (Pills/avatars)

---

## 6. Do's and Don'ts

### Do
- Use Comfortaa font *only* for the logo brand name and brand headings.
- Maintain the primary purple color (`#8B5CF6`) for interactive triggers.
- Keep border radius around 12px–16px for card components to preserve the visual identity.

### Don't
- Do not use hardcoded plain colors (such as solid saturated blue or green) outside the Tailwind/DaisyUI semantic tokens.
- Do not use pure white backgrounds for cards in dark mode. Use `#1A1A1F` or `#262626`.
