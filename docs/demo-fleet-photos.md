# Demo fleet photos

> Lives in `docs/`, not under `public/`. It was in the docroot at first,
> which meant `https://drivedesk.ma/images/fleet/CREDITS.md` served this
> file to anyone who asked -- publishing, on the commercial site, the note
> that the photo advertised as a Peugeot 208 is a VW Golf.

Stock photography for the vehicles `DevDataSeeder` creates. The files live
in `database/seeders/fixtures/fleet/`. These are **demo
fixtures**, not a customer's real fleet — `ensureFleetPhoto()` only fills in a
picture that is missing, so a real photo uploaded through the admin is never
overwritten by the nightly `demo:seed --if-demo`.

Four of the seven seeded vehicles have a photo. Three deliberately do not, and
keep the existing default placeholder. Why is below.

## Licence

From [Unsplash](https://unsplash.com), under the
[Unsplash Licence](https://unsplash.com/license): free for commercial use, no
attribution required. The table below records provenance anyway, so anyone can
re-check where a file came from.

## What is here

| File | Unsplash photo | What it actually shows | Matches the model? |
|---|---|---|---|
| `toyota-rav4.jpg` | [`MN0-x2hDNrc`](https://unsplash.com/photos/MN0-x2hDNrc) | Toyota RAV4 hybrid at a forest campsite | **yes** |
| `renault-clio.jpg` | [`ZRuo9qFprXk`](https://unsplash.com/photos/ZRuo9qFprXk) | current-generation Renault Clio on a rooftop car park | **yes** |
| `peugeot-208.jpg` | [`ZhEnFcHO0es`](https://unsplash.com/photos/ZhEnFcHO0es) | black VW Golf GTI at a beach | no — right body style only |
| `ford-transit.jpg` | [`XDw-MK_Kp6Q`](https://unsplash.com/photos/XDw-MK_Kp6Q) | white passenger van at an airport terminal | no — right body style only |

Every one of these was opened and looked at before being committed. The first
attempt at this set was assembled from search-result text descriptions and got
three of seven wrong — a police-spec Ford Explorer filed as a RAV4, a rally car
in racing livery filed as a Clio. Look at the file; do not trust the caption.

## Why three vehicles have no photo

Dacia Duster, Mercedes GLE and Volkswagen T-Roc are on the default placeholder.

Candidates for all three came back from Unsplash's generic `suv` search, and on
inspection each was a **manufacturer marketing asset** rather than the amateur
photography Unsplash is built on: a Toyota Land Cruiser press shot filed as a
Mercedes, a Kia Sorento press shot filed as a T-Roc, and a Hyundai Tucson studio
render filed as a Duster — all with press-plate registrations, studio lighting
and staged compositions.

Unsplash's licence cannot grant rights an uploader never held. A manufacturer
press image re-uploaded by a third party is still the manufacturer's, and
putting one on a live commercial site is the exact risk that sending this work
to Unsplash was meant to avoid. So they were dropped rather than shipped.

Nothing is broken by their absence: the storefront already falls back to
`/assets/images/client/default-car.jpg`.

## Filling the gaps

In rough order of preference:

1. **Photograph the actual cars.** A phone in good light beats stock, and it is
   the only option that is unambiguously yours.
2. **A paid stock licence** (Getty, Adobe Stock) where the chain of rights is
   contractual rather than inferred.
3. **More Unsplash**, per-model rather than by category, opening each file
   before committing it. `unsplash.com/s/photos/<make>-<model>` works better
   than `suv` or `car` — that is how the RAV4 and Clio here were found.

## Not sourced from a competitor

An earlier request was to take these from a real rental agency's website. That
was declined: those photographs are that business's property, and using them to
market this product would be commercial use of someone else's work without a
licence. The same reasoning is why the three manufacturer assets above were
dropped even though they arrived through a licensed channel.
