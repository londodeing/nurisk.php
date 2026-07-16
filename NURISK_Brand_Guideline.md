# NURISK
### NU Disaster Risk Information System
**Brand Guideline & Design System — Version 1.0**

Developed by **LPBI NU Jawa Tengah** (NU Peduli Jawa Tengah)

Implementation-ready for Flutter · Laravel Blade · Bootstrap · Tailwind CSS · Material 3

---

## Table of Contents

1. Brand Story
2. Brand Values & Personality
3. Logo System
4. Color System
5. Typography
6. Iconography
7. Illustration & Imagery Guidelines
8. Layout System
9. Mobile Design System & Component Library
10. Dashboard Design System
11. Component States
12. Motion Design
13. Accessibility
14. Voice & Tone
15. Flutter Theme Specification
16. Material 3 Theme Mapping
17. Dark Mode Rules
18. Usage Examples & Do / Don't
19. Future Branding Expansion
20. Governance & Document Ownership

---

## 1. Brand Story

NURISK exists because disaster does not wait for paperwork. When a landslide closes a village road in the hills of Central Java, or a flood displaces a coastal community overnight, the difference between chaos and coordinated relief is information — accurate, fast, and shared by everyone who needs it. NURISK is the digital nervous system for LPBI NU Jawa Tengah's humanitarian response: it connects the citizen reporting a hazard, the volunteer heading into the field, the operator verifying an assessment, the district administrator allocating logistics, and the provincial commander making the call — all inside one calm, dependable system.

The brand is built on a simple premise: technology in a crisis must never compete for attention. It must recede into competence. Every visual decision in this guideline — the deep, grounded green, the unembellished type, the restrained motion — exists to keep the interface out of the way of the decision being made.

### 1.1 Brand Positioning

NURISK is positioned as a government-grade humanitarian operations platform, not a consumer app. It borrows the information density and reliability cues of command-and-control software, and the warmth and clarity cues of humanitarian and public-service design. It deliberately avoids the visual language of social media (bright gradients, playful illustration, infinite scroll) and fintech (glassmorphism, neon accents, gamified progress) because both erode the perceived seriousness and trustworthiness required for disaster response.

### 1.2 Brand Pillars

**Humanity first** — Every screen should read as being built for a person under stress: clear hierarchy, generous touch targets, forgiving error states.

**Radical clarity** — One primary action per screen. No ambiguity about system status, especially during active incidents.

**Calm authority** — Confident, muted color use; urgency is expressed through semantic color and copy, never through visual noise.

**Built to scale** — The same design tokens must work across a public-facing mobile app, an internal command center, and a GIS-heavy analytics dashboard.

---

## 2. Brand Values & Personality

### 2.1 Core Values

- **Humanity** — the person affected by disaster is the center of every decision.
- **Professionalism** — output must be credible enough for provincial government and NU leadership.
- **Trust** — consistent, predictable interface behavior across every module.
- **Rapid Response** — minimal steps between observation and action.
- **Collaboration** — shared visual language between citizen, volunteer, and command levels.
- **Accountability** — every status, approval, and log is visibly attributable and timestamped.
- **Transparency** — the public dashboard uses the identical data visualization language as internal tools, nothing hidden behind different styling.
- **Resilience** — the interface itself must remain usable in low-connectivity, low-light, field conditions.

### 2.2 Personality Spectrum

NURISK sits at defined points on four personality spectrums — the fastest way for any designer or developer joining the project to self-check a new screen.

| Spectrum | NURISK sits here | Not here |
|---|---|---|
| Playful ←→ Serious | Serious, 85% | Cartoonish, gamified |
| Cold ←→ Warm | Warm, 60% | Clinical / sterile |
| Minimal ←→ Dense | Dense-but-organized, context-dependent | Cluttered without hierarchy |
| Quiet ←→ Loud | Quiet, 80% — urgency reserved for semantic color | Constant red/alert visual noise |

---

## 3. Logo System

The NURISK logo pairs a shield-and-compass mark with a wordmark. The shield references NU's institutional heritage and protective mandate; the compass/pulse notch at its center signals live monitoring and direction-finding — the two core functions of the system.

### 3.1 Logo Variants

