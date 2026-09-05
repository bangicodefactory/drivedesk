import { useMemo, useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
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
import StorefrontLayout from '@/Layouts/StorefrontLayout';
import {
    Calendar, Clock, MapPin, User, Phone, Mail, MessageCircle, Users, Flag, UserCheck, AlertCircle,
    Banknote, CreditCard, Wallet,
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
    payment_preference: z.enum(['cash', 'paypal', 'cmi'], { message: 'Veuillez choisir un mode de paiement.' }),
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
                <div
                    key={vehicle.id}
                    onClick={() => onSelect(vehicle)}
                    role="button"
                    tabIndex={0}
                    onKeyDown={(e) => { if (e.key === 'Enter' || e.key === ' ') onSelect(vehicle); }}
                    className={`bg-card rounded-lg shadow-md overflow-hidden transition-all duration-300 hover:shadow-lg hover:-translate-y-1 cursor-pointer border ${
                        String(selectedId) === String(vehicle.id) ? 'ring-2 ring-primary' : ''
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
                                <div className="text-lg font-bold text-primary">{Number(vehicle.daily_rate).toFixed(0)} MAD</div>
                                <div className="text-xs text-muted-foreground">{t('per_day', 'par jour')}</div>
                            </div>
                        </div>
                        <p className="text-muted-foreground text-sm">
                            {vehicle.gearbox ?? '—'} • {vehicle.number_of_seats ?? '—'} {t('seats', 'Sièges')} • {vehicle.fuel_type ?? '—'}
                        </p>
                    </div>
                </div>
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

function Booking({ vehicles = [], places = [], preselectedVehicle = null }) {
    const t = useTranslations();
    const today = new Date().toISOString().slice(0, 10);

    const preselected = preselectedVehicle
        ? vehicles.find((v) => String(v.id) === String(preselectedVehicle))
        : null;

    const [step, setStep] = useState(preselected ? 2 : 1);
    const [checkingAvailability, setCheckingAvailability] = useState(false);
    const [availabilityError, setAvailabilityError] = useState(false);

    const { form, submit } = useZodForm(schema, {
        defaultValues: {
            vehicle_id: preselected ? String(preselected.id) : '',
            pickup_address: '', drop_off_address: '',
            start_date: '', start_time: '09:00',
            end_date: '', end_time: '18:00',
            name: '', age: 25, nationality: '', driving_experience: 1, passengers: 1,
            phone_number: '', whatsapp: '', email: '', termsAccepted: false,
            payment_preference: undefined,
        },
    });
    const { register, control, watch, setValue, trigger, formState: { errors, isSubmitting } } = form;
    // 'cash' | 'online' | null — which top-level choice is highlighted on the
    // payment step. Separate from payment_preference because "online" alone
    // isn't a complete choice until PayPal or CMI is picked underneath it.
    const [paymentMode, setPaymentMode] = useState(null);

    const vehicleId = watch('vehicle_id');
    const startDate = watch('start_date');
    const startTime = watch('start_time');
    const endDate = watch('end_date');
    const endTime = watch('end_time');
    const pickupAddress = watch('pickup_address');
    const dropOffAddress = watch('drop_off_address');
    const termsAccepted = watch('termsAccepted');
    const paymentPreference = watch('payment_preference');

    const selectedVehicle = useMemo(
        () => vehicles.find((v) => String(v.id) === String(vehicleId)) ?? null,
        [vehicles, vehicleId],
    );

    const days = daysBetween(startDate, endDate);
    const total = selectedVehicle ? days * Number(selectedVehicle.daily_rate) : 0;

    const selectCar = (vehicle) => {
        setValue('vehicle_id', String(vehicle.id), { shouldValidate: true });
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
        // needs PayPal or CMI underneath it, so don't set a value yet.
        setValue('payment_preference', mode === 'cash' ? 'cash' : undefined, { shouldValidate: true });
    };

    const stepLabels = [
        t('step_1', 'Sélectionner une Voiture'),
        t('step_2', 'Sélectionner les Dates'),
        t('step_3', 'Vos Informations'),
        t('step_4', 'Paiement'),
    ];

    return (
        <>
            <Head title={t('booking_page_title', 'Réservez Votre Voiture | MarrueCar')} />
            <PageBanner title={t('booking_title', 'Réservez Votre Voiture')} subtitle={t('booking_banner_subtitle', 'Complétez votre réservation en quelques étapes simples')} />

            <section className="py-12 md:py-16 lg:py-24">
                <div className="container mx-auto px-4 max-w-7xl">
                    <div className="mb-10 md:mb-16 text-center">
                        <h2 className="text-3xl md:text-4xl font-bold mb-4">{t('booking_title', 'Réservez Votre Voiture')}</h2>
                        <p className="text-lg text-muted-foreground max-w-3xl mx-auto">{t('booking_section_subtitle', 'Complétez votre réservation en 3 étapes simples')}</p>
                    </div>

                    <Stepper current={step} labels={stepLabels} />

                    {step === 1 && (
                        <CarPicker vehicles={vehicles} selectedId={vehicleId} onSelect={selectCar} t={t} />
                    )}

                    {step === 2 && selectedVehicle && (
                        <div className="max-w-3xl mx-auto bg-card rounded-lg shadow-md p-6 md:p-8">
                            <div className="flex items-center mb-6">
                                <img src={vehiclePictureUrl(selectedVehicle)} alt={selectedVehicle.name} className="w-16 h-16 object-cover rounded-md me-4" />
                                <div>
                                    <h3 className="text-lg font-bold">{selectedVehicle.name}</h3>
                                    <p className="text-muted-foreground">{t('from', 'À partir de')} {Number(selectedVehicle.daily_rate).toFixed(0)} MAD {t('per_day', 'par jour')}</p>
                                </div>
                            </div>

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
                                        <IconInput icon={Calendar} id="end_date" type="date" min={startDate || today}
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
                                <Button type="button" variant="outline" onClick={backToCarStep}>{t('back', 'Retour')}</Button>
                                <Button
                                    type="button"
                                    onClick={goToCustomerStep}
                                    disabled={!startDate || !endDate || !pickupAddress || !dropOffAddress || checkingAvailability}
                                >
                                    {checkingAvailability ? t('checking_availability', 'Vérification…') : t('continue', 'Continuer')}
                                </Button>
                            </div>
                        </div>
                    )}

                    {step === 3 && selectedVehicle && (
                        <div className="max-w-3xl mx-auto bg-card rounded-lg shadow-md p-6 md:p-8 space-y-6">
                            <div className="flex items-center justify-between mb-2 pb-6 border-b">
                                <div className="flex items-center">
                                    <img src={vehiclePictureUrl(selectedVehicle)} alt={selectedVehicle.name} className="w-16 h-16 object-cover rounded-md me-4" />
                                    <div>
                                        <h3 className="text-lg font-bold">{selectedVehicle.name}</h3>
                                        <p className="text-muted-foreground text-sm">
                                            {startDate} - {endDate} ({days} {t('days', 'jours')})
                                        </p>
                                    </div>
                                </div>
                                <p className="text-lg font-bold text-primary shrink-0 ms-2">{total.toFixed(0)} MAD</p>
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
                                <Button type="button" variant="outline" onClick={() => setStep(2)}>{t('back', 'Retour')}</Button>
                                <Button type="button" onClick={goToPaymentStep}>{t('continue', 'Continuer')}</Button>
                            </div>
                        </div>
                    )}

                    {step === 4 && selectedVehicle && (
                        <form onSubmit={submit('post', route('booking.store_request'))} className="max-w-3xl mx-auto bg-card rounded-lg shadow-md p-6 md:p-8 space-y-6">
                            <input type="hidden" {...register('vehicle_id')} />

                            <div className="flex items-center justify-between mb-2 pb-6 border-b">
                                <div className="flex items-center">
                                    <img src={vehiclePictureUrl(selectedVehicle)} alt={selectedVehicle.name} className="w-16 h-16 object-cover rounded-md me-4" />
                                    <div>
                                        <h3 className="text-lg font-bold">{selectedVehicle.name}</h3>
                                        <p className="text-muted-foreground text-sm">
                                            {startDate} - {endDate} ({days} {t('days', 'jours')})
                                        </p>
                                    </div>
                                </div>
                                <p className="text-lg font-bold text-primary shrink-0 ms-2">{total.toFixed(0)} MAD</p>
                            </div>

                            <div>
                                <Label className="mb-2 block">{t('payment_method_label', 'Comment souhaitez-vous payer ?')}</Label>
                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div
                                        onClick={() => choosePaymentMode('cash')}
                                        role="button" tabIndex={0}
                                        onKeyDown={(e) => { if (e.key === 'Enter' || e.key === ' ') choosePaymentMode('cash'); }}
                                        className={`flex items-center gap-3 p-4 rounded-lg border cursor-pointer transition-colors ${
                                            paymentMode === 'cash' ? 'ring-2 ring-primary border-primary' : 'hover:bg-muted/50'
                                        }`}
                                    >
                                        <Banknote className="h-6 w-6 text-primary shrink-0" />
                                        <div>
                                            <p className="font-medium">{t('payment_cash', 'Paiement à la Livraison')}</p>
                                            <p className="text-sm text-muted-foreground">{t('payment_cash_desc', 'Payez en espèces au bureau')}</p>
                                        </div>
                                    </div>
                                    <div
                                        onClick={() => choosePaymentMode('online')}
                                        role="button" tabIndex={0}
                                        onKeyDown={(e) => { if (e.key === 'Enter' || e.key === ' ') choosePaymentMode('online'); }}
                                        className={`flex items-center gap-3 p-4 rounded-lg border cursor-pointer transition-colors ${
                                            paymentMode === 'online' ? 'ring-2 ring-primary border-primary' : 'hover:bg-muted/50'
                                        }`}
                                    >
                                        <CreditCard className="h-6 w-6 text-primary shrink-0" />
                                        <div>
                                            <p className="font-medium">{t('payment_online', 'Paiement en Ligne')}</p>
                                            <p className="text-sm text-muted-foreground">{t('payment_online_desc', 'PayPal ou CMI')}</p>
                                        </div>
                                    </div>
                                </div>

                                {paymentMode === 'online' && (
                                    <div className="mt-4 p-4 rounded-lg bg-muted/40 space-y-3">
                                        <p className="text-sm font-medium">{t('payment_choose_gateway', 'Choisissez votre moyen de paiement en ligne')}</p>
                                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <div
                                                onClick={() => setValue('payment_preference', 'paypal', { shouldValidate: true })}
                                                role="button" tabIndex={0}
                                                onKeyDown={(e) => { if (e.key === 'Enter' || e.key === ' ') setValue('payment_preference', 'paypal', { shouldValidate: true }); }}
                                                className={`flex items-center gap-2 p-3 rounded-lg border bg-card cursor-pointer transition-colors ${
                                                    paymentPreference === 'paypal' ? 'ring-2 ring-primary border-primary' : 'hover:bg-muted/50'
                                                }`}
                                            >
                                                <Wallet className="h-5 w-5 text-primary shrink-0" />
                                                <span className="font-medium">PayPal</span>
                                            </div>
                                            <div
                                                onClick={() => setValue('payment_preference', 'cmi', { shouldValidate: true })}
                                                role="button" tabIndex={0}
                                                onKeyDown={(e) => { if (e.key === 'Enter' || e.key === ' ') setValue('payment_preference', 'cmi', { shouldValidate: true }); }}
                                                className={`flex items-center gap-2 p-3 rounded-lg border bg-card cursor-pointer transition-colors ${
                                                    paymentPreference === 'cmi' ? 'ring-2 ring-primary border-primary' : 'hover:bg-muted/50'
                                                }`}
                                            >
                                                <CreditCard className="h-5 w-5 text-primary shrink-0" />
                                                <span className="font-medium">CMI</span>
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
                                <Button type="button" variant="outline" onClick={() => setStep(3)}>{t('back', 'Retour')}</Button>
                                <Button type="submit" disabled={!termsAccepted || !paymentPreference || isSubmitting}>
                                    {isSubmitting ? t('sending', 'Envoi…') : t('submit', 'Compléter la Réservation')}
                                </Button>
                            </div>
                        </form>
                    )}
                </div>
            </section>
        </>
    );
}

Booking.layout = (page) => <StorefrontLayout>{page}</StorefrontLayout>;
export default Booking;
