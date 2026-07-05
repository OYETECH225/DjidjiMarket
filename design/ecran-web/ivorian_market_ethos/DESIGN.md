---
name: Ivorian Market Ethos
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
  display-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 48px
    fontWeight: '800'
    lineHeight: 56px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 32px
    fontWeight: '800'
    lineHeight: 40px
    letterSpacing: -0.01em
  headline-lg-mobile:
    fontFamily: Plus Jakarta Sans
    fontSize: 24px
    fontWeight: '800'
    lineHeight: 32px
  headline-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 24px
    fontWeight: '700'
    lineHeight: 32px
  headline-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 20px
    fontWeight: '700'
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
  sm: 16px
  md: 24px
  lg: 40px
  xl: 64px
  gutter: 16px
  margin-mobile: 16px
  margin-desktop: 32px
---

## Brand & Style
This design system embodies a "Clean Modernism" aesthetic tailored for the Ivorian digital landscape. It balances local vibrancy with international professional standards. The personality is trustworthy and energetic, utilizing high-contrast flat surfaces rather than depth-simulating effects. 

The visual narrative is driven by sharp execution of color and geometry. By stripping away shadows and gradients, the focus shifts to content clarity and ease of navigation. The result is a crisp, high-performance interface that feels native to the modern Ivorian tech ecosystem—bold, efficient, and welcoming.

## Colors
The palette is rooted in a deep, forest green that signals stability and growth, paired with a sun-baked accent orange to represent the vibrant energy of the marketplace. 

- **Primary Green (#204E29):** Used for main navigation, primary actions, and brand identification. Always paired with white text for AA/AAA accessibility.
- **Accent Orange (#D56E2B):** Reserved for high-value interactions, promotional banners, and status indicators that require immediate attention.
- **Neutral Background (#F9FAF4):** A warm, off-white "bone" color that reduces screen glare and provides a soft canvas for pure white surfaces.
- **Surfaces & Borders:** Interactive cards and containers use pure white (#FFFFFF) with a 1px solid light gray border (#E5E7EB) to define boundaries without using shadows.

## Typography
Plus Jakarta Sans is the exclusive typeface for the design system. It provides a contemporary feel with high legibility. 

Headlines utilize ExtraBold (800) and Bold (700) weights to create a strong visual hierarchy and a sense of authority. Body text maintains a comfortable regular weight for long-form reading, while labels use semi-bold weights with slight tracking adjustments for utility-based UI elements. On mobile devices, headline sizes scale down to prevent awkward line breaks while maintaining their heavy weight for brand consistency.

## Layout & Spacing
The design system utilizes an 8px-based spacing grid for consistent rhythm. 

- **Desktop:** A 12-column fluid grid with 24px gutters. Max content width is 1280px.
- **Mobile:** A 4-column fluid grid with 16px gutters and 16px side margins.
- **Rhythm:** Use "md" (24px) for most container padding and component separation to echo the 24px corner radius of the cards. Use "lg" (40px) for vertical section spacing on landing pages.

## Elevation & Depth
This design system intentionally rejects depth-based metaphors. There are no shadows or blurs. 

Hierarchy is established through **Strokes and Tonal Contrast**. Pure white surfaces are placed atop the #F9FAF4 background and defined by a 1px #E5E7EB border. When an element requires "elevation" (like a dropdown or a modal), it uses a slightly thicker border or a contrasting background color rather than a shadow. This approach ensures the UI remains fast, lightweight, and accessible on all device types.

## Shapes
Geometry is a defining characteristic of this design system. It uses a specific combination of "Soft" and "Pill" shapes.

- **Cards & Containers:** All main surface containers must use a 24px corner radius to create a friendly, modern appearance.
- **Interactive Elements:** Buttons and tags must be "Pill-shaped" (fully rounded) to differentiate them from static content containers.
- **Small Elements:** Checkboxes and inputs use a 8px (Soft) radius to maintain internal structural integrity while still feeling approachable.

## Components

- **Buttons:** Primary buttons are pill-shaped, filled with Primary Green, and contain white text. Secondary buttons use a 1px border of Primary Green with Green text. No shadows or gradients are permitted.
- **Cards:** White background, 1px light gray border, and 24px corner radius. Padding should be a consistent 24px (md) on all sides.
- **Inputs:** 1px border, 8px radius. When focused, the border weight increases to 2px in Primary Green.
- **Chips/Badges:** Pill-shaped with a light tint of the Primary Green or Accent Orange background and dark text. Used for categories and status.
- **Icons:** Minimalist, 2px stroke-based icons. Icons should be monochrome (Primary Green or Gray) to avoid competing with brand colors.
- **Lists:** Clean rows separated by 1px light gray horizontal dividers, avoiding boxed containers for list items unless they are individual product cards.