| Variant | Usage |
|---|---|
| Primary logo | Full-color mark + wordmark, Deep NU Green on white. Default for splash, login, letterhead, public dashboard header. |
| Secondary logo | Single-color Deep NU Green mark + wordmark, for constrained-color print (thermal printers, faxed situation reports). |
| Monochrome | 100% black or 100% white version for single-ink documents and engraved hardware. |
| Negative | White mark on Deep NU Green or photographic background — dark hero sections and field banners. |
| Horizontal lockup | Mark left, wordmark right — app bars, letterhead, wide banners. |
| Vertical lockup | Mark above wordmark, centered — splash screens, cover pages. |
| Icon only | Shield-compass mark with no wordmark — app launcher icon, favicon, avatar fallback. |
| Favicon | 16×16 / 32×32 flattened icon-only mark, single color below 24px. |
| Splash logo | Vertical lockup, centered, animated fade-and-settle on cold start (600ms). |
| Launcher icon (Android/iOS) | Icon-only mark on Primary-600 rounded-square background, 15% safe-zone padding per side. |
| Adaptive icon (Android 8+) | Foreground: mark only, transparent. Background: solid Primary-600. Mark stays inside the 66% safe circle. |
| Notification icon (Android status bar) | Flattened single-color silhouette, white on transparent, no gradient. |
| Watermark | Icon-only mark at 6% opacity, Neutral-900, behind printed situation reports and PDF exports. |

### 3.2 Construction & Spacing Rules

- Minimum size: 24px height (digital), 15mm height (print) for the horizontal lockup; 20px for icon-only.
- Clear space: padding equal to the height of the shield mark on all sides — nothing may enter this zone.
- Backgrounds: primary logo only on white, Neutral-50, or photography with a scrim ≥40% black. Negative logo on Primary-700/800/900 or dark photography.

> **Rationale:** A fixed clear-space ratio (rather than a fixed pixel value) scales correctly whether the logo appears at 24px in a nav bar or 200px on a cover page, preventing the most common real-world logo violation: crowding.

### 3.3 Incorrect Usage

- Do not recolor the mark outside the approved palette.
- Do not stretch, skew, or rotate the lockup.
- Do not place the full-color logo on a low-contrast or busy photographic background.
- Do not recreate the wordmark in a different typeface.
- Do not add drop shadows, bevels, gradients, or outlines to the mark.
- Do not separate the compass notch from the shield or use it as a standalone decorative element.

---

## 4. Color System

Deep NU Green is the anchor of the entire system — grounded, institutional, and legible in both bright outdoor field conditions and dim command-center rooms. It is deliberately darker and less saturated than a typical "eco" green to avoid reading as an environmental or agricultural brand; instead it reads as institutional and dependable, consistent with its NU heritage.

### 4.1 Primary Palette — Deep NU Green (50–900)

| Step | HEX | RGB | HSL |
|---|---|---|---|
| 50 | #E6F3EC | 230,243,236 | 150°,40%,93% |
| 100 | #C2E4D2 | 194,228,210 | 150°,39%,83% |
| 200 | #99D3B5 | 153,211,181 | 150°,38%,71% |
| 300 | #6FC297 | 111,194,151 | 150°,38%,60% |
| 400 | #4CB47F | 76,180,127 | 150°,41%,50% |
| 500 | #2CA368 | 44,163,104 | 150°,58%,41% |
| 600 (Base) | #0F6B3C | 15,107,60 | 150°,75%,24% |
| 700 | #0B5730 | 11,87,48 | 150°,78%,19% |
| 800 | #084325 | 8,67,37 | 150°,79%,15% |
| 900 | #052E19 | 5,46,25 | 150°,80%,10% |

> **Rationale:** A 10-step scale (not just 3–5 shades) is required because NURISK spans public marketing surfaces (needs 500–700 for vibrant CTAs), dense dashboards (needs 50–100 for subtle fills, 800–900 for on-dark text), and dark mode (needs 400 as the accessible accent against near-black surfaces).

### 4.2 Neutral Palette

| Step | HEX |
|---|---|
| 50 | #F8F9FA |
| 100 | #F1F3F5 |
| 200 | #E9ECEF |
| 300 | #DEE2E6 |
| 400 | #CED4DA |
| 500 | #ADB5BD |
| 600 | #6C757D |
| 700 | #495057 |
| 800 | #343A40 |
| 900 | #212529 |

### 4.3 Semantic Palette

| Token | HEX | Usage |
|---|---|---|
| Emergency Red | #D7263D | Critical incidents, disaster alerts, SOS, delete/destructive actions |
| Warning Orange | #F2994A | Pending review, at-risk status, caution banners |
| Safe Green | #27AE60 | Resolved, verified, mission complete, safe-zone map markers |
| Information Blue | #2F80ED | Informational messages, links, in-progress status |
| Dark Text | #1A1D1F | Primary reading text on light surfaces |
| Background White | #FFFFFF | Base app background, cards on light theme |

> **Rationale:** Semantic color is the only place urgency is allowed to live. Because the brand palette itself stays calm, a red banner or orange chip immediately reads as meaningful rather than being lost among decorative reds elsewhere in the UI — this is what makes alerts trustworthy rather than fatiguing.

### 4.4 Dark Mode Palette

