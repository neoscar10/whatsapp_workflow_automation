---
name: CompliFlow
colors:
  surface: '#fcf9f8'
  surface-dim: '#dcd9d9'
  surface-bright: '#fcf9f8'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f6f3f2'
  surface-container: '#f0eded'
  surface-container-high: '#eae7e7'
  surface-container-highest: '#e5e2e1'
  on-surface: '#1c1b1b'
  on-surface-variant: '#424656'
  inverse-surface: '#313030'
  inverse-on-surface: '#f3f0ef'
  outline: '#727687'
  outline-variant: '#c2c6d8'
  surface-tint: '#0054d6'
  primary: '#0050cb'
  on-primary: '#ffffff'
  primary-container: '#0066ff'
  on-primary-container: '#f8f7ff'
  inverse-primary: '#b3c5ff'
  secondary: '#006a61'
  on-secondary: '#ffffff'
  secondary-container: '#86f2e4'
  on-secondary-container: '#006f66'
  tertiary: '#4345d1'
  on-tertiary: '#ffffff'
  tertiary-container: '#5d60eb'
  on-tertiary-container: '#faf6ff'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#dae1ff'
  primary-fixed-dim: '#b3c5ff'
  on-primary-fixed: '#001849'
  on-primary-fixed-variant: '#003fa4'
  secondary-fixed: '#89f5e7'
  secondary-fixed-dim: '#6bd8cb'
  on-secondary-fixed: '#00201d'
  on-secondary-fixed-variant: '#005049'
  tertiary-fixed: '#e1e0ff'
  tertiary-fixed-dim: '#c0c1ff'
  on-tertiary-fixed: '#07006c'
  on-tertiary-fixed-variant: '#2f2ebe'
  background: '#fcf9f8'
  on-background: '#1c1b1b'
  surface-variant: '#e5e2e1'
typography:
  display-lg:
    fontFamily: Inter
    fontSize: 48px
    fontWeight: '700'
    lineHeight: 56px
    letterSpacing: -0.04em
  headline-lg:
    fontFamily: Inter
    fontSize: 32px
    fontWeight: '600'
    lineHeight: 40px
    letterSpacing: -0.03em
  headline-lg-mobile:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
    letterSpacing: -0.02em
  headline-md:
    fontFamily: Inter
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
    letterSpacing: -0.02em
  body-lg:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 26px
    letterSpacing: 0em
  body-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 22px
    letterSpacing: 0em
  label-md:
    fontFamily: Geist
    fontSize: 12px
    fontWeight: '500'
    lineHeight: 16px
    letterSpacing: 0.02em
  mono-sm:
    fontFamily: Geist
    fontSize: 12px
    fontWeight: '400'
    lineHeight: 18px
    letterSpacing: 0em
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  base: 4px
  xs: 8px
  sm: 12px
  md: 16px
  lg: 24px
  xl: 32px
  2xl: 48px
  3xl: 64px
  gutter: 24px
  margin-mobile: 16px
  margin-desktop: 40px
---

## Brand & Style
The design system embodies a premium, high-fidelity fintech aesthetic that balances institutional trust with modern software agility. It is designed for high-stakes financial operations where clarity and precision are paramount.

The visual style is a synthesis of **Minimalism** and **Glassmorphism**, characterized by:
- **Atmospheric Depth:** Using translucent layers and background blurs to create a sense of hierarchy without heavy visual weight.
- **Precision Engineering:** Borrowing the technical rigor of developer tools (Linear) and the polished elegance of modern banking (Mercury).
- **Professional Calm:** A high-whitespace approach that reduces cognitive load during complex compliance workflows.
- **Dynamic Modernity:** Subtle use of mesh gradients and micro-interactions to signal a state-of-the-art automated platform.

## Colors
The palette is rooted in a "Soft White" ecosystem to differentiate from the sterile starkness of pure white interfaces.

- **Primary & Secondary:** An authoritative Blue (#0066FF) drives action, while a sophisticated Teal (#0D9488) is used for "Success" states and financial growth indicators.
- **Typography:** Deep Charcoal (#1A1A1A) provides high legibility while appearing softer and more premium than pure black.
- **Accents:** A tertiary Indigo (#6366F1) is reserved for automated features and AI-driven insights.
- **Surface Strategy:** Use semi-transparent white (80-90% opacity) with a 20px backdrop blur for floating cards and navigation overlays to maintain the glassmorphic theme.

## Typography
This design system utilizes **Inter** for its systematic clarity and **Geist** for technical labels and data-heavy instances.

- **Headings:** Feature tight negative letter-spacing and semi-bold/bold weights to create a "locked-in" professional look.
- **Body:** Prioritizes readability with generous line heights (1.5x - 1.6x) to facilitate the reading of dense financial regulations or audit trails.
- **Data Display:** Use the monospaced Geist font for transaction IDs, currency amounts, and timestamps to ensure character alignment and a "fintech-native" feel.

## Layout & Spacing
The layout relies on a **Fluid Grid** with strict adherence to an 8px spatial system to ensure mathematical harmony.

- **Grid:** A 12-column grid for desktop with 24px gutters. Content should be grouped in logic-based clusters (cards) rather than sprawling across the full width.
- **Margins:** Generous page margins (40px+) on desktop create a "canvas" feel that elevates the content.
- **Density:** Use "High" density for data tables and "Spacious" density for landing dashboards and onboarding flows.

## Elevation & Depth
Depth is created through a combination of tonal layering and multi-stop ambient shadows.

- **Surface Tiers:**
    - **Level 0 (Background):** #FAFAFA.
    - **Level 1 (Cards):** White (#FFFFFF) with a 1px border (#E5E7EB).
    - **Level 2 (Modals/Popovers):** White with glassmorphism (backdrop-blur: 12px) and a multi-layered shadow.
- **Shadows:** Avoid harsh black shadows. Use soft, diffused shadows with a slight blue tint (e.g., `hsla(220, 30%, 10%, 0.05)`).
- **Borders:** Utilize extremely subtle "inner-glow" borders on buttons and cards to simulate a tactile, high-end hardware feel.

## Shapes
The shape language is sophisticated and approachable. 

- **Standard Elements:** Buttons, input fields, and small cards use a **12px** (rounded-lg) radius.
- **Large Containers:** Dashboard widgets and main content areas use a **16px** (rounded-xl) radius.
- **Interactive States:** Use a slight "squish" or scale-down effect (0.98x) on click for tactile feedback.

## Components
- **Buttons:** Primary buttons feature a subtle linear gradient (Primary Blue to a slightly darker shade) and a 1px top-border highlight.
- **Input Fields:** Soft grey backgrounds (#F3F4F6) that transition to White on focus with a Primary Blue 2px ring. Labels are always Geist Medium.
- **Cards:** Incorporate a "Glass" variant for sidebars and navigation panels using 80% opacity and `backdrop-filter: blur(20px)`.
- **Status Chips:** Use desaturated background versions of the semantic colors (e.g., Soft Teal background for "Approved") with high-contrast text.
- **Data Tables:** Row hovering should use a very subtle #F9FAFB tint. Avoid vertical lines; use horizontal dividers only (#F3F4F6).
- **Steppers:** For compliance workflows, use a vertical "Linear-style" stepper with thin connecting lines and glowing active states.