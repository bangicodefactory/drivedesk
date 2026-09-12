import { useMemo, useState } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { Controller } from 'react-hook-form';
import { z } from 'zod';
import { useZodForm } from '@/hooks/useZodForm';
import { useTranslations } from '@/hooks/useTranslations';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import FieldError from '@/components/FieldError';
import { fieldA11y } from '@/lib/fieldA11y';
import PageBanner from '@/components/PageBanner';
import Stepper from '@/components/booking/Stepper';
import BookingSummary from '@/components/booking/BookingSummary';
import { specLabels } from '@/lib/vehicleSpecs';
import { useCurrency } from '@/hooks/useCurrency';
import { useOnlinePayment } from '@/hooks/useOnlinePayment';
import { dayAfter } from '@/lib/dates';
import StorefrontLayout from '@/Layouts/StorefrontLayout';
import {
    Calendar, Clock, MapPin, User, Phone, Mail, MessageCircle, Users, Flag, UserCheck, AlertCircle,
    Banknote, CreditCard, Wifi, Check,
} from 'lucide-react';

const schema = z.object({
    vehicle_id: z.string().min(1, 'Veuillez sélectionner une voiture.'),
    pickup_address: z.string().min(1, 'Lieu de prise en charge requis.'),
    drop_off_address: z.string().min(1, 'Lieu de retour requis.'),
    start_date: z.string().min(1, 'Date de prise en charge requise.'),
    start_time: z.string().min(1, "Heure de prise en charge requise."),
    end_date: z.string().min(1, 'Date de retour requise.'),
    end_time: z.string().min(1, 'Heure de retour requise.'),
    name: z.string().min(1, 'Nom complet requis.'),
    age: z.coerce.number().min(18, 'Âge minimum : 18 ans.').max(100).optional(),
    nationality: z.string().min(1, 'Nationalité requise.'),
    driving_experience: z.coerce.number().min(0).optional(),
    passengers: z.coerce.number().min(1).max(9).optional(),
    phone_number: z.string().min(1, 'Numéro de téléphone requis.'),
    whatsapp: z.string().optional(),
    email: z.string().email('Adresse email invalide.'),
    termsAccepted: z.boolean().refine((v) => v === true, { message: "Vous devez accepter les termes et conditions." }),
    payment_preference: z.enum(['cash', 'cmi'], { message: 'Veuillez choisir un mode de paiement.' }),
});

// Fields that belong to step 3 (customer info) — validated before advancing
// to the payment step, same way steps 1→2 and 2→3 gate on their own fields.
const STEP_3_FIELDS = ['name', 'age', 'nationality', 'driving_experience', 'passengers', 'phone_number', 'whatsapp', 'email', 'termsAccepted'];

function vehiclePictureUrl(vehicle) {
    return vehicle.picture ? `/storage/upload/picture/${vehicle.picture}` : '/assets/images/client/default-car.jpg';
}

function daysBetween(startDate, endDate) {
    if (!startDate || !endDate) return 0;
    const start = new Date(startDate);
    const end = new Date(endDate);
    const diff = Math.round((end - start) / (1000 * 60 * 60 * 24));
    return Math.max(1, diff);
}

function CarCard({ vehicle, selected, onSelect, t }) {
    const { gearbox, fuel } = specLabels(vehicle, t);
    const { symbol } = useCurrency();

    return (
        <div
            onClick={() => onSelect(vehicle)}
            role="button"
            tabIndex={0}
            onKeyDown={(e) => { if (e.key === 'Enter' || e.key === ' ') onSelect(vehicle); }}
            className={`bg-card rounded-xl overflow-hidden transition-all duration-300 hover:shadow-xl hover:shadow-black/5 hover:-translate-y-1 cursor-pointer border ${
                selected ? 'ring-2 ring-primary border-primary' : 'border-border/60 hover:border-foreground/20'
            }`}
        >
            <div className="relative pt-[56.25%] bg-muted overflow-hidden">
                <img
                    src={vehiclePictureUrl(vehicle)}
                    alt={vehicle.name}
                    loading="lazy"
                    className="absolute inset-0 w-full h-full object-cover"
                    onError={(e) => { e.target.src = '/assets/images/client/default-car.jpg'; }}
                />
            </div>
            <div className="p-6">
                <div className="flex justify-between items-start mb-3">
                    <h3 className="text-xl font-bold">{vehicle.name}</h3>
                    <div className="text-end shrink-0 ms-2">
                        <div className="text-sm text-muted-foreground">{t('from', 'À partir de')}</div>
                        <div className="text-lg font-display text-primary">
                            {Number(vehicle.daily_rate).toFixed(0)} {symbol}
                        </div>
                        <div className="text-xs text-muted-foreground">{t('per_day', 'par jour')}</div>
                    </div>
                </div>
                <p className="text-muted-foreground text-sm">
                    {gearbox} • {vehicle.number_of_seats ?? '—'} {t('seats', 'Sièges')} • {fuel}
                </p>
            </div>
        </div>
    );
}