| Token | HEX | Usage |
|---|---|---|
| Surface Base | #121614 | App background in dark mode |
| Surface Elevated | #1B211D | Cards, sheets, dialogs |
| Surface Overlay | #242B26 | Modals, popovers |
| Primary Accent (Dark) | #4CB47F | Primary-400 used instead of 600 for AA contrast on dark |
| Text Primary (Dark) | #F1F3F5 | Main reading text |
| Text Secondary (Dark) | #ADB5BD | Supporting text, captions |
| Border (Dark) | #343A40 | Dividers, card outlines |

### 4.5 Flutter Color Values

```dart
class NuriskColors {
  static const Color primary50  = Color(0xFFE6F3EC);
  static const Color primary100 = Color(0xFFC2E4D2);
  static const Color primary400 = Color(0xFF4CB47F);
  static const Color primary600 = Color(0xFF0F6B3C); // Base
  static const Color primary700 = Color(0xFF0B5730);
  static const Color primary900 = Color(0xFF052E19);
  static const Color emergencyRed = Color(0xFFD7263D);
  static const Color warningOrange = Color(0xFFF2994A);
  static const Color safeGreen = Color(0xFF27AE60);
  static const Color infoBlue = Color(0xFF2F80ED);
  static const Color darkText = Color(0xFF1A1D1F);
  static const Color bgWhite = Color(0xFFFFFFFF);
  static const Color surfaceDark = Color(0xFF121614);
}
```

### 4.6 CSS Variables

```css
:root {
  --nurisk-primary-50: #E6F3EC;
  --nurisk-primary-100: #C2E4D2;
  --nurisk-primary-400: #4CB47F;
  --nurisk-primary-600: #0F6B3C;
  --nurisk-primary-700: #0B5730;
  --nurisk-primary-900: #052E19;
  --nurisk-red: #D7263D;
  --nurisk-orange: #F2994A;
  --nurisk-green-safe: #27AE60;
  --nurisk-blue: #2F80ED;
  --nurisk-text: #1A1D1F;
  --nurisk-bg: #FFFFFF;
}
[data-theme='dark'] {
  --nurisk-bg: #121614;
  --nurisk-text: #F1F3F5;
  --nurisk-primary-600: #4CB47F;
}
```

### 4.7 Tailwind Tokens (tailwind.config.js excerpt)

```js
colors: {
  primary: {
    50:'#E6F3EC',100:'#C2E4D2',200:'#99D3B5',300:'#6FC297',
    400:'#4CB47F',500:'#2CA368',600:'#0F6B3C',700:'#0B5730',
    800:'#084325',900:'#052E19'
  },
  emergency:'#D7263D', warning:'#F2994A',
  safe:'#27AE60', info:'#2F80ED'
}
```

---

## 5. Typography

### 5.1 Font Family

**Primary: Plus Jakarta Sans** — a humanist grotesque available free on Google Fonts and easily bundled in Flutter via the `google_fonts` package or embedded as static assets for offline field use. Its slightly rounded terminals soften the otherwise institutional palette without tipping into a playful register, and its Latin + extended diacritic coverage handles Bahasa Indonesia and regional names cleanly.

**Secondary / data workhorse: Inter** — used inside dense dashboard tables, forms, and charts where its tabular figures and tested small-size legibility outperform Plus Jakarta Sans below 13px.

**Monospace: Roboto Mono** — mission IDs, GPS coordinates, API/log payloads, approval reference numbers.

**Fallback stack:** Plus Jakarta Sans → Inter → Roboto (Android system) → -apple-system/San Francisco (iOS) → sans-serif.

> **Rationale:** Two humanist sans-serifs rather than one keeps a single visual voice while giving each surface the metrics it needs — Plus Jakarta Sans for warmth in headlines and reading content, Inter for density and numeral clarity in operational tables. Both are open-source, avoiding licensing risk for a government-affiliated deployment.

### 5.2 Type Scale

| Style | Size/Line | Weight | Tracking | Usage |
|---|---|---|---|---|
| Display | 32/40 | Bold 700 | -0.2px | Splash, onboarding, hero KPI numbers |
| Headline | 28/36 | Bold 700 | -0.2px | Screen titles, dashboard section headers |
| Title | 22/28 | SemiBold 600 | 0 | Card titles, dialog titles |
| Subtitle | 18/24 | SemiBold 600 | 0 | List section headers, sub-navigation |
| Body Large | 16/24 | Regular 400 | 0 | Primary reading content, mission descriptions |
| Body | 14/20 | Regular 400 | 0.1px | Default UI text, form labels, descriptions |
| Caption | 12/16 | Regular 400 | 0.2px | Timestamps, helper text, metadata |
| Label / Overline | 11/16 | SemiBold 600, UPPERCASE | 0.6px | Status chips, section eyebrows |
| Button | 14/20 | SemiBold 600 | 0.2px | All button and CTA text |
| Navigation | 12/16 | Medium 500 | 0.1px | Bottom nav, tab labels |
| Dashboard KPI | 36/40 | Bold 700, tabular nums | -0.4px | Command Center statistic figures |
| Table Cell | 13/18 | Regular 400, tabular nums | 0 | Data tables, logs |
| Chart Label | 11/14 | Medium 500 | 0.1px | Axis labels, legends |
| Code / ID | 13/20 | Regular 400 (mono) | 0 | Mission IDs, coordinates, API payloads |

