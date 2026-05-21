---
name: Kinetic Enterprise
colors:
  surface: '#0b1326'
  surface-dim: '#0b1326'
  surface-bright: '#31394d'
  surface-container-lowest: '#060e20'
  surface-container-low: '#131b2e'
  surface-container: '#171f33'
  surface-container-high: '#222a3d'
  surface-container-highest: '#2d3449'
  on-surface: '#dae2fd'
  on-surface-variant: '#bec8d2'
  inverse-surface: '#dae2fd'
  inverse-on-surface: '#283044'
  outline: '#88929b'
  outline-variant: '#3e4850'
  surface-tint: '#89ceff'
  primary: '#89ceff'
  on-primary: '#00344d'
  primary-container: '#0ea5e9'
  on-primary-container: '#003751'
  inverse-primary: '#006591'
  secondary: '#b7c8e1'
  on-secondary: '#213145'
  secondary-container: '#3a4a5f'
  on-secondary-container: '#a9bad3'
  tertiary: '#ffb86e'
  on-tertiary: '#492900'
  tertiary-container: '#de8712'
  on-tertiary-container: '#4d2b00'
  error: '#ffb4ab'
  on-error: '#690005'
  error-container: '#93000a'
  on-error-container: '#ffdad6'
  primary-fixed: '#c9e6ff'
  primary-fixed-dim: '#89ceff'
  on-primary-fixed: '#001e2f'
  on-primary-fixed-variant: '#004c6e'
  secondary-fixed: '#d3e4fe'
  secondary-fixed-dim: '#b7c8e1'
  on-secondary-fixed: '#0b1c30'
  on-secondary-fixed-variant: '#38485d'
  tertiary-fixed: '#ffdcbd'
  tertiary-fixed-dim: '#ffb86e'
  on-tertiary-fixed: '#2c1600'
  on-tertiary-fixed-variant: '#693c00'
  background: '#0b1326'
  on-background: '#dae2fd'
  surface-variant: '#2d3449'
typography:
  display-lg:
    fontFamily: Inter
    fontSize: 48px
    fontWeight: '700'
    lineHeight: 56px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Inter
    fontSize: 32px
    fontWeight: '600'
    lineHeight: 40px
    letterSpacing: -0.01em
  headline-lg-mobile:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  headline-md:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  title-lg:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '600'
    lineHeight: 28px
  body-lg:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  label-md:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '500'
    lineHeight: 16px
    letterSpacing: 0.01em
  mono-sm:
    fontFamily: JetBrains Mono
    fontSize: 12px
    fontWeight: '400'
    lineHeight: 16px
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  unit: 4px
  xs: 4px
  sm: 8px
  md: 16px
  lg: 24px
  xl: 32px
  2xl: 48px
  gutter: 24px
  margin-mobile: 16px
  margin-desktop: 32px
  sidebar-width: 260px
---

## Brand & Style

The design system is engineered for high-performance internal operations. It targets HR administrators and team leads who require a focused, low-fatigue environment for managing complex organizational data.

The aesthetic follows a **Modern Corporate** movement with **Minimalist** tendencies. It prioritizes clarity and efficiency, utilizing deep charcoal surfaces to reduce eye strain during long work sessions. The emotional response should be one of "calm authority"—the UI feels responsive, precise, and professional. We avoid unnecessary decorative elements, instead using subtle borders and intentional whitespace to organize information.

## Colors

The palette is built on a foundation of deep, cool charcoals. 
- **Primary:** An "Electric Cyan" used exclusively for high-priority actions and active states.
- **Surface Strategy:** We use a tiered dark mode. The `canvas` is the darkest layer, `surface` is used for the primary content areas, and `container` for cards or elevated elements.
- **Accents:** Semantic colors are used with high saturation but limited surface area (dots, thin borders, or subtle text) to ensure they stand out against the dark background without causing visual vibration.

## Typography

The typography system utilizes **Inter** for its exceptional legibility in data-dense interfaces. 
- **Hierarchy:** We use a tight scale to keep as much information "above the fold" as possible. 
- **Data Display:** For ID numbers or technical metrics, an optional Monospace font (JetBrains Mono) is introduced to ensure character alignment in tables.
- **Contrast:** High-emphasis text uses White (`#FFFFFF`), while secondary body text uses a muted Slate (`#94A3B8`) to create a clear visual path.

## Layout & Spacing

The design system employs a **Fluid-Fixed Hybrid** grid. 
- **Sidebar:** A fixed-width navigation rail (260px) persists on the left for desktop.
- **Main Content:** A 12-column fluid grid for the main stage. 
- **Rhythm:** An 8px linear scale (with a 4px sub-step) governs all padding and margins. 
- **Responsive Behavior:** On mobile, the sidebar collapses into a bottom-sheet or "hamburger" drawer, and margins shrink to 16px. Data tables on mobile should transition into a "card-list" format to preserve readability.

## Elevation & Depth

In this dark-themed system, we avoid traditional drop shadows which can appear "dirty" on dark backgrounds. Instead, we use **Tonal Layering** and **Low-Contrast Outlines**.

- **Level 0 (Canvas):** The base background.
- **Level 1 (Surface):** Default container level. Uses a subtle 1px border (`#1E293B`) to define edges.
- **Level 2 (Elevated):** For modals or dropdowns. These use a slightly lighter background color and a very soft, high-diffusion shadow with a 20% opacity black tint to create a "lifted" effect.
- **Interactive States:** Hovering over a card or list item increases the border brightness rather than adding depth, maintaining a flat, modern feel.

## Shapes

We use a **Soft (0.25rem)** rounding strategy. This provides a professional, "tooled" appearance that feels modern without being overly playful or bubbly. 

- Small components (Checkboxes, Tags) use 4px (`rounded-sm`).
- Standard components (Buttons, Inputs) use 6px (`rounded-md`).
- Large containers (Cards, Modals) use 8px (`rounded-lg`).

## Components

### Buttons
- **Primary:** Solid Electric Blue with white text. High contrast.
- **Secondary:** Transparent background with a thin slate border.
- **Ghost:** No border or background, used for low-priority actions in headers.

### Data Tables
- **Header:** Darker than the row background, using `label-md` uppercase text.
- **Rows:** Alternating subtle zebra striping or 1px bottom borders. Hover state highlights the entire row in a dark indigo tint.
- **Cells:** Vertical alignment is centered; text-overflow uses ellipses.

### Form Inputs
- **Base:** Dark background (`#0F172A`) with a subtle border.
- **Focus State:** Border changes to the Primary Electric Blue with a subtle outer glow.
- **Validation:** Error states use a red border and a small icon for accessibility.

### Sidebar & Topbar
- **Sidebar:** Semi-collapsed or full-width options. Active links use a left-edge "accent bar" in the primary color.
- **Topbar:** Glassmorphic effect (backdrop-blur: 12px) with 80% opacity to feel lightweight. Contains search and a notification bell with a red "pulse" badge for alerts.

### Stat Cards
- Simple containers with a large `headline-md` value and a small `label-md` trend indicator (e.g., +12% in green).