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
  surface-tint: '#3a6841'
  primary: '#053715'
  on-primary: '#ffffff'
  primary-container: '#204e29'
  on-primary-container: '#8dbf8f'
  inverse-primary: '#a0d3a2'
  secondary: '#9b4500'
  on-secondary: '#ffffff'
  secondary-container: '#fe8e48'
  on-secondary-container: '#6b2d00'
  tertiary: '#511d2c'
  on-tertiary: '#ffffff'
  tertiary-container: '#6c3342'
  on-tertiary-container: '#ea9eae'
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
  tertiary-fixed: '#ffd9e0'
  tertiary-fixed-dim: '#ffb1c2'
  on-tertiary-fixed: '#380919'
  on-tertiary-fixed-variant: '#6e3544'
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

The visual style is **Clean Modernism** with a focus on high legibility and friendliness. It utilizes a flat design language, eschewing shadows and gradients in favor of bold color blocks, generous whitespace, and soft, rounded geometry. This approach ensures the UI remains lightweight and performant even on varied mobile network conditions, while the lack of visual "noise" puts the focus entirely on the vendors' products.

## Colors
The palette is inspired by Ivorian growth and energy. 
- **Primary Green (#204E29):** Used for navigation, headers, and structural elements to anchor the app in a sense of security and establishment.
- **Accent Orange (#D56E2B):** Reserved strictly for action-oriented elements like "Buy Now," "Add to Cart," and promotional alerts to draw the eye through high contrast.
- **Background & Surfaces:** A pure white background is used for the main canvas, with a subtle off-white/greenish tint (#F4F7F5) used for secondary containers or section grouping to maintain a "flat but layered" feel without using shadows.
- **Typography:** A consistent Dark Gray (#333333) ensures high readability while feeling softer than pure black.

## Typography
This design system uses **Plus Jakarta Sans** across all levels. Its geometric yet soft curves perfectly mirror the "friendly and modern" brand promise. 

Headlines utilize heavier weights (Bold/ExtraBold) to create a clear hierarchy against the white background. Body text is kept at a comfortable 16px base for accessibility. For small labels or price tags, the semi-bold weight is used to ensure visibility even at smaller sizes.

## Layout & Spacing
The layout follows a **Fluid Grid** system optimized for mobile-first usage. 
- **Margins:** A standard 16px (md) margin is applied to the left and right of all screens.
- **Gutters:** 12px spacing between cards in a multi-column grid (e.g., product listings).
- **Rhythm:** Spacing follows a 4px baseline. Use 16px (md) for grouping related elements and 32px (xl) for separating distinct sections.
- **Verticality:** Ensure generous top and bottom padding within cards and sections to maintain the "airy" feel of the brand.

## Elevation & Depth
This design system intentionally avoids shadows and blurs to maintain a clean, flat aesthetic. Depth is communicated through **Tonal Layering** and **Borders**:
- **Level 0 (Background):** White (#FFFFFF).
- **Level 1 (Cards/Containers):** A 1px solid border using a very light gray (#EEEEEE) or the `surface_subtle` color as a background fill to define boundaries.
- **Interaction:** When an item is pressed, it does not "lift"; instead, it should show a subtle color shift (e.g., a 5% darken or lighten of the fill color).

## Shapes
The shape language is defined by **Pill-shaped (Level 3)** roundedness. This high-radius approach removes "sharpness" from the marketplace, making the app feel safe and inviting. 
- All primary buttons and chips use a fully rounded (pill) radius.
- Cards and input fields use the `rounded-xl` (1.5rem / 24px) setting to maintain consistency with the buttons while maximizing internal content space.

## Components
- **Buttons:** Primary buttons are pill-shaped, using the Accent Orange with white text. Secondary buttons use the Primary Green with white text.
- **Tabs & Chips:** Use a pill-shaped background. Active states use a solid Primary Green fill with white text; inactive states use a `surface_subtle` fill with dark gray text.
- **Inputs:** Search bars and form fields use a 24px corner radius. Borders should be 1px solid light gray, turning Primary Green on focus.
- **Cards:** Product cards should have a 24px corner radius. Use a 1px border instead of a shadow. Images should be clipped to the top corners of the card.
- **Iconography:** Use "Linear" or "Rounded" icon sets with a consistent 2px stroke weight. Avoid filled icons unless they represent an active state.
- **Vendor Badges:** Small pill-shaped tags used to denote "Verified" or "Top Seller," using a Primary Green background at 10% opacity with solid Primary Green text.