### 5.3 Spacing, Weight & Rhythm Rules

- Body copy line-height is fixed at 1.5× for reading content, 1.4× for UI labels — never below 1.3× for accessibility.
- Paragraph spacing: 8px between related lines, 16px between distinct paragraphs.
- Only three weights are used system-wide: Regular (400), SemiBold (600), Bold (700) — Medium (500) is reserved exclusively for navigation labels to keep the weight vocabulary small and predictable.
- Numerals in tables, KPIs, and charts always use tabular (monospaced) figures so columns align.

---

## 6. Iconography

**Icon family: Material Symbols Rounded**, used in its Rounded terminal style at 1.5px stroke-equivalent weight. Rounded is chosen over Sharp because it matches the softened-institutional personality of the wordmark, and over the default Material Outlined because Rounded reads friendlier without sacrificing the seriousness needed for a disaster-response context.

> **Rationale:** Material Symbols ships natively as a variable font with Flutter (`Icons`/`Symbols` classes) and Google Fonts for web — meaning one icon source drives both the mobile app and Laravel dashboard with zero duplicated asset pipelines, and supports fill/weight/optical-size axes for state changes without swapping SVGs.

### 6.1 Style Rules

- Default state: Outlined/unfilled, 1.5px stroke.
- Active/selected state: Filled variant of the same glyph, Primary-600.
- Minimum rendering size: 20px; minimum tap target 48×48dp regardless of visual icon size.
- Icons never carry meaning alone in critical flows — always pair with a text label for disaster-severity and status icons (accessibility + non-native-reading users).

### 6.2 Functional Icon Map

| Category | Representative glyphs (Material Symbols names) |
|---|---|
| Navigation | house, map-trifold, list-checks, users-three, user-circle |
| Disaster / Hazard | warning, fire, waves (flood), mountain (landslide), wind, house-line (damage) |
| Mission | target, flag-checkered, path, clipboard-text, timer |
| Volunteer | users-three, id-badge, hand-heart, shield-check |
| Assessment | clipboard, magnifying-glass, camera, ruler, check-square |
| Inventory / Logistics | package, truck, warehouse, boxes, stack |
| Approval | seal-check, signature, check-circle, x-circle, clock-user |
| Weather | cloud-rain, sun, cloud-lightning, thermometer, drop |
| Map / GIS | map-pin, compass, polygon, layers, crosshair |
| Notification | bell, bell-ringing, chat-circle-text, envelope-simple |

---

## 7. Illustration & Imagery Guidelines

### 7.1 Illustration Style

Modern flat / semi-flat illustration with restrained shading (2-tone shadow max), using the primary and neutral palette plus a single semantic accent per scene. Style reference: government infographic and public-health-campaign illustration — think WHO or UNDP explainer graphics rather than app-store onboarding illustration.

- Characters are depicted with simplified, respectful, non-caricatured proportions — no oversized heads, no anime styling, no chibi proportions.
- Disaster scenes are depicted at a level of abstraction that conveys the hazard (water lines for flood, cracked terrain for landslide, flame silhouette for fire) without graphic or distressing detail.
- Volunteer and community figures wear neutral, non-branded clothing unless depicting an official TRC/NU uniform for training material.

> **Rationale:** Because NURISK deals directly with real disaster and loss, illustration must never risk feeling dismissive or trivializing — flat, infographic-style renderings signal "informational tool," while cute or anime-adjacent styling would undercut the platform's credibility in exactly the moments it matters most.

### 7.2 Photography Style

- Documentary, natural-light photography of real field operations wherever available; no stock-photo staged smiling crowds.
- Color-graded with a very slight warm-neutral tone (not desaturated/moody, not oversaturated) to stay consistent with the brand's calm-but-human personality.
- Always apply a Neutral-900 scrim at 30–50% when overlaying white text for accessibility.

---

## 8. Layout System

### 8.1 Grid

- Mobile: 4-column grid, 16px margins, 8px gutter.
- Tablet: 8-column grid, 24px margins, 16px gutter.
- Desktop dashboard: 12-column grid, 32px margins, 24px gutter, max content width 1440px with centered overflow.

### 8.2 8pt Spacing System

