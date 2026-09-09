import { Head, Link, usePage } from '@inertiajs/react';
import { Check, MessageCircle, IdCard, Banknote, FileText } from 'lucide-react';
import { Button } from '@/components/ui/button';
import StorefrontLayout from '@/Layouts/StorefrontLayout';
import { useTranslations } from '@/hooks/useTranslations';
import { useCurrency } from '@/hooks/useCurrency';

/**
 * What a visitor sees the moment they have handed over their details
 * (BAN-333).
 *
 * Two jobs, in this order: say plainly that nothing has been charged and that
 * this is a request rather than a confirmed booking, and then tell them what
 * to bring so the pick-up does not fail. The second half is drawn from the
 * rental agreement the client prints on the contract
 * (config/clients/<client>.php → terms.rental_agreement, articles 2 and 4).
 *
 * The dark PageBanner is gone: a confirmation is the end of a flow, and
 * leading it with a 160px banner pushed the reference number — the one thing
 * on this page worth writing down — below the fold on a phone.
 */
function Confirmation({
    reference, car, pickupPlace, dropOffPlace,
    startDate, startTime, endDate, endTime, days, amount, paymentPreference,
}) {
    const t = useTranslations();
    const { symbol } = useCurrency();
    const { contact, branding } = usePage().props;

    // Suffix with the tenant's actual name (Settings → General, "Application
    // Name") instead of a literal client name — a hardcoded suffix here would
    // go stale the moment an owner renames their business.
    const pageTitle = branding?.appName
        ? `${t('booking_confirmation_title', 'Réservation Envoyée')} | ${branding.appName}`
        : t('booking_confirmation_title', 'Réservation Envoyée');

    const whatsappText = encodeURIComponent(
        `Bonjour, je confirme ma réservation ${reference} : ${car?.name ?? ''} du ${startDate} au ${endDate}.`,
    );
    const whatsappHref = contact?.whatsapp ? `https://wa.me/${contact.whatsapp}?text=${whatsappText}` : null;

    // No CMI charge actually happens yet — staff follow up by phone/WhatsApp to
    // collect it, so the copy must not imply payment is done.
    const isOnlinePayment = paymentPreference === 'paypal' || paymentPreference === 'cmi';
    const confirmationBody = isOnlinePayment
        ? t('confirmation_body_online', 'Nous vous contacterons rapidement pour finaliser votre paiement en ligne et confirmer votre réservation.')
        : t('confirmation_body', 'Nous vous contacterons rapidement pour confirmer votre réservation.');

    const rows = [
        { key: 'car', label: t('summary_car', 'Voiture'), value: [car?.name, car?.model].filter(Boolean).join(' ') },
        { key: 'pickup', label: t('summary_pickup', 'Prise en charge'), value: [pickupPlace, [startDate, startTime].filter(Boolean).join(' ')].filter(Boolean).join(' — ') },
        { key: 'return', label: t('summary_return', 'Retour'), value: [dropOffPlace, [endDate, endTime].filter(Boolean).join(' ')].filter(Boolean).join(' — ') },
        { key: 'days', label: t('summary_days', 'Durée'), value: `${days} ${days > 1 ? t('detail_days', 'jours') : t('detail_day', 'jour')}` },
        paymentPreference && {
            key: 'payment',
            label: t('summary_payment', 'Paiement'),
            value: paymentPreference === 'cash'
                ? t('payment_cash', 'Paiement à la Livraison')
                : paymentPreference.toUpperCase(),
        },
    ].filter(Boolean).filter((r) => r.value);

    const bring = [
        { icon: IdCard, text: t('bring_licence', 'Permis de conduire original, en cours de validité depuis 2 ans au moins.') },
        { icon: FileText, text: t('bring_id', 'Pièce d’identité (CIN ou passeport).') },
        { icon: Banknote, text: t('bring_deposit', 'De quoi régler la location et, le cas échéant, la caution — restituée au retour du véhicule.') },
    ];

    const hours = [
        contact?.hoursWeekday && [t('hours_weekday_label', 'Lun – Ven'), contact.hoursWeekday],
        contact?.hoursSaturday && [t('hours_saturday_label', 'Sam'), contact.hoursSaturday],
        contact?.hoursSunday && [t('hours_sunday_label', 'Dim'), contact.hoursSunday],
    ].filter(Boolean);

    return (
        <>
            <Head title={pageTitle} />

            <section className="container mx-auto max-w-2xl px-4 py-10 md:py-14">
                <div className="rounded-lg border border-border bg-card p-6 text-center md:p-8">
                    <span
                        className="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-full"
                        style={{ background: 'hsl(var(--success) / 0.12)' }}
                    >
                        <Check className="h-7 w-7" strokeWidth={3} style={{ color: 'hsl(var(--success))' }} />
                    </span>

                    <h1 className="font-display text-3xl uppercase md:text-4xl">
                        {t('confirmation_heading_short', 'Demande envoyée')}
                    </h1>
                    <p className="mx-auto mt-3 max-w-md leading-relaxed text-muted-foreground">
                        {confirmationBody}{' '}
                        <strong className="font-semibold text-foreground">
                            {t('confirmation_nothing_charged', "Rien n'a été prélevé.")}
                        </strong>
                    </p>

                    {/* The one thing on this page worth writing down. */}
                    <p className="mt-6 flex flex-wrap items-center justify-center gap-x-4 gap-y-1 rounded-lg border border-border bg-background px-4 py-3">
                        <span className="eyebrow text-[11px] font-bold text-muted-foreground">
                            {t('confirmation_reference_label', 'Référence')}
                        </span>
                        <strong className="font-mono text-xl font-bold tracking-widest">{reference}</strong>
                    </p>

                    <dl className="mt-5 space-y-2.5 rounded-lg border border-border p-4 text-start text-sm">
                        {rows.map(({ key, label, value }) => (
                            <div key={key} className="flex justify-between gap-4">
                                <dt className="text-muted-foreground">{label}</dt>
                                <dd className="text-end font-semibold">{value}</dd>
                            </div>
                        ))}
                        <div className="flex items-baseline justify-between gap-4 border-t border-border pt-3">
                            <dt className="font-bold">{t('summary_total', 'Total estimé')}</dt>
                            <dd className="font-display text-2xl">{Number(amount).toFixed(0)} {symbol}</dd>
                        </div>
                    </dl>

                    <div className="mt-5 flex flex-col gap-3 sm:flex-row sm:justify-center">
                        {whatsappHref && (
                            <a href={whatsappHref} target="_blank" rel="noopener noreferrer" className="sm:flex-1">
                                <Button className="h-12 w-full bg-green-600 text-base hover:bg-green-700">
                                    <MessageCircle className="h-5 w-5" /> {t('confirm_on_whatsapp', 'Confirmer sur WhatsApp')}
                                </Button>
                            </a>
                        )}
                        <Link href={route('client.home')} className="sm:flex-1">
                            <Button variant="outline" className="h-12 w-full">
                                {t('back_to_home', "Retour à l'Accueil")}
                            </Button>
                        </Link>
                    </div>
                </div>

                <div className="mt-5 rounded-lg border border-border bg-card p-5">
                    <h2 className="font-display text-2xl uppercase">{t('bring_title', 'À apporter au retrait')}</h2>
                    <ul className="mt-4 space-y-3">
                        {bring.map(({ icon: Icon, text }) => (
                            <li key={text} className="flex gap-2.5">
                                <Icon className="mt-0.5 h-4 w-4 shrink-0 text-primary" strokeWidth={1.8} />
                                <span className="text-sm leading-relaxed text-muted-foreground">{text}</span>
                            </li>
                        ))}
                    </ul>

                    {hours.length > 0 && (
                        <div className="mt-4 flex flex-wrap gap-x-6 gap-y-1 border-t border-border pt-3 text-[13px] text-muted-foreground">
                            {hours.map(([label, value]) => (
                                <span key={label}>
                                    <strong className="font-semibold text-foreground">{label}</strong> {value}
                                </span>
                            ))}
                        </div>
                    )}
                </div>
            </section>
        </>
    );
}

Confirmation.layout = (page) => <StorefrontLayout>{page}</StorefrontLayout>;
export default Confirmation;
