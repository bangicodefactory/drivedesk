import { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import {
    Car, Fuel, Settings2, Wrench, Tag, Users, Calendar, Gauge, MapPin,
    ShieldCheck, Banknote, IdCard, Droplet, ArrowRight,
} from 'lucide-react';
import StorefrontLayout from '@/Layouts/StorefrontLayout';
import { useTranslations } from '@/hooks/useTranslations';
import { specLabels } from '@/lib/vehicleSpecs';

/**
 * Public vehicle detail page (BAN-333).
 *
 * What this page used to carry, on a page any visitor can reach -- /car/{id}
 * is not itself in sitemap.xml, but /landing is and links straight to it:
 *   - two reviews with invented names and lorem-ipsum bodies,
 *   - a hardcoded five-star rating and "2 Reviews" on every card,
 *   - a seven-row "Price Table (by day of the week)" printing the same
 *     daily_rate seven times, for a per-weekday pricing feature that does
 *     not exist,
 *   - every string hardcoded in English, on a storefront whose default
 *     public locale is French (CLAUDE.md §5: SPA copy goes through
 *     useTranslations, always).
 * All four are gone. The review keys stay in resources/lang/* (§4/§8).
 *
 * The inline booking form is gone too, and that one is a deliberate
 * consolidation rather than a deletion: it posted to booking.store_request
 * with a strictly smaller field set than /reserve sends to the same endpoint
 * — which is why age, nationality, driving_experience and passengers are all
 * nullable there. The booking card now hands the car, the location and the
 * dates to the wizard (BAN-333 prefill), so a request created from this page
 * goes through more validation than before, not less.
 */

const TODAY = () => new Date().toISOString().slice(0, 10);

function pictureUrl(picture) {
    // /storage/upload/picture/ — the path every other caller uses
    // (Landing, the wizard, the confirmation). This page had /storage/${picture}
    // and served a broken image for the hero and every similar car.
    return picture ? `/storage/upload/picture/${picture}` : '/assets/images/client/default-car.jpg';
}

function daysBetween(start, end) {
    if (!start || !end) return 0;
    const diff = Math.round((new Date(end) - new Date(start)) / 86400000);
    return diff > 0 ? diff : 0;
}

function SpecGrid({ car, t }) {
    const { gearbox, fuel } = specLabels(car, t);
    const specs = [
        { icon: Car, label: t('spec_type', 'Type'), value: car.types?.type ?? null },
        { icon: Calendar, label: t('spec_year', 'Année'), value: car.first_registration_year ?? car.model ?? null },
        { icon: Settings2, label: t('spec_gearbox', 'Boîte'), value: car.gearbox ? gearbox : null },
        { icon: Fuel, label: t('spec_fuel', 'Carburant'), value: car.fuel_type ? fuel : null },
        { icon: Users, label: t('spec_seats', 'Places'), value: car.number_of_seats ?? null },
        { icon: Gauge, label: t('spec_mileage', 'Kilométrage'), value: car.kilometers ? `${Number(car.kilometers).toLocaleString('fr-MA')} km` : null },
        { icon: Wrench, label: t('spec_engine', 'Moteur'), value: car.engine_type ?? null },
        { icon: Tag, label: t('spec_model', 'Modèle'), value: car.model ?? null },
    ].filter((s) => s.value !== null && s.value !== '');

    if (specs.length === 0) return null;

    return (
        <section className="rounded-lg border border-border bg-card p-5">
            <h2 className="font-display text-2xl uppercase mb-4">{t('detail_specs_title', 'Caractéristiques')}</h2>
            <dl className="grid grid-cols-2 gap-4 sm:grid-cols-4">
                {specs.map(({ icon: Icon, label, value }) => (
                    <div key={label} className="flex items-start gap-2.5">
                        <Icon className="mt-0.5 h-4 w-4 shrink-0 text-primary" strokeWidth={1.8} />
                        <div>
                            <dt className="text-xs text-muted-foreground">{label}</dt>
                            <dd className="text-sm font-semibold">{value}</dd>
                        </div>
                    </div>
                ))}
            </dl>
        </section>
    );
}

/**
 * Sourced line by line from the rental agreement this client actually prints
 * on the contract (config/clients/<client>.php → terms.rental_agreement,
 * articles 2, 4, 6 and 7). Nothing here is a marketing claim someone invented
 * for a landing page; if the agreement changes, this is the list to revisit.
 */
function Conditions({ t }) {
    const items = [
        { icon: ShieldCheck, text: t('detail_condition_insurance', 'Assurance tous risques comprise, avec une franchise à votre charge indiquée au contrat.') },
        { icon: IdCard, text: t('detail_condition_driver', 'Conducteur de 21 ans minimum, titulaire du permis depuis 2 ans au moins. Pièce d’identité et permis exigés à la signature.') },
        { icon: Banknote, text: t('detail_condition_deposit', 'Une caution peut être demandée ; elle est restituée au retour, selon l’état du véhicule.') },
        { icon: Droplet, text: t('detail_condition_fuel', 'Restitution au lieu et à l’heure convenus, dans le même état et avec le même niveau de carburant.') },
    ];

    return (
        <section className="rounded-lg border border-border bg-card p-5">
            <h2 className="font-display text-2xl uppercase mb-4">{t('detail_conditions_title', 'Conditions de location')}</h2>
            <ul className="space-y-3">
                {items.map(({ icon: Icon, text }) => (
                    <li key={text} className="flex gap-2.5">
                        <Icon className="mt-0.5 h-4 w-4 shrink-0 text-primary" strokeWidth={1.8} />
                        <span className="text-sm leading-relaxed text-muted-foreground">{text}</span>
                    </li>
                ))}
            </ul>
            <p className="mt-4 border-t border-border pt-3 text-xs text-muted-foreground">
                {t('detail_conditions_note', 'Extrait des conditions générales de location remises et signées au retrait.')}
            </p>
        </section>
    );
}

/**
 * The page's action. It collects the two things you decide while looking at a
 * particular car — where and when — and hands them to /reserve along with the
 * car itself, so the wizard opens on the dates step with all three already
 * filled in.
 */
function BookingCard({ car, places, t }) {
    const [place, setPlace] = useState('');
    const [startDate, setStartDate] = useState('');
    const [endDate, setEndDate] = useState('');
    const today = TODAY();

    const rate = Number(car.daily_rate ?? 0);
    const days = daysBetween(startDate, endDate);
    const total = days * rate;

    const submit = (e) => {
        e.preventDefault();
        const params = { vehicle: car.id };
        if (place) params.place = place;
        if (startDate) params.start_date = startDate;
        if (endDate) params.end_date = endDate;
        if (startDate && endDate) {
            params.start_time = '09:00';
            params.end_time = '18:00';
        }
        router.get(route('reserve.create'), params);
    };

    return (
        <form
            onSubmit={submit}
            aria-label={t('detail_book_title', 'Réserver ce véhicule')}
            className="rounded-lg border border-border bg-card p-5 lg:sticky lg:top-24"
        >
            <p className="mb-4">
                <span className="font-display text-3xl text-primary">{rate.toFixed(0)}</span>
                <span className="text-sm font-semibold text-muted-foreground"> Dh / {t('car_per_day', 'jour')}</span>
            </p>

            <div className="space-y-3">
                <div className="space-y-1.5">
                    <label htmlFor="detail-place" className="eyebrow block text-[11px] font-bold text-muted-foreground">
                        {t('pickup_location_label', 'Lieu de prise en charge')}
                    </label>
                    <Select value={place} onValueChange={setPlace}>
                        <SelectTrigger id="detail-place" className="h-11 relative ps-9">
                            <MapPin className="absolute start-3 top-1/2 -translate-y-1/2 h-4 w-4 text-primary pointer-events-none" />
                            <SelectValue placeholder={t('pickup_location_select', 'Lieux')} />
                        </SelectTrigger>
                        <SelectContent>
                            {places.map((p) => <SelectItem key={p.id} value={String(p.id)}>{p.name}</SelectItem>)}
                        </SelectContent>
                    </Select>
                </div>

                <div className="grid grid-cols-2 gap-3">
                    <div className="space-y-1.5">
                        <label htmlFor="detail-start" className="eyebrow block text-[11px] font-bold text-muted-foreground">
                            {t('pickup_date_label', 'Date de prise en charge')}
                        </label>
                        <Input
                            id="detail-start" type="date" className="h-11" min={today} value={startDate}
                            onChange={(e) => {
                                setStartDate(e.target.value);
                                if (endDate && e.target.value && endDate <= e.target.value) setEndDate('');
                            }}
                        />
                    </div>
                    <div className="space-y-1.5">
                        <label htmlFor="detail-end" className="eyebrow block text-[11px] font-bold text-muted-foreground">
                            {t('dropoff_date_label', 'Date de restitution')}
                        </label>
                        <Input
                            id="detail-end" type="date" className="h-11" min={startDate || today} value={endDate}
                            onChange={(e) => setEndDate(e.target.value)}
                        />
                    </div>
                </div>
            </div>

            <div className="mt-4 border-t border-border pt-4">
                {days > 0 ? (
                    <>
                        <div className="flex items-center justify-between text-sm text-muted-foreground">
                            <span>
                                {days} {days > 1 ? t('detail_days', 'jours') : t('detail_day', 'jour')} × {rate.toFixed(0)} Dh
                            </span>
                            <span>{total.toFixed(0)} Dh</span>
                        </div>
                        <div className="mt-2 flex items-baseline justify-between">
                            <span className="font-bold">{t('detail_total_estimated', 'Total estimé')}</span>
                            <span className="font-display text-2xl">{total.toFixed(0)} Dh</span>
                        </div>
                    </>
                ) : (
                    <p className="text-sm text-muted-foreground">
                        {t('detail_pick_dates', 'Choisissez vos dates pour voir le total.')}
                    </p>
                )}
            </div>

            <Button type="submit" size="lg" className="mt-4 h-12 w-full text-base">
                {t('car_book_now', 'Réserver')} <ArrowRight className="ms-1 h-4 w-4" />
            </Button>

            <p className="mt-3 text-xs leading-relaxed text-muted-foreground">
                {t('detail_price_note', "Aucun paiement en ligne. Le montant définitif est confirmé par l'agence avant le retrait.")}
            </p>
        </form>
    );
}

function SimilarCar({ car, t }) {
    const { gearbox, fuel } = specLabels(car, t);

    return (
        <article className="overflow-hidden rounded-lg border border-border bg-card">
            <Link href={route('client.details', car.id)}>
                <img
                    src={pictureUrl(car.picture)}
                    alt={car.name}
                    loading="lazy"
                    className="h-40 w-full object-cover"
                    onError={(e) => { e.target.src = '/assets/images/client/default-car.jpg'; }}
                />
            </Link>
            <div className="space-y-2 p-4">
                <div className="flex items-baseline justify-between gap-3">
                    <h3 className="font-display text-lg uppercase">
                        <Link href={route('client.details', car.id)} className="hover:text-primary transition-colors">
                            {car.name}
                        </Link>
                    </h3>
                    <p className="shrink-0 whitespace-nowrap">
                        <span className="font-display text-lg text-primary">{Number(car.daily_rate ?? 0).toFixed(0)}</span>
                        <span className="text-xs font-semibold text-muted-foreground"> Dh/{t('car_per_day', 'jour')}</span>
                    </p>
                </div>
                <p className="text-[13px] font-semibold text-muted-foreground">
                    {car.number_of_seats ?? '—'} {t('car_seats', 'places')} · {gearbox} · {fuel}
                </p>
            </div>
        </article>
    );
}

function CarDetails({ car, similarCars = [], places = [] }) {
    const t = useTranslations();

    return (
        <>
            <Head title={car.name} />

            <div className="container mx-auto px-4 py-6">
                <nav aria-label={t('breadcrumb', 'Fil d’Ariane')} className="flex items-center gap-2 text-[13px] text-muted-foreground">
                    <Link href={route('client.home')} className="hover:text-foreground transition-colors">
                        {t('nav_home', 'Accueil')}
                    </Link>
                    <span aria-hidden="true">/</span>
                    <Link href={`${route('client.home')}#fleet`} className="hover:text-foreground transition-colors">
                        {t('fleet_eyebrow', 'Notre flotte')}
                    </Link>
                    <span aria-hidden="true">/</span>
                    <span className="font-medium text-foreground">{car.name}</span>
                </nav>
            </div>

            <div className="container mx-auto grid grid-cols-1 items-start gap-6 px-4 pb-12 lg:grid-cols-[1fr_380px]">
                <div className="space-y-6">
                    <div className="relative overflow-hidden rounded-lg border border-border bg-muted">
                        <img
                            src={pictureUrl(car.picture)}
                            alt={car.name}
                            className="h-64 w-full object-cover sm:h-96"
                            onError={(e) => { e.target.src = '/assets/images/client/default-car.jpg'; }}
                        />
                        {car.first_registration_year && (
                            <span className="absolute start-4 top-4 rounded-full bg-foreground/85 px-3 py-1.5 text-xs font-bold text-background">
                                {car.first_registration_year}
                            </span>
                        )}
                    </div>

                    <div className="space-y-2">
                        <h1 className="font-display text-4xl uppercase md:text-5xl">{car.name}</h1>
                        {car.notes && <p className="max-w-2xl leading-relaxed text-muted-foreground">{car.notes}</p>}
                    </div>

                    <SpecGrid car={car} t={t} />
                    <Conditions t={t} />
                </div>

                <BookingCard car={car} places={places} t={t} />
            </div>

            {similarCars.length > 0 && (
                <section className="border-t border-border bg-card">
                    <div className="container mx-auto px-4 py-12">
                        <h2 className="font-display text-3xl uppercase mb-6">
                            {t('detail_similar_title', 'Véhicules similaires')}
                        </h2>
                        <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            {similarCars.map((c) => <SimilarCar key={c.id} car={c} t={t} />)}
                        </div>
                    </div>
                </section>
            )}
        </>
    );
}

CarDetails.layout = (page) => <StorefrontLayout>{page}</StorefrontLayout>;
export default CarDetails;
