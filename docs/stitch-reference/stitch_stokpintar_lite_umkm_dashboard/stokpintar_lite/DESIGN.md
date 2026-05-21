---
name: StokPintar Lite
colors:
  surface: '#f9faf7'
  surface-dim: '#d9dad8'
  surface-bright: '#f9faf7'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f3f4f1'
  surface-container: '#edeeeb'
  surface-container-high: '#e7e8e6'
  surface-container-highest: '#e2e3e0'
  on-surface: '#1a1c1b'
  on-surface-variant: '#404944'
  inverse-surface: '#2e312f'
  inverse-on-surface: '#f0f1ee'
  outline: '#717974'
  outline-variant: '#c0c8c3'
  surface-tint: '#396756'
  primary: '#001e15'
  on-primary: '#ffffff'
  primary-container: '#003527'
  on-primary-container: '#709f8c'
  inverse-primary: '#a0d1bc'
  secondary: '#2b6954'
  on-secondary: '#ffffff'
  secondary-container: '#adedd3'
  on-secondary-container: '#306d58'
  tertiary: '#310d0c'
  on-tertiary: '#ffffff'
  tertiary-container: '#4b211f'
  on-tertiary-container: '#c28581'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#bcedd8'
  primary-fixed-dim: '#a0d1bc'
  on-primary-fixed: '#002117'
  on-primary-fixed-variant: '#204f3f'
  secondary-fixed: '#b0f0d6'
  secondary-fixed-dim: '#95d3ba'
  on-secondary-fixed: '#002117'
  on-secondary-fixed-variant: '#0b513d'
  tertiary-fixed: '#ffdad7'
  tertiary-fixed-dim: '#f9b6b1'
  on-tertiary-fixed: '#340f0e'
  on-tertiary-fixed-variant: '#693937'
  background: '#f9faf7'
  on-background: '#1a1c1b'
  surface-variant: '#e2e3e0'
typography:
  headline-xl:
    fontFamily: Inter
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 40px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
    letterSpacing: -0.01em
  body-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  label-sm:
    fontFamily: Inter
    fontSize: 13px
    fontWeight: '600'
    lineHeight: 16px
    letterSpacing: 0.05em
  data-tabular:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '500'
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  sidebar_width: 280px
  container_max_width: 1440px
  gutter: 1.5rem
  margin_desktop: 2rem
  margin_mobile: 1rem
  component_gap: 1rem
---

## Brand & Style

The design system is built on the principles of **Efficiency, Trust, and Modernity**. Tailored for Indonesian MSMEs (UMKM), it bridges the gap between sophisticated enterprise tools and accessible local commerce. The visual direction follows a **Corporate / Modern** aesthetic, prioritizing clarity and speed over decorative elements. 

The UI evokes a sense of organized stability through the use of deep botanical greens and structural blues. High-density layouts are balanced with ample whitespace to ensure that business owners can manage inventory and process transactions without cognitive fatigue. Every element is designed to feel "fast"—both in terms of application performance and the speed at which a user can interpret data.

## Colors

This design system utilizes a palette rooted in **Deep Forest Greens** to symbolize growth and financial stability. The primary green (#003527) provides a professional foundation, while the secondary green creates depth in navigation. 

To ensure the interface feels airy, a **Cool Slate/Blue background** (#F8F9FF) is used instead of pure white, reducing eye strain during long work hours. Semantic colors are highly functional: **Amber** is reserved exclusively for transaction-related actions, while **Red** is strictly for destructive actions or critical stock alerts. Surfaces are layered using subtle blue-tinted grays to differentiate content areas without relying on heavy shadows.

## Typography

**Inter** is the sole typeface for the design system, chosen for its exceptional legibility in data-heavy environments. 

- **Hierarchy:** Dashboard headings use bold weights and tighter letter-spacing to command attention.
- **Form Labels:** These are optimized for quick scanning using a slightly smaller, semi-bold, uppercase treatment.
- **Numerical Data:** For currency (IDR) and stock counts, the design system mandates the use of **Tabular Figures** (`tnum`). This ensures that columns of prices align perfectly, allowing users to compare totals at a glance.
- **Body Text:** Standardized at 14px to maintain a high information density suitable for desktop-first management tools.

## Layout & Spacing

The design system employs a **Fixed-Fluid hybrid grid**. 
- **The Sidebar** is a permanent fixture on the left at 280px, utilizing a clean white background and a 1px right border (#BFC9C3) to separate navigation from the workspace.
- **The Content Area** is fluid, adapting to the browser width but capped at 1440px for optimal readability on wide monitors. 
- **Responsive Behavior:** On tablet devices, the sidebar collapses into a rail or drawer. On mobile, the layout reflows into a single column with horizontal margins reduced to 1rem.
- **Rhythm:** A 4px/8px baseline grid is used to maintain vertical rhythm across all components and containers.

## Elevation & Depth

To maintain the "Lite" and fast feel, the design system avoids heavy, blurry shadows. Instead, it uses **Low-Contrast Outlines** and **Tonal Layering**:
- **Level 0 (Background):** #F8F9FF - The base canvas.
- **Level 1 (Panels/Cards):** #FFFFFF - Raised using a 1px solid border (#BFC9C3).
- **Level 2 (Dropdowns/Modals):** #FFFFFF - These are the only elements allowed to use a subtle ambient shadow (0px 4px 12px rgba(0, 0, 0, 0.05)) to indicate focus.
- **Interactive Depth:** Buttons and clickable cards do not use elevation; they use state-based color shifts (darken/lighten) to indicate interaction.

## Shapes

The design system follows a **"Rounded"** geometric language. 
- **Standard Radius:** 8px (0.5rem) is the default for cards, input fields, and buttons. 
- **Small Elements:** Checkboxes and badges utilize a 4px radius to maintain sharp definition at small scales.
- **Consistency:** All containers must adhere to the 8px rule to create a cohesive, modern UI that feels approachable yet professional.

## Components

### Buttons
- **Primary:** Dark Green (#003527) with White text. Used for main actions (e.g., Save, Add Product).
- **Transaction:** Warning Amber (#FF9939) with White text. Reserved exclusively for "Checkout" or "Process Payment."
- **Danger:** Red (#BA1A1A) with White text. Used for "Delete" or "Void."

### Badges
Badges use the "Soft" variant of their respective colors (e.g., Soft Green for "In Stock," Soft Amber for "Low Stock," Soft Red for "Out of Stock"). Text inside badges must be the high-contrast "Primary" or "Semantic" equivalent to ensure WCAG compliance.

### Form Inputs
Inputs feature an 8px radius and a 1px border (#BFC9C3). Upon focus, the border transitions to Primary Green with a 3px soft ring of Soft Green (#B0F0D6) to provide clear visual feedback.

### Sidebar
The 280px sidebar must use Material Symbols (Rounded style) for navigation items. Icons are sized at 20px, paired with 14px medium-weight text. Active states are indicated by a Soft Blue (#DBE1FF) background and Accent Blue (#0051D5) text/icon.

### Cards
All data containers (inventory lists, sales summaries) are 8px rounded white panels with a 1px #BFC9C3 border. Internal padding is fixed at 24px (1.5rem).