function CarPicker({ vehicles, selectedId, onSelect, t }) {
    if (vehicles.length === 0) {
        return (
            <p className="text-center text-muted-foreground max-w-md mx-auto">
                {t('no_vehicles_in_fleet', 'Aucune voiture disponible pour le moment.')}
            </p>
        );
    }

    return (
        <div className="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
            {vehicles.map((vehicle) => (
                <CarCard
                    key={vehicle.id}
                    vehicle={vehicle}
                    selected={String(selectedId) === String(vehicle.id)}
                    onSelect={onSelect}
                    t={t}
                />
            ))}
        </div>
    );
}

function IconInput({ icon: Icon, className = '', ...props }) {
    return (
        <div className="relative">
            <Icon className="absolute start-3 top-1/2 -translate-y-1/2 h-5 w-5 text-muted-foreground pointer-events-none" />
            <Input className={`ps-10 ${className}`} {...props} />
        </div>
    );
}

function Booking({ vehicles = [], places = [], preselectedVehicle = null, prefill = {} }) {
    const t = useTranslations();
    const { branding } = usePage().props;
    const today = new Date().toISOString().slice(0, 10);

    // Suffix with the tenant's actual name (Settings → General, "Application
    // Name") instead of a literal client name — a hardcoded suffix here would
    // go stale the moment an owner renames their business.
    const pageTitle = branding?.appName
        ? `${t('booking_page_title', 'Réservez Votre Voiture')} | ${branding.appName}`
        : t('booking_page_title', 'Réservez Votre Voiture');

    const preselected = preselectedVehicle
        ? vehicles.find((v) => String(v.id) === String(preselectedVehicle))
        : null;

    const [step, setStep] = useState(preselected ? 2 : 1);
    const [checkingAvailability, setCheckingAvailability] = useState(false);
    const [availabilityError, setAvailabilityError] = useState(false);

    const { form, submit } = useZodForm(schema, {
        defaultValues: {
            vehicle_id: preselected ? String(preselected.id) : '',
            // Carried over from the landing search panel, which asks for the
            // same three things this step does. The server has already checked
            // the shape and that `place` names a real one (BAN-333), so an
            // absent value here means "not supplied", never "supplied badly".
            // Pick-up doubles as drop-off: one location is the common case,
            // and the visitor can still change either.
            pickup_address: prefill.place ?? '',
            drop_off_address: prefill.place ?? '',
            start_date: prefill.start_date ?? '',
            start_time: prefill.start_time ?? '09:00',
            end_date: prefill.end_date ?? '',
            end_time: prefill.end_time ?? '18:00',
            name: '', age: 25, nationality: '', driving_experience: 1, passengers: 1,
            phone_number: '', whatsapp: '', email: '', termsAccepted: false,
            payment_preference: undefined,
        },
    });
    const { register, control, watch, setValue, trigger, formState: { errors, isSubmitting } } = form;
    // 'cash' | 'online' | null — which top-level choice is highlighted on the
    // payment step. Separate from payment_preference because "online" alone
    // isn't a complete choice until a gateway is picked underneath it.
    const [paymentMode, setPaymentMode] = useState(null);
    // Whether this deployment offers card at all. Still no gateway anywhere in
    // the codebase, so what the tile collects is an intent for staff to follow
    // up on, never a charge -- see useOnlinePayment for the flag's path and why
    // it is read in exactly one place.
    //
    // drivedesk turns it on deliberately (BAN-334). This comment used to end
    // "the flag wants flipping false there before this reaches a public
    // storefront", which was true when it was written and is now an
    // instruction to undo a decision that has been made.
    const onlinePaymentEnabled = useOnlinePayment();

    const vehicleId = watch('vehicle_id');
    const startDate = watch('start_date');
    const startTime = watch('start_time');
    const endDate = watch('end_date');
    const endTime = watch('end_time');
    const pickupAddress = watch('pickup_address');
    const dropOffAddress = watch('drop_off_address');
    const termsAccepted = watch('termsAccepted');
    const paymentPreference = watch('payment_preference');

    // Remembered rather than derived from `vehicles` alone.
    //
    // goToCustomerStep() replaces `vehicles` with the list filtered to the
    // chosen dates. When the car turns out to be taken it is *absent* from that
    // list -- so a plain find() returned null, `{step === 2 && selectedVehicle
    // && ...}` unmounted the whole step, and the "no longer available" message
    // this very check had just triggered went with it, along with the Back
    // button. The visitor was left with a stepper and nothing else.
    const [lastChosenVehicle, setLastChosenVehicle] = useState(preselected ?? null);

    const selectedVehicle = useMemo(() => {
        const found = vehicles.find((v) => String(v.id) === String(vehicleId));
        if (found) return found;

        // Same car, just filtered out of the current availability list.
        return String(lastChosenVehicle?.id) === String(vehicleId) ? lastChosenVehicle : null;
    }, [vehicles, vehicleId, lastChosenVehicle]);

    const days = daysBetween(startDate, endDate);
    const total = selectedVehicle ? days * Number(selectedVehicle.daily_rate) : 0;

    const selectCar = (vehicle) => {
        setValue('vehicle_id', String(vehicle.id), { shouldValidate: true });
        setLastChosenVehicle(vehicle);
        setAvailabilityError(false);
        setStep(2);
    };

    const backToCarStep = () => {
        // The vehicles list may currently be filtered down to a previous date
        // attempt (see goToCustomerStep) — refetch the full fleet so step 1
        // doesn't look like it lost cars that are actually fine for new dates.
        router.get(route('reserve.create'), {}, {
            only: ['vehicles'],
            preserveState: true,
            preserveScroll: true,
        });
        setAvailabilityError(false);
        setStep(1);
    };

    const goToCustomerStep = () => {
        setAvailabilityError(false);
        setCheckingAvailability(true);
        router.get(route('reserve.create'), {
            start_date: startDate, start_time: startTime,
            end_date: endDate, end_time: endTime,
        }, {
            only: ['vehicles'],
            preserveState: true,
            preserveScroll: true,
            onSuccess: (page) => {
                const stillAvailable = page.props.vehicles.some((v) => String(v.id) === String(vehicleId));
                if (stillAvailable) {
                    setStep(3);
                } else {
                    setAvailabilityError(true);
                }
            },
            onFinish: () => setCheckingAvailability(false),
        });
    };

    const goToPaymentStep = async () => {
        if (await trigger(STEP_3_FIELDS)) setStep(4);
    };

    const choosePaymentMode = (mode) => {
        setPaymentMode(mode);
        // Picking "cash" is itself a complete choice; picking "online" still
        // needs a gateway underneath it, so don't set a value yet.
        setValue('payment_preference', mode === 'cash' ? 'cash' : undefined, { shouldValidate: true });
    };

    // The payment tiles are a radio group, so they owe the radio keyboard
    // contract: Enter/Space select, arrows move between options and select as
    // they go, and only one option is in the tab order (roving tabindex).
    // Space must preventDefault or it selects *and* scrolls the page.
    const paymentModes = onlinePaymentEnabled ? ['cash', 'online'] : ['cash'];

    const paymentModeKeyDown = (mode) => (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            choosePaymentMode(mode);
            return;
        }

        // Horizontal arrows follow the reading direction, so they invert under
        // RTL (`ar`); vertical ones never do.
        const rtl = typeof document !== 'undefined' && document.documentElement.dir === 'rtl';
        const forward = event.key === 'ArrowDown' || event.key === (rtl ? 'ArrowLeft' : 'ArrowRight');
        const back = event.key === 'ArrowUp' || event.key === (rtl ? 'ArrowRight' : 'ArrowLeft');
        if ((!forward && !back) || paymentModes.length < 2) return;

        event.preventDefault();
        const step = forward ? 1 : -1;
        const next = paymentModes[
            (paymentModes.indexOf(mode) + step + paymentModes.length) % paymentModes.length
        ];
        choosePaymentMode(next);
        document.getElementById(`payment-mode-${next}`)?.focus();
    };

    const selectCmi = () => setValue('payment_preference', 'cmi', { shouldValidate: true });

    // step_1..step_4 keep their existing values for anyone still rendering
    // them; the stepper reads the shorter step_short_* keys, which fit a phone
    // without being clipped mid-word (CLAUDE.md §4: keys are added, not
    // repurposed).
    const stepLabels = [
        t('step_short_1', 'Voiture'),
        t('step_short_2', 'Dates'),
        t('step_short_3', 'Vos infos'),
        t('step_short_4', 'Paiement'),
    ];

    const summary = (
        <BookingSummary
            vehicle={selectedVehicle}
            places={places}
            pickupId={pickupAddress}
            dropOffId={dropOffAddress}
            startDate={startDate}
            startTime={startTime}
            endDate={endDate}
            endTime={endTime}
            days={days}
            total={total}
            t={t}
        />
    );

    return (
        <>
            <Head title={pageTitle} />
            <PageBanner title={t('booking_title', 'Réservez Votre Voiture')} subtitle={t('booking_banner_subtitle', 'Complétez votre réservation en quelques étapes simples')} />

            <section className="py-10 md:py-12">
                <div className="container mx-auto px-4 max-w-6xl">
                    <Stepper current={step} labels={stepLabels} />

                    {step === 1 && (
                        <CarPicker vehicles={vehicles} selectedId={vehicleId} onSelect={selectCar} t={t} />
                    )}

                    {/* Steps 2-4 sit beside the summary; step 1 is the car grid
                        itself, where there is nothing to summarise yet. */}
                    <div className={step === 1 ? 'contents' : 'grid grid-cols-1 items-start gap-6 lg:grid-cols-[1fr_320px]'}>
                    <div className="space-y-6">

                    {step === 2 && selectedVehicle && (
                        <div className="rounded-lg border border-border bg-card p-5 md:p-6">
                            <h2 className="font-display text-2xl uppercase mb-5">{t('step_2', 'Sélectionner les Dates')}</h2>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div className="space-y-4">
                                    <div>
                                        <Label htmlFor="start_date">{t('pickup_date', 'Date de Prise en Charge')}</Label>
                                        <IconInput icon={Calendar} id="start_date" type="date" min={today}
                                            {...register('start_date')} {...fieldA11y(errors, 'start_date')} />
                                        <FieldError name="start_date" errors={errors} />
                                    </div>
                                    <div>
                                        <Label htmlFor="pickup_address">{t('pickup_location', 'Lieu de Prise en Charge')}</Label>
                                        <Controller
                                            name="pickup_address" control={control}
                                            render={({ field }) => (
                                                <Select value={field.value} onValueChange={field.onChange}>
                                                    <SelectTrigger id="pickup_address" className="ps-10 relative" {...fieldA11y(errors, 'pickup_address')}>
                                                        <MapPin className="absolute start-3 top-1/2 -translate-y-1/2 h-5 w-5 text-muted-foreground pointer-events-none" />
                                                        <SelectValue placeholder={t('select_location', 'Sélectionner un lieu')} />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        {places.map((p) => <SelectItem key={p.id} value={String(p.id)}>{p.name}</SelectItem>)}
                                                    </SelectContent>
                                                </Select>
                                            )}
                                        />
                                        <FieldError name="pickup_address" errors={errors} />
                                    </div>
                                    <div>
                                        <Label htmlFor="start_time">{t('pickup_time', 'Heure de Prise en Charge')}</Label>
                                        <IconInput icon={Clock} id="start_time" type="time" {...register('start_time')} {...fieldA11y(errors, 'start_time')} />
                                        <FieldError name="start_time" errors={errors} />
                                    </div>
                                </div>

                                <div className="space-y-4">
                                    <div>
                                        <Label htmlFor="end_date">{t('return_date', 'Date de Retour')}</Label>
                                        <IconInput icon={Calendar} id="end_date" type="date" min={dayAfter(startDate) || today}
                                            disabled={!startDate}
                                            {...register('end_date')} {...fieldA11y(errors, 'end_date')} />
                                        <FieldError name="end_date" errors={errors} />
                                    </div>
                                    <div>
                                        <Label htmlFor="drop_off_address">{t('return_location', 'Lieu de Retour')}</Label>
                                        <Controller
                                            name="drop_off_address" control={control}
                                            render={({ field }) => (
                                                <Select value={field.value} onValueChange={field.onChange}>
                                                    <SelectTrigger id="drop_off_address" className="ps-10 relative" {...fieldA11y(errors, 'drop_off_address')}>
                                                        <MapPin className="absolute start-3 top-1/2 -translate-y-1/2 h-5 w-5 text-muted-foreground pointer-events-none" />
                                                        <SelectValue placeholder={t('select_location', 'Sélectionner un lieu')} />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        {places.map((p) => <SelectItem key={p.id} value={String(p.id)}>{p.name}</SelectItem>)}
                                                    </SelectContent>
                                                </Select>
                                            )}
                                        />
                                        <FieldError name="drop_off_address" errors={errors} />
                                    </div>
                                    <div>
                                        <Label htmlFor="end_time">{t('return_time', 'Heure de Retour')}</Label>
                                        <IconInput icon={Clock} id="end_time" type="time" {...register('end_time')} {...fieldA11y(errors, 'end_time')} />
                                        <FieldError name="end_time" errors={errors} />
                                    </div>
                                </div>
                            </div>

                            {availabilityError && (
                                <div className="flex items-start gap-2 mt-6 p-3 rounded-md bg-destructive/10 text-destructive text-sm">
                                    <AlertCircle className="h-4 w-4 mt-0.5 shrink-0" />
                                    <span>{t('vehicle_unavailable_for_dates', "Cette voiture n'est plus disponible pour ces dates. Choisissez d'autres dates ou revenez en arrière pour une autre voiture.")}</span>
                                </div>
                            )}

                            <div className="flex justify-between mt-8">
                                <Button type="button" variant="outline" className="rounded-full" onClick={backToCarStep}>{t('back', 'Retour')}</Button>
                                <Button
                                    type="button"
                                    className="rounded-full"
                                    onClick={goToCustomerStep}
                                    disabled={!startDate || !endDate || !pickupAddress || !dropOffAddress || checkingAvailability}
                                >
                                    {checkingAvailability ? t('checking_availability', 'Vérification…') : t('continue', 'Continuer')}
                                </Button>
                            </div>
                        </div>
                    )}

                    {step === 3 && selectedVehicle && (
                        <div className="rounded-lg border border-border bg-card p-5 md:p-6 space-y-6">
                            <div>
                                <h2 className="font-display text-2xl uppercase">{t('step_3', 'Vos Informations')}</h2>
                                <p className="mt-1 text-sm text-muted-foreground">
                                    {t('driver_eligibility_note', 'Le conducteur doit avoir 21 ans et le permis depuis 2 ans au moins.')}
                                </p>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <Label htmlFor="name">{t('full_name', 'Nom Complet')}</Label>
                                    <IconInput icon={User} id="name" {...register('name')} {...fieldA11y(errors, 'name')} />
                                    <FieldError name="name" errors={errors} />
                                </div>
                                <div>
                                    <Label htmlFor="age">{t('age_label', 'Âge')}</Label>
                                    <IconInput icon={UserCheck} id="age" type="number" min={18} max={100} {...register('age')} {...fieldA11y(errors, 'age')} />
                                    <FieldError name="age" errors={errors} />
                                </div>
                                <div>
                                    <Label htmlFor="nationality">{t('nationality', 'Nationalité')}</Label>
                                    <IconInput icon={Flag} id="nationality" {...register('nationality')} {...fieldA11y(errors, 'nationality')} />
                                    <FieldError name="nationality" errors={errors} />
                                </div>
                                <div>
                                    <Label htmlFor="driving_experience">{t('driving_experience', "Années d'Expérience de Conduite")}</Label>
                                    <Input id="driving_experience" type="number" min={0} {...register('driving_experience')} {...fieldA11y(errors, 'driving_experience')} />
                                    <FieldError name="driving_experience" errors={errors} />
                                </div>
                                <div className="md:col-span-2">
                                    <Label htmlFor="passengers">{t('passengers', 'Nombre de Passagers')}</Label>
                                    <IconInput icon={Users} id="passengers" type="number" min={1} max={9} {...register('passengers')} {...fieldA11y(errors, 'passengers')} />
                                    <FieldError name="passengers" errors={errors} />
                                </div>
                                <div>
                                    <Label htmlFor="phone_number">{t('phone', 'Numéro de Téléphone')}</Label>
                                    <IconInput icon={Phone} id="phone_number" type="tel" {...register('phone_number')} {...fieldA11y(errors, 'phone_number')} />
                                    <FieldError name="phone_number" errors={errors} />
                                </div>
                                <div>
                                    <Label htmlFor="whatsapp">{t('whatsapp', 'Numéro WhatsApp')}</Label>
                                    <IconInput icon={MessageCircle} id="whatsapp" type="tel" {...register('whatsapp')} {...fieldA11y(errors, 'whatsapp')} />
                                    <FieldError name="whatsapp" errors={errors} />
                                </div>
                                <div className="md:col-span-2">
                                    <Label htmlFor="email">{t('email', 'Adresse Email')}</Label>
                                    <IconInput icon={Mail} id="email" type="email" {...register('email')} {...fieldA11y(errors, 'email')} />
                                    <FieldError name="email" errors={errors} />
                                </div>
                            </div>

                            <div className="flex items-start">
                                <input
                                    id="termsAccepted" type="checkbox"
                                    className="h-4 w-4 rounded border-input text-primary focus:ring-primary mt-0.5"
                                    {...register('termsAccepted')}
                                />
                                <label htmlFor="termsAccepted" className="ms-3 text-sm text-muted-foreground">
                                    {t('terms_prefix', "J'accepte les")}{' '}
                                    <Link href="/terms" className="text-primary hover:underline">{t('terms_link', 'termes et conditions')}</Link>
                                </label>
                            </div>
                            <FieldError name="termsAccepted" errors={errors} />

                            <div className="flex justify-between">
                                <Button type="button" variant="outline" className="rounded-full" onClick={() => setStep(2)}>{t('back', 'Retour')}</Button>
                                <Button type="button" className="rounded-full" onClick={goToPaymentStep}>{t('continue', 'Continuer')}</Button>
                            </div>
                        </div>
                    )}

                    {step === 4 && selectedVehicle && (
                        <form onSubmit={submit('post', route('booking.store_request'))} className="rounded-lg border border-border bg-card p-5 md:p-6 space-y-6">
                            <input type="hidden" {...register('vehicle_id')} />

                            <h2 className="font-display text-2xl uppercase">{t('step_4', 'Paiement')}</h2>

                            <div>
                                <Label id="payment-method-label" className="mb-2 block">{t('payment_method_label', 'Comment souhaitez-vous payer ?')}</Label>
                                <div role="radiogroup" aria-labelledby="payment-method-label" className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div
                                        id="payment-mode-cash"
                                        onClick={() => choosePaymentMode('cash')}
                                        role="radio"
                                        aria-checked={paymentMode === 'cash'}
                                        tabIndex={paymentMode === null || paymentMode === 'cash' ? 0 : -1}
                                        onKeyDown={paymentModeKeyDown('cash')}
                                        className={`flex items-center gap-3 p-4 rounded-xl border cursor-pointer transition-colors ${
                                            paymentMode === 'cash' ? 'ring-2 ring-primary border-primary' : 'border-border/60 hover:border-foreground/20 hover:bg-muted/40'
                                        }`}
                                    >
                                        <Banknote className="h-6 w-6 text-primary shrink-0" strokeWidth={1.5} />
                                        <div>
                                            <p className="font-medium">{t('payment_cash', 'Paiement à la Livraison')}</p>
                                            <p className="text-sm text-muted-foreground">{t('payment_cash_desc', 'Payez en espèces au bureau')}</p>
                                        </div>
                                    </div>
                                    {onlinePaymentEnabled && (
                                    <div
                                        id="payment-mode-online"
                                        onClick={() => choosePaymentMode('online')}
                                        role="radio"
                                        aria-checked={paymentMode === 'online'}
                                        tabIndex={paymentMode === 'online' ? 0 : -1}
                                        onKeyDown={paymentModeKeyDown('online')}
                                        className={`flex items-center gap-3 p-4 rounded-xl border cursor-pointer transition-colors ${
                                            paymentMode === 'online' ? 'ring-2 ring-primary border-primary' : 'border-border/60 hover:border-foreground/20 hover:bg-muted/40'
                                        }`}
                                    >
                                        <CreditCard className="h-6 w-6 text-primary shrink-0" strokeWidth={1.5} />
                                        <div>
                                            <p className="font-medium">{t('payment_online', 'Paiement en Ligne')}</p>
                                            <p className="text-sm text-muted-foreground">{t('payment_online_desc', 'Carte bancaire (CMI)')}</p>
                                        </div>
                                    </div>
                                    )}
                                </div>

                                {onlinePaymentEnabled && paymentMode === 'online' && (
                                    <div className="mt-4 p-4 rounded-xl bg-muted/40 border border-border/60 space-y-3">
                                        <p id="payment-gateway-label" className="text-sm font-medium">{t('payment_choose_gateway', 'Choisissez votre moyen de paiement en ligne')}</p>
                                        <div role="radiogroup" aria-labelledby="payment-gateway-label" className="grid grid-cols-1 gap-3">
                                            <div
                                                id="payment-gateway-cmi"
                                                onClick={selectCmi}
                                                role="radio"
                                                aria-checked={paymentPreference === 'cmi'}
                                                // Named explicitly: the card chrome (cardholder line, the printed
                                                // "paiement en ligne", the masked digits) would otherwise be read
                                                // out as part of this option's name.
                                                aria-label="CMI"
                                                tabIndex={0}
                                                onKeyDown={(e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); selectCmi(); } }}
                                                className={`relative w-full max-w-xs overflow-hidden rounded-2xl p-5 cursor-pointer bg-primary text-primary-foreground transition-all ${
                                                    paymentPreference === 'cmi'
                                                        ? 'ring-2 ring-primary-foreground ring-offset-2 ring-offset-background shadow-lg'
                                                        : 'opacity-90 shadow-md hover:opacity-100'
                                                }`}
                                            >
                                                {/* Darkens the card toward the bottom-end corner so every label on it
                                                    clears WCAG AA. White on bare `primary` is only 3.49:1, and the
                                                    translucent gradient this replaces fell to 2.46:1 at its lightest
                                                    corner — which is exactly where the right-hand label sits. This
                                                    ramp measures 4.65:1 at the top-start and 6.33:1 at the bottom-end. */}
                                                <div aria-hidden className="pointer-events-none absolute inset-0 bg-gradient-to-br from-black/15 to-black/30" />
                                                <div aria-hidden className="pointer-events-none absolute -end-8 -top-8 h-32 w-32 rounded-full bg-white/10" />
                                                <div aria-hidden className="pointer-events-none absolute -bottom-10 -start-6 h-28 w-28 rounded-full bg-white/10" />

                                                <div className="relative flex items-center justify-between">
                                                    <Wifi className="h-5 w-5 rotate-90 text-primary-foreground" strokeWidth={1.5} />
                                                    <span className="flex items-center gap-1.5 text-sm font-bold tracking-wide">
                                                        {/* A shape, not just the ring colour: `ring-primary` used to be
                                                            drawn on `bg-primary` (identical token), so "selected" was
                                                            invisible — and clicking this card is what enables Continue. */}
                                                        {paymentPreference === 'cmi' && (
                                                            <Check className="h-4 w-4" strokeWidth={3} aria-hidden />
                                                        )}
                                                        CMI
                                                    </span>
                                                </div>

                                                <div aria-hidden className="relative mt-6 h-6 w-9 rounded-md bg-white/25" />

                                                <div aria-hidden className="relative mt-4 flex gap-3 font-mono text-lg tracking-[0.2em]">
                                                    <span>••••</span><span>••••</span><span>••••</span><span>••••</span>
                                                </div>

                                                <div className="relative mt-4 flex items-center justify-between text-xs uppercase tracking-wide text-primary-foreground">
                                                    <span>{t('payment_cardholder', 'Titulaire de la carte')}</span>
                                                    <span className="flex items-center gap-1">
                                                        <CreditCard className="h-4 w-4" strokeWidth={1.5} />
                                                        {t('payment_online', 'Paiement en Ligne')}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <p className="text-xs text-muted-foreground">
                                            {t('payment_online_note', "Nous vous contacterons pour finaliser le paiement en ligne après votre demande.")}
                                        </p>
                                    </div>
                                )}
                                <FieldError name="payment_preference" errors={errors} />
                            </div>

                            <div className="flex justify-between">
                                <Button type="button" variant="outline" className="rounded-full" onClick={() => setStep(3)}>{t('back', 'Retour')}</Button>
                                <Button type="submit" className="rounded-full" disabled={!termsAccepted || !paymentPreference || isSubmitting}>
                                    {isSubmitting ? t('sending', 'Envoi…') : t('submit', 'Compléter la Réservation')}
                                </Button>
                            </div>
                        </form>
                    )}
                    </div>
                    {step !== 1 && summary}
                    </div>
                </div>
            </section>
        </>
    );
}

Booking.layout = (page) => <StorefrontLayout>{page}</StorefrontLayout>;
export default Booking;