| Token (px) | Usage |
|---|---|
| 4 | xs — icon-to-label gap, chip internal padding |
| 8 | sm — base unit; default gap between related elements |
| 12 | md — internal card padding (compact), form field gaps |
| 16 | lg — standard card padding, section internal margin |
| 24 | xl — spacing between unrelated content blocks |
| 32 | 2xl — screen top margin, major section separation |
| 48 | 3xl — dashboard widget grid gutters (tablet/desktop) |

> **Rationale:** An 8pt base (with a 4px half-step for icon/label micro-adjustments) maps cleanly to both Flutter's logical pixel system and CSS rem units at a 16px root, so the same spacing tokens produce visually identical rhythm across the mobile app and web dashboard.

### 8.3 Corner Radius

| Token | Applies to |
|---|---|
| Radius XS — 4px | Chips, badges, input fields |
| Radius SM — 8px | Buttons, small cards, dialogs |
| Radius MD — 12px | Standard cards, bottom sheets |
| Radius LG — 16px | Hero cards, image cards |
| Radius Full — 999px | Avatars, status pills, FAB |

### 8.4 Elevation & Shadow

| Level | Shadow spec | Applies to |
|---|---|---|
| Level 0 | 0dp | Flat, on-background surfaces (list rows) |
| Level 1 | 1dp / y2 blur4 8% black | Resting cards |
| Level 2 | 3dp / y4 blur8 10% black | Raised cards, app bar on scroll |
| Level 3 | 6dp / y6 blur16 12% black | FAB, dropdown menus |
| Level 4 | 8dp / y8 blur24 16% black | Dialogs, bottom sheets |
| Level 5 | 12dp / y12 blur32 20% black | Modal command palette, alert overlays |

---

## 9. Mobile Design System & Component Library

All components share the same tokens (color, radius, elevation, spacing) so a developer moving between Flutter widgets and Blade/Tailwind partials never has to re-derive values.

**Buttons** — Primary (filled Primary-600, white text), Secondary (outlined Primary-600, 1.5px border), Tertiary (text-only, Primary-700), Destructive (filled Emergency-600). Height 48dp, radius SM, horizontal padding 20px. Disabled = 38% opacity.

**FAB** — 56dp circle, Primary-600 fill, elevation Level 3, used only for the single most critical action per screen (e.g. "Report Incident", "New Mission").

**Bottom Navigation** — 4–5 items max, 64dp height, active item shows filled icon + Primary-600 label, inactive shows outlined icon + Neutral-600 label.

**Top App Bar** — 56dp height, Neutral-50 or Primary-700 (context-dependent), centered or leading title per platform convention, max 2 trailing actions before overflow menu.

**Navigation Rail / Drawer** — Used on tablet/desktop breakpoints ≥840px for Operator and Command Center roles; collapses to bottom nav below that breakpoint.

**Dialog** — Radius MD, elevation Level 4, max width 400px, always includes explicit Cancel + primary action, never dismiss-only for destructive confirmations.

**Bottom Sheet** — Radius MD top corners only, drag handle 32×4px Neutral-300, used for filters, quick actions, and mission detail preview.

**SnackBar** — Neutral-900 background, white text, 1 optional text action, auto-dismiss 4s, never used for critical/blocking alerts.

**Toast** — Lightweight, non-interactive, 2s duration, used for low-stakes confirmations ("Saved", "Synced").

**Input Field** — 48dp height, radius XS, Neutral-300 border default, Primary-600 border on focus, Emergency-600 border + helper text on error.

**Search** — Persistent search bar in list/map screens, leading search icon, trailing clear icon, radius Full.

**Filter / Chip** — Radius Full, Neutral-100 fill default, Primary-50 fill + Primary-700 text when active, includes optional count badge.

**Badge** — 10px circle or pill, Emergency-600 for unread/critical counts, Neutral-600 for neutral counts.

**Avatar** — Circle, initials fallback on Primary-100 background with Primary-700 text, role-ring color-coded (Volunteer=Info Blue, Operator=Primary-600, Commander=Emergency-600).

**Progress (linear/circular)** — Primary-600 indicator on Neutral-100 track, indeterminate for unknown duration, determinate with % label for uploads/sync.

**Timeline** — Vertical connector line Neutral-300, event dot color-coded by status, used in mission and assessment history.

**Empty State** — Centered icon (Neutral-300), Title + one-line Body copy, single primary action where applicable — never a bare blank screen.

**Loading / Skeleton** — Shimmer animation over Neutral-100 blocks matching final content shape; spinners reserved for sub-1s actions only.

**Map Marker** — Teardrop pin, fill color = incident severity (Emergency/Warning/Safe/Info), cluster badge shows count on zoom-out.

**Mission Card** — Radius MD, elevation Level 1, leading status dot, title, location + distance, assigned volunteer avatars, trailing chevron.

**Assessment Card** — Thumbnail (damage photo) + severity chip + timestamp + assessor name.

