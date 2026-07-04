---
name: DjidjiMarket Design System
colors:
  surface: '#f9faf4'
  surface-dim: '#d9dbd5'
  surface-bright: '#f9faf4'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f3f4ee'
  surface-container: '#edeee8'
  surface-container-high: '#e7e9e3'
  surface-container-highest: '#e2e3dd'
  on-surface: '#191c19'
  on-surface-variant: '#414940'
  inverse-surface: '#2e312d'
  inverse-on-surface: '#f0f1eb'
  outline: '#727970'
  outline-variant: '#c1c9be'
  surface-tint: '#204e29'
  primary: '#204e29'
  on-primary: '#ffffff'
  primary-container: '#204e29'
  on-primary-container: '#ffffff'
  inverse-primary: '#a0d3a2'
  secondary: '#d56e2b'
  on-secondary: '#ffffff'
  secondary-container: '#d56e2b'
  on-secondary-container: '#ffffff'
  tertiary: '#204e29'
  on-tertiary: '#ffffff'
  tertiary-container: '#204e29'
  on-tertiary-container: '#ffffff'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#bbf0bd'
  primary-fixed-dim: '#a0d3a2'
  on-primary-fixed: '#002108'
  on-primary-fixed-variant: '#22502b'
  secondary-fixed: '#ffdbca'
  secondary-fixed-dim: '#ffb68e'
  on-secondary-fixed: '#331200'
  on-secondary-fixed-variant: '#763300'
  background: '#f9faf4'
  on-background: '#191c19'
  surface-variant: '#e2e3dd'
typography:
  display:
    fontFamily: Plus Jakarta Sans
    fontSize: 40px
    fontWeight: '800'
    lineHeight: 48px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 40px
    letterSpacing: -0.01em
  headline-lg-mobile:
    fontFamily: Plus Jakarta Sans
    fontSize: 24px
    fontWeight: '700'
    lineHeight: 32px
  headline-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
  body-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 18px
    fontWeight: '400'
    lineHeight: 28px
  body-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  label-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 20px
    letterSpacing: 0.01em
  label-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 12px
    fontWeight: '500'
    lineHeight: 16px
rounded:
  sm: 0.5rem
  DEFAULT: 1rem
  md: 1.5rem
  lg: 2rem
  xl: 3rem
  full: 9999px
spacing:
  base: 4px
  xs: 8px
  sm: 12px
  md: 16px
  lg: 24px
  xl: 32px
  xxl: 48px
  container-margin: 16px
  gutter: 12px
---

## Brand & Style
The design system is built to foster trust and communal warmth for a multi-vendor marketplace in Côte d'Ivoire. The brand personality is "The Reliable Neighbor"—approachable, vibrant, yet deeply organized.

The visual style is **Clean Modernism** with a focus on high legibility and friendliness. It utilizes a flat design language, eschewing shadows and gradients in favor of bold color blocks, generous whitespace, and soft, rounded geometry.

## Colors
The palette matches the official DjidjiMarket brand identity exactly (see brand guide PDF).
- **Primary Green (#204E29):** navigation, headers, structural elements, verified/trust indicators. Always paired with white text/icons (`on-primary` / `on-primary-container` = #ffffff).
- **Accent Orange (#D56E2B):** reserved strictly for action-oriented elements ("Buy Now," "Add to Cart," promotional alerts). Always paired with white text (`on-secondary` / `on-secondary-container` = #ffffff).
- **Tertiary:** intentionally mirrors primary green — this brand has only two colors (green + orange). Do not introduce a third accent color (e.g. pink/tertiary tones from generic Material 3 generation) anywhere in the UI.
- **Background & Surfaces:** off-white background (#f9faf4), white card surfaces, tonal grays for layering — no shadows.
- **Typography color:** `on-surface` (#191c19, near-black) for body text, `on-surface-variant` (#414940) for secondary/muted text.

## Typography
**Official brand font: Plus Jakarta Sans** (supersedes any earlier mention of Poppins/Nunito Sans in prior brand documents — Plus Jakarta Sans is the single source of truth going forward).

Headlines use heavier weights (700/800) for hierarchy. Body text at 16px base. Labels use semi-bold (500/600) for visibility at small sizes.

## Layout & Spacing
Mobile-first fluid grid.
- **Margins:** 16px (md) standard screen margin.
- **Gutters:** 12px between cards in grids.
- **Rhythm:** 4px baseline; 16px for grouping related elements, 32px between distinct sections.
- **Verticality:** generous top/bottom padding inside cards/sections.

## Elevation & Depth
No shadows or blurs. Depth via **Tonal Layering** and **Borders**:
- **Level 0 (Background):** off-white (#f9faf4).
- **Level 1 (Cards/Containers):** white fill with a 1px solid light gray border (`outline-variant` #c1c9be).
- **Interaction:** pressed state = subtle 5% darken/lighten of fill, no "lift" effect.

## Shapes
**Pill-shaped (Level 3)** roundedness throughout.
- Primary/secondary buttons and chips: fully rounded (pill).
- Cards and input fields: `rounded-xl` (24px).

## Components
- **Buttons:** Primary action buttons = Accent Orange fill (`secondary` #d56e2b) + white text. Secondary buttons = Primary Green fill (`primary` #204e29) + white text. Never orange text on green or vice versa — always white on color.
- **Tabs & Chips:** pill-shaped. Active = solid Primary Green fill + white text. Inactive = `surface-container` fill + `on-surface-variant` dark gray text.
- **Inputs:** 24px corner radius, 1px solid `outline-variant` border, turns Primary Green (`primary`) on focus.
- **Cards:** 24px corner radius, 1px border (no shadow), images clipped to top corners.
- **Iconography:** Linear/Rounded icon sets, 2px stroke weight, outline style unless representing an active/selected state.
- **Vendor Badges ("Vérifié"):** pill-shaped tag, Primary Green at 10% opacity background, solid Primary Green text/icon.
