import { useMemo, useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import {
    Users, Settings2, Fuel, ArrowRight, MapPin, Search,
    Banknote, ShieldCheck, CheckCircle2,
} from 'lucide-react';
import StorefrontLayout from '@/Layouts/StorefrontLayout';
import { useTranslations } from '@/hooks/useTranslations';
import { specLabels } from '@/lib/vehicleSpecs';

/**
 * Booking-first storefront landing (BAN-333).
 *
 * The search panel is the page's primary element — it overlaps the hero and
 * sits above the fleet, because a visitor here is trying to rent a car on
 * given dates, not read about us. Everything below it exists to support that
 * one action.
 *
 * Three things this page deliberately does NOT carry, all of which it used to:
 *   - four testimonials with invented names and quotes,
 *   - a hardcoded five-star rating and "2 Reviews" on every fleet card,
 *   - "2 Airports Served" / "7/7" stat blocks nothing measures.
 * The storefront is crawlable (BAN-262), so invented social proof is not a
 * placeholder — it is published claims about a real business. Their
 * translation keys stay in resources/lang/* (CLAUDE.md §4/§8: keys are added,
 * never removed); nothing renders them.
 */

const TODAY = () => new Date().toISOString().slice(0, 10);

function Eyebrow({ children, className = '' }) {
    return <p className={`eyebrow text-xs font-bold text-primary ${className}`}>{children}</p>;
}

/**
 * Short by design. The hero used to be 720px of photograph with the search
 * widget pushed below the fold on a laptop; here it is a band that names the
 * business and hands over to the search panel immediately.
 *
 * An owner-uploaded banner still wins when there is one (Settings → General),
 * which is the only reason heroImage is still threaded through — without an
 * upload the band paints itself from theme tokens instead of a stock photo.
 */
function Hero({ heroImage }) {
    const t = useTranslations();
    const image = heroImage?.desktop || heroImage?.mobile ? heroImage : null;

    return (
        <section className="relative overflow-hidden bg-foreground">
            {image && (
                <>
                    <div
                        className="hidden md:block absolute inset-0"
                        style={{ background: image.desktop ? `url(${image.desktop}) center/cover no-repeat` : undefined }}
                    />
                    <div
                        className="block md:hidden absolute inset-0"
                        style={{ background: image.mobile ? `url(${image.mobile}) center/cover no-repeat` : undefined }}
                    />
                    <div className="absolute inset-0 bg-foreground/70" />
                </>
            )}
            {!image && (
                <div
                    className="absolute inset-0"
                    style={{
                        background:
                            'radial-gradient(620px 420px at 78% 10%, hsl(var(--primary) / 0.44), transparent 62%),'
                            + ' radial-gradient(520px 380px at 4% 96%, hsl(var(--chart-2) / 0.16), transparent 68%)',
                    }}
                />
            )}

            <div className="relative container mx-auto px-4 pt-14 pb-24 md:pt-20 md:pb-32">
                <div className="max-w-2xl space-y-4">
                    <p className="eyebrow text-xs font-bold" style={{ color: 'hsl(var(--chart-2))' }}>
                        {t('hero_eyebrow', 'Location de voitures · Maroc')}
                    </p>
                    <h1 className="font-display text-background text-4xl sm:text-5xl md:text-6xl uppercase text-balance">
                        {t('hero_title', 'Louez une voiture, sans mauvaise surprise')}
                    </h1>
                    <p className="text-base md:text-lg leading-relaxed text-background/70 max-w-xl">
                        {t('hero_subtitle', "Assurance comprise, paiement à l'agence au retrait.")}
                    </p>
                </div>
            </div>

            <div
                className="absolute inset-x-0 bottom-0 h-[5px]"
                style={{ background: 'linear-gradient(100deg, hsl(var(--chart-2)) 0%, hsl(var(--primary)) 48%, hsl(var(--primary)) 100%)' }}
            />
        </section>
    );
}

/**
 * The page's primary action, overlapping the hero so it reads as the thing to
 * do rather than a filter bar under a banner.
 *
 * It hands off to the booking wizard rather than filtering in place: /reserve
 * already runs the real overlap query against `bookings` for the dates it is
 * given (RequestBookingController::create), so the cars it lists for a date
 * range are genuinely free. The fleet grid further down this page cannot make
 * that claim and does not try to.
 *
 * Submitting with nothing filled is allowed on purpose — it lands on the
 * wizard's full fleet, which is a reasonable answer to "show me the cars",
 * not a dead end.
 */
function SearchPanel({ places }) {
    const t = useTranslations();
    const [place, setPlace] = useState('');
    const [startDate, setStartDate] = useState('');
    const [endDate, setEndDate] = useState('');
    const today = TODAY();

    const submit = (e) => {
        e.preventDefault();
        const params = {};
        if (place) params.place = place;
        if (startDate) params.start_date = startDate;
        if (endDate) params.end_date = endDate;
        // The wizard filters on a full timestamp range; send its own defaults
        // so a date-only search means "the whole of those days".
        if (startDate && endDate) {
            params.start_time = '09:00';
            params.end_time = '18:00';
        }
        router.get(route('reserve.create'), params);
    };

    return (
        <section className="relative z-10 px-4">
            <div className="container mx-auto">
                <form
                    onSubmit={submit}
                    aria-label={t('search_panel_label', 'Rechercher une voiture')}
                    className="-mt-12 md:-mt-16 rounded-lg border border-border bg-card p-4 md:p-5 shadow-xl shadow-black/10"
                >
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1fr_auto] lg:items-end">
                        <div className="space-y-1.5">
                            <label htmlFor="search-place" className="eyebrow block text-[11px] font-bold text-muted-foreground">
                                {t('pickup_location_label', 'Lieu de prise en charge')}
                            </label>
                            <Select value={place} onValueChange={setPlace}>
                                <SelectTrigger id="search-place" className="h-12 relative ps-10">
                                    <MapPin className="absolute start-3 top-1/2 -translate-y-1/2 h-4 w-4 text-primary pointer-events-none" />
                                    <SelectValue placeholder={t('pickup_location_select', 'Lieux')} />
                                </SelectTrigger>
                                <SelectContent>
                                    {places.map((p) => (
                                        <SelectItem key={p.id} value={String(p.id)}>{p.name}</SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>

                        <div className="space-y-1.5">
                            <label htmlFor="search-start" className="eyebrow block text-[11px] font-bold text-muted-foreground">
                                {t('pickup_date_label', 'Date de prise en charge')}
                            </label>
                            <Input
                                id="search-start" type="date" className="h-12" min={today}
                                value={startDate}
                                onChange={(e) => {
                                    setStartDate(e.target.value);
                                    if (endDate && e.target.value && endDate < e.target.value) setEndDate('');
                                }}
                            />
                        </div>

                        <div className="space-y-1.5">
                            <label htmlFor="search-end" className="eyebrow block text-[11px] font-bold text-muted-foreground">
                                {t('dropoff_date_label', 'Date de restitution')}
                            </label>
                            <Input
                                id="search-end" type="date" className="h-12" min={startDate || today}
                                value={endDate}
                                onChange={(e) => setEndDate(e.target.value)}
                            />
                        </div>

                        <Button type="submit" size="lg" className="h-12 w-full lg:w-auto px-7 text-base">
                            <Search className="h-4 w-4" />
                            {t('search_see_cars', 'Voir les voitures')}
                        </Button>
                    </div>
                </form>
            </div>
        </section>
    );
}

/**
 * What a renter actually worries about before handing over a card number —
 * and here, that they will not have to. Every line is something the app
 * genuinely does: nothing takes payment online (booking_payment is off), the
 * agency approves each request before it becomes a booking.
 */
function Reassurance() {
    const t = useTranslations();
    const points = [
        {
            icon: Banknote,
            title: t('reassurance_1_title', "Paiement à l'agence"),
            desc: t('reassurance_1_desc', 'Aucun prélèvement en ligne. Vous réglez au retrait du véhicule.'),
        },
        {
            icon: ShieldCheck,
            title: t('reassurance_2_title', 'Assurance comprise'),
            desc: t('reassurance_2_desc', 'Comprise dans le prix, avec franchise indiquée au contrat.'),
        },
        {
            icon: CheckCircle2,
            title: t('reassurance_3_title', 'Demande sans engagement'),
            desc: t('reassurance_3_desc', "L'agence confirme la disponibilité avant toute réservation ferme."),
        },
    ];

    return (
        <section className="container mx-auto px-4 pt-9 pb-2">
            <div className="grid grid-cols-1 gap-7 md:grid-cols-3">
                {points.map(({ icon: Icon, title, desc }) => (
                    <div key={title} className="flex gap-3">
                        <Icon className="h-5 w-5 shrink-0 mt-0.5 text-primary" strokeWidth={1.9} />
                        <div className="space-y-1">
                            <h3 className="font-bold text-[15px]">{title}</h3>
                            <p className="text-sm text-muted-foreground leading-relaxed">{desc}</p>
                        </div>
                    </div>
                ))}
            </div>
        </section>
    );
}

function FleetCard({ vehicle, t }) {
    const { gearbox, fuel } = specLabels(vehicle, t);

    return (
        <article className="group flex flex-col overflow-hidden rounded-lg border border-border bg-card transition-colors hover:border-foreground/25">
            <div className="relative h-52 bg-muted">
                <img
                    src={vehicle.picture ? `/storage/upload/picture/${vehicle.picture}` : '/assets/images/client/default-car.jpg'}
                    alt={vehicle.name}
                    loading="lazy"
                    className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.03]"
                    onError={(e) => { e.target.src = '/assets/images/client/default-car.jpg'; }}
                />
                {vehicle.model && (
                    <span className="absolute top-3 start-3 rounded-full bg-foreground/85 px-2.5 py-1 text-xs font-bold text-background">
                        {vehicle.model}
                    </span>
                )}
            </div>

            <div className="flex flex-grow flex-col gap-3.5 p-4 pb-5">
                <div className="flex items-baseline justify-between gap-3">
                    <h3 className="font-display text-xl uppercase">{vehicle.name}</h3>
                    <p className="shrink-0 whitespace-nowrap">
                        <span className="font-display text-xl text-primary">{Number(vehicle.daily_rate).toFixed(0)}</span>
                        <span className="text-xs font-semibold text-muted-foreground"> Dh/{t('car_per_day', 'jour')}</span>
                    </p>
                </div>

                <div className="flex flex-wrap items-center gap-x-3 gap-y-1 text-[13px] font-semibold text-muted-foreground">
                    <span className="inline-flex items-center gap-1.5">
                        <Users className="h-3.5 w-3.5" /> {vehicle.number_of_seats ?? '—'} {t('car_seats', 'places')}
                    </span>
                    <span aria-hidden="true">·</span>
                    <span className="inline-flex items-center gap-1.5"><Settings2 className="h-3.5 w-3.5" /> {gearbox}</span>
                    <span aria-hidden="true">·</span>
                    <span className="inline-flex items-center gap-1.5"><Fuel className="h-3.5 w-3.5" /> {fuel}</span>
                </div>

                <div className="mt-auto flex gap-2 pt-1">
                    <Link href={route('reserve.create', { vehicle: vehicle.id })} className="flex-grow">
                        <Button className="w-full">{t('car_book_now', 'Réserver')}</Button>
                    </Link>
                    <Link href={route('client.details', vehicle.id)}>
                        <Button variant="outline" className="px-4">{t('car_view_details', 'Détails')}</Button>
                    </Link>
                </div>
            </div>
        </article>
    );
}

/**
 * The fleet, directly under the search panel.
 *
 * The heading counts the cars offered for rent — `available_for_rent`, which
 * means "we rent this car", not "this car is free on your dates". Nothing here
 * has checked a date, so the copy does not say "disponibles"; the wizard is
 * where availability is actually resolved.
 */
function Fleet({ vehicles, vehicleTypes }) {
    const t = useTranslations();
    const [typeFilter, setTypeFilter] = useState('all');

    // Only offer a chip for a type the fleet actually contains — a "Cabriolet"
    // filter that always yields nothing is worse than no filter.
    const usedTypes = useMemo(() => {
        const present = new Set(vehicles.map((v) => String(v.type)));
        return vehicleTypes.filter((vt) => present.has(String(vt.id)));
    }, [vehicles, vehicleTypes]);

    const shown = useMemo(
        () => (typeFilter === 'all' ? vehicles : vehicles.filter((v) => String(v.type) === typeFilter)),
        [vehicles, typeFilter],
    );

    return (
        <section id="fleet" className="container mx-auto px-4 py-12 md:py-14">
            <div className="mb-8 flex flex-col gap-5 md:flex-row md:items-end md:justify-between">
                <div className="space-y-2">
                    <Eyebrow>{t('fleet_eyebrow', 'Notre flotte')}</Eyebrow>
                    <h2 className="font-display text-3xl md:text-4xl uppercase">
                        {vehicles.length} {t('fleet_vehicles', 'véhicules')}
                    </h2>
                    <p className="text-sm text-muted-foreground">
                        {t('fleet_availability_note', "L'agence confirme la disponibilité pour vos dates.")}
                    </p>
                </div>

                {usedTypes.length > 1 && (
                    <div className="flex flex-wrap gap-2">
                        <Button
                            type="button" size="sm"
                            variant={typeFilter === 'all' ? 'default' : 'outline'}
                            onClick={() => setTypeFilter('all')}
                            aria-pressed={typeFilter === 'all'}
                        >
                            {t('fleet_filter_all', 'Tous')}
                        </Button>
                        {usedTypes.map((vt) => (
                            <Button
                                key={vt.id} type="button" size="sm"
                                variant={typeFilter === String(vt.id) ? 'default' : 'outline'}
                                onClick={() => setTypeFilter(String(vt.id))}
                                aria-pressed={typeFilter === String(vt.id)}
                            >
                                {vt.type}
                            </Button>
                        ))}
                    </div>
                )}
            </div>

            {shown.length === 0 ? (
                <p className="rounded-lg border border-dashed border-border py-12 text-center text-muted-foreground">
                    {t('fleet_empty', 'Aucun véhicule à afficher pour le moment.')}
                </p>
            ) : (
                <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    {shown.map((v) => <FleetCard key={v.id} vehicle={v} t={t} />)}
                </div>
            )}
        </section>
    );
}

/** Three steps, describing what the app really does — request, agency
 *  approval, pick-up and pay. No step promises an instant confirmation. */
function HowItWorks() {
    const t = useTranslations();
    const steps = [
        { n: '01', title: t('how_1_title', 'Choisissez vos dates'), body: t('how_1_body', 'Indiquez le lieu et les dates : seules les voitures libres sur cette période vous sont proposées.') },
        { n: '02', title: t('how_2_title', 'Envoyez votre demande'), body: t('how_2_body', "Quelques informations sur le conducteur suffisent. Aucun paiement n'est demandé à cette étape.") },
        { n: '03', title: t('how_3_title', "Retirez à l'agence"), body: t('how_3_body', "L'agence confirme la disponibilité, puis vous réglez au retrait du véhicule.") },
    ];

    return (
        <section className="border-y border-border bg-card">
            <div className="container mx-auto px-4 py-14">
                <div className="mb-10 space-y-2">
                    <Eyebrow>{t('how_eyebrow', 'Comment ça marche')}</Eyebrow>
                    <h2 className="font-display text-3xl md:text-4xl uppercase">{t('how_title', 'Trois étapes')}</h2>
                </div>
                <div className="grid grid-cols-1 gap-10 md:grid-cols-3">
                    {steps.map(({ n, title, body }) => (
                        <div key={n} className="space-y-3">
                            <p className="font-display text-3xl text-primary">{n}</p>
                            <h3 className="text-lg font-bold">{title}</h3>
                            <p className="text-[15px] leading-relaxed text-muted-foreground">{body}</p>
                        </div>
                    ))}
                </div>
                <div className="mt-10">
                    <Link href={route('reserve.create')}>
                        <Button size="lg" className="h-12 px-7 text-base">
                            {t('how_cta', 'Commencer ma réservation')} <ArrowRight className="ms-1 h-4 w-4" />
                        </Button>
                    </Link>
                </div>
            </div>
        </section>
    );
}

function Landing({ vehicles = [], vehicleTypes = [], places = [], heroImage = null }) {
    const t = useTranslations();

    return (
        <>
            <Head title={t('landing_page_title', 'Location de voitures au Maroc')} />
            <Hero heroImage={heroImage} />
            <SearchPanel places={places} />
            <Reassurance />
            <Fleet vehicles={vehicles} vehicleTypes={vehicleTypes} />
            <HowItWorks />
        </>
    );
}

Landing.layout = (page) => <StorefrontLayout>{page}</StorefrontLayout>;
export default Landing;
