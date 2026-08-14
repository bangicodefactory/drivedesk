# DriveDesk promo video (Remotion)

A ~67-second promotional video for DriveDesk, built in [Remotion](https://remotion.dev)
(React → real MP4). Same content in three languages, driven off one codebase.

| Composition | Output | Language |
| --- | --- | --- |
| `PromoEN` | `out/DriveDesk-Promo-EN.mp4` | English |
| `PromoFR` | `out/DriveDesk-Promo-FR.mp4` | French |
| `PromoAR` | `out/DriveDesk-Promo-AR.mp4` | Arabic (RTL) |

1920×1080, 30 fps, 2020 frames.

## Running it

```bash
cd docs/sales/video
npm install

npm run dev          # Remotion Studio — scrub the timeline, edit live
npm run render       # English
npm run render:fr    # French
npm run render:ar    # Arabic
npm run render:all   # all three, in sequence
```

Every script runs `sync-shots` first, which copies `../screenshots` into
`public/shots` — Remotion can only serve assets from `public/`, and the
screenshots are shared with the decks and the handbook. `public/shots` is
gitignored so they aren't committed twice.

To check a single moment without rendering the whole thing:

```bash
npx remotion still src/index.jsx PromoEN out/frame.png --frame=1450
```

## How it's put together

```
src/
  index.jsx        registerRoot
  Root.jsx         the three compositions (one per language)
  Promo.jsx        the master timeline — a <Series> of scenes
  theme.js         colours, fonts, and SCENES (the running order + lengths)
  copy.js          every word on screen, one object per language
  components/
    ui.jsx         Scene / Title / Shot / Callout / SignatureCard / Wordmark …
  scenes/
    index.jsx      the eleven scenes
scripts/
  sync-shots.mjs   copies ../screenshots → public/shots before every build
public/shots/      (generated, gitignored)
```

**Scene lengths live in `theme.js`.** `SCENES` is the running order; the
composition duration is the sum, so a scene can be lengthened or cut without
recalculating anything.

**Text lives in `copy.js`.** Nothing is hard-coded in a scene. Adding a fourth
language means adding a fourth object and a fourth `<Composition>`.

**Running order:** intro → the problem → dashboard → planning → fleet &
customers → contracts + e-signature → money & VAT → traffic fines → languages →
white-label → close.

## Things worth knowing before you edit

- **The signature is drawn, not filmed.** `SignatureCard` animates SVG paths via
  `stroke-dashoffset`. The screenshot of the real signature list was illegible at
  this size. Timing is the `dur` field on each stroke, in frames.
- **Screenshots are cropped, not re-taken.** `<Shot crop={0.173} />` trims the app
  sidebar off the left of the capture. If you re-take screenshots at a different
  window width, that number needs to change.
- **The dashboard callouts are positioned by percentage** over the KPI cards
  (`x={14 + i * 24.7}%`, `y={146}`). They will drift if the dashboard screenshot
  is replaced.
- **Fonts are system fonts** (Segoe UI / Tahoma), deliberately — so a render never
  depends on Google Fonts being reachable, and so Arabic renders correctly.
- **The dashboard shot changes per language** (`DASHBOARD_SHOT` in `copy.js`), so
  the UI in the screenshot matches the words on top of it.

## No soundtrack

The video renders **silent** — there's no licensed track to ship in the repo.
To add one: drop an MP3 at `public/music.mp3` and uncomment the `<Audio>` block
at the bottom of `src/Promo.jsx` (it already fades out over the last two seconds).
Around 67 seconds of music is needed.

## Accuracy

The copy follows the same rule as the deck and the handbook: only claims that are
backed by something that actually exists in the product. Specifically **not**
claimed anywhere in the video — online/card payment, coupons, multi-branch,
"14 languages", a mobile app, or reminder emails. See
`../DriveDesk-Salesperson-Handbook-EN.pdf` §9.
