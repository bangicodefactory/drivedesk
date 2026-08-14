# DriveDesk sales collateral

Client-facing pitch decks (EN / FR / AR) and an internal salesperson's handbook (EN),
built from a scan of this codebase plus live screenshots of https://drivedesk.ma.

## Deliverables

| File | What it is | Audience |
| --- | --- | --- |
| `DriveDesk-Sales-Deck-EN.pdf` | 16-slide pitch deck, 16:9 | Prospect (rental agency owner) |
| `DriveDesk-Presentation-Commerciale-FR.pdf` | Same deck, French | Prospect |
| `DriveDesk-Presentation-AR.pdf` | Same deck, Arabic, full RTL | Prospect |
| `DriveDesk-Salesperson-Handbook-EN.pdf` | 32-page training manual | Internal — the salesperson |

The handbook is **internal only**. Its §9 ("What you must not promise") lists everything
the marketing copy overstates. Do not send it to a prospect.

## Sources

- `deck-en.html`, `deck-fr.html`, `deck-ar.html` — slides, sharing `_deck.css`
- `training-en.html` — handbook, using `_shared.css`
- `screenshots/` — captures from the live demo tenant, taken 2026-08-10
  - `en-*` admin panel in English · `fr-*` French · `ar-*` Arabic (RTL)

## Regenerating the PDFs

Edit the HTML, then re-render with headless Chrome. Output paths must be absolute
and use forward slashes.

```bash
cd docs/sales
CHROME="/c/Program Files/Google/Chrome/Application/chrome.exe"
BASE="C:/Users/<you>/…/rentcar/docs/sales"
URL="file:///C:/Users/<you>/…/rentcar/docs/sales"

for pair in \
  "deck-en:DriveDesk-Sales-Deck-EN" \
  "deck-fr:DriveDesk-Presentation-Commerciale-FR" \
  "deck-ar:DriveDesk-Presentation-AR" \
  "training-en:DriveDesk-Salesperson-Handbook-EN"; do
  src="${pair%%:*}"; out="${pair##*:}"
  "$CHROME" --headless=new --disable-gpu --no-pdf-header-footer \
    --virtual-time-budget=25000 \
    --print-to-pdf="$BASE/$out.pdf" "$URL/$src.html"
done
```

Page geometry lives in the `@page` rules: `297mm × 167mm` (16:9) for decks, A4 for the
handbook.

## Known gaps to close before this ships

1. **Pricing is a placeholder.** The handbook's §10 has an empty commercial-terms box.
   Fill it from management before anyone quotes a number.
2. **The demo tenant has no traffic violations seeded.** That module is the strongest
   differentiator and its list is empty. Either seed a few rows or have the salesperson
   create one live during the demo (the handbook says to do the latter).
3. **Demo invoice data lists `stripe` and `paypal` as payment methods.** Neither
   integration exists. A prospect who looks closely will ask, and the honest answer
   contradicts what's on screen. Worth re-seeding those rows to cash / bank_transfer /
   card / cheque.
4. **Booking payment statuses render in French in the English UI** (`Payé`,
   `Impayé`, `Partiellement Payé`) because they are stored values, not translation keys.
   Cosmetic, but visible in an English demo.

## Accuracy

Every product claim in these documents was checked against the code in this repo,
not against the marketing site. Where the marketing site overstates something —
"14 languages", online payments, coupons, multi-branch — the handbook says so
explicitly and gives the salesperson a safe alternative phrasing.
