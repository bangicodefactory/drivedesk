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

Six of the seven seeded vehicles have one. The Mercedes does not — see below.

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
| `peugeot-208.jpg` | [`ZhEnFcHO0es`](https://unsplash.com/photos/ZhEnFcHO0es) | black VW Golf GTI at a beach | no — right body style only |
| `ford-transit.jpg` | [`XDw-MK_Kp6Q`](https://unsplash.com/photos/XDw-MK_Kp6Q) | white passenger van at an airport terminal | no — right body style only |

Every file was opened and looked at before being committed. The first attempt at
this set was assembled from search-result captions without opening anything and
got three of seven wrong — a police-spec Ford Explorer filed as a RAV4, a rally
car in racing livery filed as a Clio, both of which reached the storefront.
**Look at the file; do not trust the caption.**

Two portrait sources (`volkswagen-t-roc`, `dacia-duster`) were cropped to
landscape, and this is subtler than it looks. The landing card's box is
`h-52 w-full` — about **2.3:1** at a three-column desktop grid — and the booking
wizard's is `pt-[56.25%]`, 1.78:1. `object-cover` scales the image to fill the
box and throws the overflow away, so a source *taller* than the box loses its
top and bottom, not its sides. Making a crop taller to "fit more car in"
therefore does the opposite. Both fixtures here are at least 1.9:1 and lose
under 10% top and bottom; the car survives whole in each. Check a crop by
rendering the band the browser will actually show, not by looking at the file.

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

### The Mercedes has no photo

Every Mercedes candidate was a dealership or press asset — a Land Cruiser press
shot, a GLE on a black studio backdrop with a dealer plate reading
"SomMotorCar | Mercedes-Benz" — except one: a genuine amateur photograph of a
Mercedes-AMG G 63 in Cape Town. That one is unusable for a different reason.
The car fills its frame vertically, so the ~2.3:1 card crops its roof clean off.
It was committed before that was checked, and the rendered band showed a
decapitated car.

A neutral placeholder makes no claim about the vehicle. A roofless G-Wagen
labelled "Mercedes GLE" would make two wrong ones. So the placeholder stays
until a landscape photograph of a Mercedes SUV turns up, or the card gets an
explicit aspect ratio.

### The Duster is the clearest illustration of the trade-off

A much cleaner
side-profile shot was available — whole car, badge readable, perfect light — but
it carried a French dummy plate (`DD-000-DD`), which is the advertising
placeholder format, alongside press-shoot lighting. The scruffier forest photo
now in use is unmistakably somebody's own photograph, so it was preferred over
the better-looking one. Provenance beat polish, deliberately.

**Per-model searches work far better than category ones.**
`unsplash.com/s/photos/dacia-duster` is how four of these were found;
`unsplash.com/s/photos/suv` is where all three rejects came from.

## Registration plates are visible

Every one of these photographs shows a legible number plate, some more than
others. The Unsplash Licence covers copyright; it does not clear a third party's
personal-data interest in a plate, which is the same reasoning used above to
reject press assets — a licence cannot grant rights the uploader never held.

They are left as published, because that is how the photographers uploaded them
and plates are visible throughout stock photography. Blurring them before this
sits on a commercial site is a reasonable call and a small change; it has not
been made unilaterally. Note also that plate format cuts both ways as a
heuristic: a *dummy* plate suggests a press asset, a *real* one suggests
somebody's own photograph and a real person's vehicle.

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