**Volunteer Card** — Avatar + name + role badge + availability status dot + current assignment (if any).

**Alert Card** — Full-width, semantic-colored left border (4px), icon, headline, short body, optional CTA — used for active-incident banners.

**Statistics Card** — Large tabular KPI number (Dashboard KPI style), label, optional trend arrow + delta in semantic color.

**Charts** — Line/bar/donut per data-viz section 10.6, always paired with a data table toggle for accessibility.

**Weather Card** — Icon + temperature + condition + short forecast strip, Info Blue accent.

**Quick Action Card** — Icon-forward square tile grid on home screen, 2–4 items, for "Report", "Call Command Center", "View Map", "My Missions".

**Profile Card** — Avatar + name + role + org unit (e.g. PC/PCI, Kecamatan) + verification badge.

**Approval Card** — Requestor + item summary + Approve/Reject inline actions (role-gated) + audit trail link.

---

## 10. Dashboard Design System

Dashboards share the mobile design tokens but shift to a denser, table-and-widget-centric layout appropriate for desktop monitors used in the Command Center.

**Command Center** — Full-bleed dark-mode-first layout (extended monitoring shifts, reduced eye strain). Large map panel (60% width) + live incident feed + KPI strip. Auto-refresh indicator always visible.

**Operator Dashboard** — Light mode default, task-queue-centric: assigned assessments, pending approvals, mission board (kanban-style columns by status).

**Leadership Dashboard** — Summary-first: province/district rollup KPIs, trend charts, map heatmap, minimal raw tables — leadership scans, doesn't operate.

**Public Dashboard** — Simplified, no login-gated data, large readable KPIs, plain-language status legend, mobile-first responsive, WCAG AA mandatory since the audience is unauthenticated general public.

**Analytics** — Filter bar pinned top, chart grid below, CSV/PDF export always available, date-range comparison as default interaction.

**Mission / Incident Tracking** — Kanban or timeline view toggle, status chip vocabulary identical to mobile app for cross-platform consistency.

**Heatmap** — Primary-scale sequential ramp (50→900) for density, never uses semantic red/orange for density-only data to avoid confusion with severity.

**GIS / Map Layer Panel** — Collapsible right-side layer toggle list, grouped by category (hazard, infrastructure, resource, boundary).

> **Rationale:** Reserving the semantic red/orange/green vocabulary strictly for incident severity — and using the neutral Primary green scale for volume/density visualizations like heatmaps — prevents the single most common dashboard misread: confusing "a lot of data here" with "this is dangerous."

### 10.1 Responsive Breakpoints

| Breakpoint | Width | Layout behavior |
|---|---|---|
| Mobile | <600px | Single column, bottom nav |
| Tablet | 600–1024px | 2-column, nav rail appears |
| Desktop | 1024–1440px | 12-col grid, drawer nav, multi-panel dashboards |
| Large / Command Center | ≥1440px | Multi-panel with persistent map + feed + KPI strip |

---

## 11. Component States

Every interactive component in the library must implement the full state set below; incomplete state coverage is the most common source of inconsistency between the Flutter app and the Blade/Tailwind dashboard.

| State | Visual treatment |
|---|---|
| Normal | Base color, no overlay |
| Hover (web only) | Surface + 4% black overlay |
| Pressed | Surface + 8% black overlay, scale 0.98, 100ms |
| Focused | 2px Primary-500 outline, 2px offset (keyboard nav) |
| Selected | Primary-50 fill, Primary-600 text/icon, 2px left accent bar |
| Disabled | 38% opacity, no shadow, pointer events off |
| Loading | Skeleton shimmer or inline spinner, content masked |
| Success | Safe Green-50 fill, Safe Green-600 icon/text |
| Warning | Warning Orange-50 fill, Warning Orange-700 icon/text |
| Danger | Emergency Red-50 fill, Emergency Red-700 icon/text |
| Offline | Neutral-100 fill, diagonal-stripe badge, sync icon |
| Read-only | Neutral-50 fill, lock icon suffix, no focus ring |
| Locked | Neutral-100 fill, 60% opacity, lock icon, tooltip on tap |

---

## 12. Motion Design

Motion in NURISK communicates system status, never delight for its own sake. Every animation either confirms an action succeeded, indicates loading, or orients the user during navigation.

| Interaction | Duration | Curve |
|---|---|---|
| Micro (icon toggle, chip select) | 120–150ms | Ease-out |
| Standard (button press, card tap) | 180–220ms | Ease-in-out (standard curve) |
| Page transition (push/pop) | 280–320ms | Emphasized decelerate (Material 3) |
| Bottom sheet / dialog | 260ms in / 200ms out | Emphasized decelerate / accelerate |
| Loading spinner rotation | 900ms loop | Linear |
| Pull-to-refresh | Elastic follow + 400ms snap-back | Spring (damping 0.8) |
| Expand / collapse (accordion, FAB menu) | 220ms | Ease-in-out, height + opacity |
| Hero shared-element (card → detail) | 320ms | Emphasized decelerate |
| Notification banner in/out | 240ms in / 180ms out | Ease-out / ease-in |
| Map marker drop / cluster | 260ms, slight overshoot 1.05x | Ease-out-back |
| Mission status change pulse | 600ms, 2 repeats | Ease-in-out, opacity 1→0.4→1 |

