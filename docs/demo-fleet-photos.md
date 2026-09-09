# Demo fleet photos

> Lives in `docs/`, not under `public/`. It was in the docroot at first, which
> meant `https://drivedesk.ma/images/fleet/CREDITS.md` served this file to
> anyone who asked — publishing, on the commercial site, the note that the
> photo advertised as a Peugeot 208 is a VW Golf.

Stock photography for the vehicles `DevDataSeeder` creates. The files live in
`database/seeders/fixtures/fleet/` and are written onto the public disk at seed
time. These are **demo fixtures**, not a customer's real fleet:
`ensureFleetPhoto()` only fills in a picture that is missing, so a real photo
uploaded through the admin is never overwritten by the nightly
`demo:seed --if-demo`.

All seven seeded vehicles now have one.

## Licence

From [Unsplash](https://unsplash.com), under the
[Unsplash Licence](https://unsplash.com/license): free for commercial use, no
attribution required. The table records provenance anyway, so anyone can
re-check where a file came from.

## What is here

| File | Unsplash photo | What it actually shows | Matches the model? |
|---|---|---|---|
| `toyota-rav4.jpg` | [`MN0-x2hDNrc`](https://unsplash.com/photos/MN0-x2hDNrc) | Toyota RAV4 hybrid at a forest campsite | **yes** |
| `renault-clio.jpg` | [`ZRuo9qFprXk`](https://unsplash.com/photos/ZRuo9qFprXk) | current-generation Renault Clio, rooftop car park | **yes** |
| `dacia-duster.jpg` | [`m3r_pSJSQ6o`](https://unsplash.com/photos/m3r_pSJSQ6o) | orange Dacia Duster in a forest, shot through grass | **yes** |
| `volkswagen-t-roc.jpg` | [`qLaYm1UZ8sE`](https://unsplash.com/photos/qLaYm1UZ8sE) | white VW T-Roc on an autumn forest road | **yes** |
| `mercedes-gle.jpg` | [`PtJDCD4fTI4`](https://unsplash.com/photos/PtJDCD4fTI4) | Mercedes-AMG G 63 parked in Cape Town | no — right marque, wrong model |
| `peugeot-208.jpg` | [`ZhEnFcHO0es`](https://unsplash.com/photos/ZhEnFcHO0es) | black VW Golf GTI at a beach | no — right body style only |
| `ford-transit.jpg` | [`XDw-MK_Kp6Q`](https://unsplash.com/photos/XDw-MK_Kp6Q) | white passenger van at an airport terminal | no — right body style only |

Every file was opened and looked at before being committed. The first attempt at
this set was assembled from search-result captions without opening anything and
got three of seven wrong — a police-spec Ford Explorer filed as a RAV4, a rally
car in racing livery filed as a Clio, both of which reached the storefront.
**Look at the file; do not trust the caption.**

Three portrait sources (`volkswagen-t-roc`, `mercedes-gle`, `dacia-duster`) were
cropped to landscape, because the card's image box is `h-52 w-full` — roughly
2:1 — and `object-cover` on a tall source cuts the car in half.

## The rule these were chosen by

Unsplash's broad category searches (`suv`, `car`) return a lot of **manufacturer
marketing assets** that contributors have uploaded: press photographs and studio
renders with press-plate registrations, flawless lighting and staged
compositions. Unsplash's licence cannot grant rights an uploader never held, so
a manufacturer press image re-uploaded by a third party is still the
manufacturer's.

Three candidates were rejected on that basis before this set settled: a Toyota
Land Cruiser press shot offered as a Mercedes, a Kia Sorento press shot as a
T-Roc, a Hyundai Tucson studio render as a Duster.

The Duster is the clearest illustration of the trade-off. A much cleaner
side-profile shot was available — whole car, badge readable, perfect light — but
it carried a French dummy plate (`DD-000-DD`), which is the advertising
placeholder format, alongside press-shoot lighting. The scruffier forest photo
now in use is unmistakably somebody's own photograph, so it was preferred over
the better-looking one. Provenance beat polish, deliberately.

**Per-model searches work far better than category ones.**
`unsplash.com/s/photos/dacia-duster` is how four of these were found;
`unsplash.com/s/photos/suv` is where all three rejects came from.

## If you want better

In rough order of preference:

1. **Photograph the actual cars.** A phone in good light beats stock, and it is
   the only option that is unambiguously yours.
2. **A paid stock licence** (Getty, Adobe Stock), where the chain of rights is
   contractual rather than inferred.
3. **More Unsplash**, per-model, opening every file before committing it.

An admin replacing any of these through Vehicles → Edit is supported and
permanent — the seeder will not touch a picture it did not put there.

## Not sourced from a competitor

An earlier request was to take these from a real rental agency's website. That
was declined: those photographs are that business's property, and using them to
market this product would be commercial use of someone else's work without a
licence. The same reasoning is why the manufacturer assets above were dropped
even though they arrived through a licensed channel.