> **Rationale:** Durations are capped under 320ms system-wide because field users are often operating one-handed, outdoors, under time pressure — longer or more decorative animation reads as friction, not polish, in this context.

---

## 13. Accessibility

NURISK targets WCAG 2.1 AA as a hard minimum across all surfaces, including the public dashboard, which by definition serves users NURISK cannot vet or train.

### 13.1 Contrast

- Body text on background: minimum 4.5:1 (Dark Text #1A1D1F on white = 15.6:1, well above minimum).
- Large text (≥18px) and icons: minimum 3:1.
- Primary-600 on white = 6.1:1 — safe for body text and buttons. Primary-400 is reserved for dark-mode accents where it meets 4.5:1 against Surface Base.

### 13.2 Text Scaling

- All layouts must remain functional up to 200% system text scaling (Flutter `TextScaler` / browser zoom) without clipped or overlapping content.

### 13.3 Touch Targets

- Minimum 48×48dp for all interactive elements, 8dp minimum spacing between adjacent targets.

### 13.4 Color-Blindness Support

- Status is never conveyed by color alone — every semantic color pairs with an icon and/or text label (e.g. severity chips always show both color and the word "Kritis / Sedang / Aman").
- Palette validated against Deuteranopia and Protanopia simulation; Emergency Red and Safe Green are distinguishable by both lightness and shape-coded icon, not hue alone.

### 13.5 Keyboard & Screen Reader

- Full keyboard operability on the Laravel dashboard: visible focus ring (2px Primary-500), logical tab order, skip-to-content link.
- All icons carry semantic labels (Flutter `Semantics` widget / `aria-label`) — decorative icons are marked `aria-hidden`.
- Live regions (`aria-live`) used for incident feed updates and mission status changes so screen reader users receive real-time alerts.

---

## 14. Voice & Tone

NURISK speaks Bahasa Indonesia throughout the product. The voice is professional, calm, short, and action-oriented — the register of a competent field coordinator, not a customer-service chatbot and not a formal government memo.

### 14.1 Tone Principles

- Be brief. A confirmation dialog during an active incident should be readable in under 3 seconds.
- State the action, not the feeling. Prefer "Laporan terkirim" over "Terima kasih telah melapor!"
- Never use exclamation marks for errors or warnings — reserve them, sparingly, for positive confirmations only.
- Always tell the user what happens next, not just what happened.

### 14.2 Copy Examples

| Context | Example copy (Bahasa Indonesia) |
|---|---|
| Button — primary submit | Kirim Laporan |
| Button — destructive | Hapus Misi |
| Dialog — confirm destructive | Judul: "Hapus laporan ini?" · Isi: "Tindakan ini tidak dapat dibatalkan." · Tombol: Batal / Hapus |
| Success | Laporan berhasil dikirim ke tim operator. |
| Error (network) | Gagal mengirim. Periksa koneksi internet, lalu coba lagi. |
| Error (validation) | Lokasi wajib diisi sebelum mengirim laporan. |
| Warning | Data belum tersimpan. Yakin ingin keluar halaman ini? |
| Notification — mission update | Misi #A0231 telah ditugaskan kepada Anda. |
| Assessment workflow step | Langkah 2 dari 4: Unggah foto kondisi lapangan. |
| Offline banner | Anda sedang offline. Data akan tersinkron otomatis saat koneksi kembali. |

---

## 15. Flutter Theme Specification

```dart
final ThemeData nuriskLightTheme = ThemeData(
  useMaterial3: true,
  colorScheme: ColorScheme.fromSeed(
    seedColor: NuriskColors.primary600,
    brightness: Brightness.light,
    error: NuriskColors.emergencyRed,
  ),
  scaffoldBackgroundColor: NuriskColors.bgWhite,
  fontFamily: 'PlusJakartaSans',
  textTheme: const TextTheme(
    displayLarge: TextStyle(fontSize: 32, height: 1.25, fontWeight: FontWeight.w700),
    headlineLarge: TextStyle(fontSize: 28, height: 1.28, fontWeight: FontWeight.w700),
    titleLarge: TextStyle(fontSize: 22, height: 1.27, fontWeight: FontWeight.w600),
    bodyLarge: TextStyle(fontSize: 16, height: 1.5, fontWeight: FontWeight.w400),
    bodyMedium: TextStyle(fontSize: 14, height: 1.43, fontWeight: FontWeight.w400),
    labelSmall: TextStyle(fontSize: 11, height: 1.45, fontWeight: FontWeight.w600, letterSpacing: 0.6),
  ),
  elevatedButtonTheme: ElevatedButtonThemeData(
    style: ElevatedButton.styleFrom(
      backgroundColor: NuriskColors.primary600,
      foregroundColor: Colors.white,
      minimumSize: const Size(64, 48),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
    ),
  ),
  inputDecorationTheme: InputDecorationTheme(
    filled: true, fillColor: NuriskColors.primary50.withOpacity(0.3),
    border: OutlineInputBorder(borderRadius: BorderRadius.circular(4)),
    focusedBorder: OutlineInputBorder(
      borderRadius: BorderRadius.circular(4),
      borderSide: const BorderSide(color: NuriskColors.primary600, width: 1.5),
    ),
  ),
);

final ThemeData nuriskDarkTheme = nuriskLightTheme.copyWith(
  brightness: Brightness.dark,
  scaffoldBackgroundColor: const Color(0xFF121614),
  colorScheme: ColorScheme.fromSeed(
    seedColor: NuriskColors.primary400,
    brightness: Brightness.dark,
  ),
);
```

> **Rationale:** Using `ColorScheme.fromSeed` keeps NURISK aligned with Material 3's tonal-palette engine (so every derived surface, container, and state layer stays contrast-safe automatically) while still pinning the exact brand hex values from Section 4, avoiding drift between design and implementation.

---

## 16. Material 3 Theme Mapping

| Material 3 role | NURISK token |
|---|---|
| primary | Primary-600 (light) / Primary-400 (dark) |
| onPrimary | White / Neutral-900 |
| primaryContainer | Primary-50 (light) / Primary-800 (dark) |
| secondary | Information Blue |
| error | Emergency Red |
| surface | White / Surface Base (#121614) |
| surfaceVariant | Neutral-100 / Surface Elevated (#1B211D) |
| outline | Neutral-300 / #343A40 |

---

## 17. Dark Mode Rules

- Dark mode is the default for Command Center (extended monitoring sessions); light mode is the default for the public dashboard and citizen-facing mobile flows.
- Never pure black backgrounds — Surface Base is #121614 to reduce halation and eye strain on OLED displays during night operations.
- Primary-400 replaces Primary-600 as the accent in dark mode to preserve AA contrast against dark surfaces.
- Semantic colors (red/orange/green/blue) are used at their standard hue but tested at reduced saturation (-8%) in dark mode to avoid vibration against the dark surface.
- Elevation in dark mode is communicated by a lighter surface tint (per Material 3 tonal elevation) rather than by darker shadows, which are nearly invisible on dark backgrounds.

---

## 18. Usage Examples & Do / Don't

### 18.1 Do

- Use Primary-600 for the single primary action on a screen; everything else is Secondary or Tertiary.
- Pair every semantic color with an icon and text label.
- Keep dashboard KPI numbers in tabular figures so multi-row values align.
- Use the exact same status-chip vocabulary (color + icon + label) across mobile, dashboard, and public site.
- Test every new screen at 200% text scale before merging.

### 18.2 Don't

- Don't introduce a new accent color outside the documented semantic palette for a one-off feature.
- Don't use drop-shadow-heavy, glassmorphic, or neon-gradient styling anywhere in the product — this is the fastest way to make NURISK look like a fintech app.
- Don't animate anything beyond 320ms; anything longer reads as lag, not polish.
- Don't rely on hover-only interactions — a majority of NURISK users are on touch devices in the field.
- Don't use red for anything that is not an actual emergency, error, or destructive action.

---

## 19. Future Branding Expansion

As NURISK matures beyond LPBI NU Jawa Tengah toward potential national NU or inter-agency adoption, the design system is built to extend without a rebrand:

- A theming layer (already implied by the token architecture in Sections 4 and 15–17) allows a province-level accent color to be swapped in as a secondary brand layer while Primary-600 remains the national anchor color.
- The icon and component library is license-clean and documented well enough to hand to an external contracted dev team without a design handoff meeting.
- Illustration style is intentionally simple enough to be recreated in-house rather than requiring an ongoing external illustration contract.
- A future "NURISK for [Provinsi]" sub-brand can reuse the entire system by changing only the Primary hue seed and the wordmark suffix, per the `ColorScheme.fromSeed` approach in Section 15.

---

## 20. Governance & Document Ownership

This guideline is maintained by the LPBI NU Jawa Tengah product and design function. Any deviation from documented tokens (color, type, spacing, motion) requires sign-off, and all approved additions must be versioned back into this document so the Flutter app, Laravel dashboard, and public site never drift out of sync.

**Version 1.0 — July 2026.**
